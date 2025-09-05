<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\UsuarioEmpresa;
use App\Models\UsuarioLocalizacao;
use App\Utils\UploadUtil;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ConfirmacaoEmail;


//algum erro tem
class UsuarioController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
        $this->middleware('permission:usuarios_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:usuarios_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:usuarios_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:usuarios_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = User::where('usuario_empresas.empresa_id', request()->empresa_id)
            ->join('usuario_empresas', 'users.id', '=', 'usuario_empresas.usuario_id')
            ->select('users.*')
            ->when(!empty($request->name), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('name', 'LIKE', "%$request->name%");
                });
            })
            ->paginate(env("PAGINACAO"));

        return view('usuarios.index', compact('data'));
    }

    public function create(Request $request)
    {
        $empresa = \App\Models\Empresa::with('plano.plano')->findOrFail($request->empresa_id);

        $usuariosAtuais = \App\Models\UsuarioEmpresa::where('empresa_id', $request->empresa_id)->count();
        $limite = optional(optional($empresa->plano)->plano)->maximo_usuarios;

        if ($limite !== null && $usuariosAtuais >= $limite) {
            session()->flash("flash_warning", "Limite de usuários do plano atingido ({$limite}).");
            return redirect()->back();
        }

        $roles = Role::orderBy('name', 'desc')
            ->where('empresa_id', $request->empresa_id)
            ->get();

        return view('usuarios.create', compact('roles'));
    }


    public function edit(Request $request, $id)
    {
        $item = User::findOrFail($id);
        if (!__isMaster()) {
            __validaObjetoEmpresa($item);
        }

        $roles = Role::orderBy('name', 'desc')
            ->where('empresa_id', $request->empresa_id)
            ->get();
        return view('usuarios.edit', compact('item', 'roles'));
    }

    public function store(Request $request)
    {
        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/usuarios');
            }
            $request->merge([
                'password' => Hash::make($request['password']),
                'imagem' => $file_name
            ]);
            $usuario = User::create($request->all());

            UsuarioEmpresa::create([
                'empresa_id' => $request->empresa_id,
                'usuario_id' => $usuario->id
            ]);

            $role = Role::findOrFail($request->role_id);
            $usuario->assignRole($role->name);

            if (isset($request->locais)) {
                for ($i = 0; $i < sizeof($request->locais); $i++) {
                    UsuarioLocalizacao::updateOrCreate([
                        'usuario_id' => $usuario->id,
                        'localizacao_id' => $request->locais[$i]
                    ]);
                }
            }

            session()->flash("flash_success", "Usuário cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('usuarios.index');
    }

        public function update(Request $request, $id)
        {
        $usuario = User::findOrFail($id); 

        $emailAntigo = $usuario->email;
        $emailNovo = $request->input('email');

        $emailAlterado = $emailAntigo !== $emailNovo;

        // Limpa qualquer flag antiga
        session()->forget('show_confirmation_modal');

        // Atualiza campos básicos
        $usuario->fill($request->except('email'));

        // Se o email foi alterado
        if ($emailAlterado) {
            $usuario->email = $emailNovo;
            $usuario->email_verified_at = null; // marca como não verificado
        }

        $usuario->save();
        

    // Se o email foi alterado ou não está verificado, envia confirmação
    if ($emailAlterado || is_null($usuario->email_verified_at)) {
        $token = Str::random(60);
        $usuario->remember_token = $token;
        $usuario->save();

        $link = route('confirm.email', [
            'token' => $token,
            'email' => urlencode($usuario->email)
        ]);

        Mail::to($usuario->email)->send(
            new ConfirmacaoEmail($usuario, $link)
        );

        // só seta a sessão se ainda não tiver verificado
        if (is_null($usuario->email_verified_at)) {
            session(['show_confirmation_modal' => true]);
        }
    }


    return redirect()->route('usuarios.edit', $id)
    ->with('success', 'Usuário atualizado com sucesso.');

     }
    public function confirmEmail($token, $email)
    {
        $user = User::where('remember_token', $token)
                    ->where('email', $email)
                    ->first();

        if (!$user) {
            return redirect()->route('login')->with('flash_error', 'Link inválido ou expirado.');
        }

        $user->email_verified_at = now();
        $user->remember_token = null;
        $user->save();

        return redirect()->route('login')->with('flash_success', 'E-mail confirmado com sucesso!');
    }
    
    public function resendConfirmation(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->email_verified_at) {
            return response()->json(['message' => 'E-mail já verificado.'], 400);
        }

        // Verifica se ainda está no tempo de espera
        if (session()->has("last_email_sent_{$usuario->id}")) {
            $lastSent = session("last_email_sent_{$usuario->id}");
            if (now()->diffInSeconds($lastSent) < 60) {
                return response()->json(['message' => 'Aguarde o tempo de espera.'], 429);
            }
        }

        // Cria novo token e link
        $token = \Illuminate\Support\Str::random(60);
        $usuario->remember_token = $token;
        $usuario->save();

        $link = route('confirm.email', [
            'token' => $token,
            'email' => urlencode($usuario->email)
        ]);

        \Illuminate\Support\Facades\Mail::to($usuario->email)->send(new \App\Mail\ConfirmacaoEmail($usuario, $link));

        // Salva o horário de envio para controlar o timeout
        session(["last_email_sent_{$usuario->id}" => now()]);

        return response()->json(['message' => 'Link de confirmação reenviado com sucesso.']);
    }

    public function destroy($id)
    {
        $item = User::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            $item->empresa->delete();
            $item->delete();
            session()->flash("flash_success", "Apagado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('usuarios.index');
    }

    public function profile($id)
    {
        $item = User::findOrFail($id);
        return view('usuarios.profile', compact('item'));
    }

    public function show($id)
    {
        if (!__isAdmin()) {
            session()->flash("flash_error", "Acesso permitido somente para administradores");
            return redirect()->back();
        }
        $item = User::findOrFail($id);
        return view('usuarios.show', compact('item'));
    }
}
