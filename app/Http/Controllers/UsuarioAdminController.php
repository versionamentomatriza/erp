<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Empresa;
use App\Models\UsuarioEmpresa;
use App\Models\UsuarioLocalizacao;
use App\Utils\UploadUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
        $this->middleware('permission:admin_usuarios_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:admin_usuarios_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:admin_usuarios_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:admin_usuarios_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = User::where('usuario_empresas.empresa_id', request()->empresa_id)
            ->join('usuario_empresas', 'users.id', '=', 'usuario_empresas.usuario_id')
            ->select('users.*')
            ->when(!empty($request->name), function ($q) use ($request) {
                return $q->where('name', 'LIKE', "%{$request->name}%");
            })
            ->paginate(env("PAGINACAO"));

        return view('usuarios_admin.index', compact('data'));
    }

    public function create(Request $request)
    {
        $roles = Role::orderBy('name', 'desc')
            ->where('empresa_id', $request->empresa_id)
            ->get();

        $count = UsuarioEmpresa::where('empresa_id', request()->empresa_id)->count();
        $count++;
        $empresa = Empresa::findOrFail(request()->empresa_id);
        $plano = $empresa->plano;

        if ($count >= $plano->plano->maximo_usuarios) {
            session()->flash("flash_warning", "Limite de usuários atingido!");
            return redirect()->back();
        }

        return view('usuarios_admin.create', compact('roles'));
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

        return view('usuarios_admin.edit', compact('item', 'roles'));
    }

    public function store(Request $request)
    {
        $this->__validate($request);
        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/usuarios');
            }

            $request->merge([
                'password' => Hash::make($request['password']),
                'imagem' => $file_name,
            ]);

            $usuario = User::create($request->all());

            UsuarioEmpresa::create([
                'empresa_id' => $request->empresa_id,
                'usuario_id' => $usuario->id,
            ]);

            $role = Role::findOrFail($request->role_id);
            $usuario->assignRole($role->name);

            if (isset($request->locais)) {
                foreach ($request->locais as $local) {
                    UsuarioLocalizacao::updateOrCreate([
                        'usuario_id' => $usuario->id,
                        'localizacao_id' => $local,
                    ]);
                }
            }

            __createLog($request->empresa_id, 'Usuário', 'cadastrar', $request->name);
            session()->flash("flash_success", "Usuário cadastrado!");
        } catch (\Exception $e) {
            __createLog($request->empresa_id, 'Usuário', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }

        return redirect()->route('usuarios_admin.index');
    }

    private function __validate(Request $request)
    {
        $rules = [
            'email' => 'unique:users',
        ];

        $messages = [
            'email.unique' => 'Email já utilizado!',
        ];

        $this->validate($request, $rules, $messages);
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        try {
            $file_name = $usuario->imagem;

            if ($request->hasFile('image')) {
                $this->util->unlinkImage($usuario, '/usuarios');
                $file_name = $this->util->uploadImage($request, '/usuarios');
            }

            if ($request->password) {
                $request->merge([
                    'password' => Hash::make($request->password),
                    'imagem' => $file_name,
                ]);
            } else {
                $request->merge([
                    'password' => $usuario->password,
                    'imagem' => $file_name,
                ]);
            }

            $usuario->fill($request->all())->save();

            $role = Role::findOrFail($request->role_id);
            foreach ($usuario->roles as $r) {
                $usuario->removeRole($r->name);
            }
            $usuario->assignRole($role->name);

            if (isset($request->locais)) {
                $usuario->locais()->delete();
                foreach ($request->locais as $local) {
                    UsuarioLocalizacao::updateOrCreate([
                        'usuario_id' => $usuario->id,
                        'localizacao_id' => $local,
                    ]);
                }
            }

            __createLog($request->empresa_id, 'Usuário', 'editar', $request->name);
            session()->flash("flash_success", "Usuário alterado!");
        } catch (\Exception $e) {
            __createLog($request->empresa_id, 'Usuário', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }

        return redirect()->route('usuarios_admin.index');
    }

    public function destroy($id)
    {
        $item = User::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            $descricaoLog = $item->name;

            $item->empresa->delete();
            $item->delete();

            __createLog(request()->empresa_id, 'Usuário', 'excluir', $descricaoLog);
            session()->flash("flash_success", "Usuário removido com sucesso!");
        } catch (\Exception $e) {
            __createLog(request()->empresa_id, 'Usuário', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }

        return redirect()->back();
    }

    public function show($id)
    {
        if (!__isAdmin()) {
            session()->flash("flash_error", "Acesso permitido somente para administradores");
            return redirect()->back();
        }

        $item = User::findOrFail($id);

        return view('usuarios_admin.show', compact('item'));
    }
}
