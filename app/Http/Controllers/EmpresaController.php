<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\User;
use App\Models\Caixa;
use App\Models\Segmento;
use App\Models\SegmentoEmpresa;
use App\Models\UsuarioEmpresa;
use Database\Seeders\CidadeSeed;
use Illuminate\Support\Facades\Hash;
use NFePHP\Common\Certificate;
use Illuminate\Support\Facades\DB;
use App\Utils\EmpresaUtil;

class EmpresaController extends Controller
{

    protected $empresaUtil;

    public function __construct(EmpresaUtil $empresaUtil)
    {
        $this->empresaUtil = $empresaUtil;
        if (!is_dir(public_path('logos'))) {
            mkdir(public_path('logos'), 0777, true);
        }
    }

    public function index(Request $request)
    {
        $this->empresaUtil->createPermissions();
        $data = Empresa::when(!empty($request->nome), function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->nome%");
        })
        ->when(!empty($request->cpf_cnpj), function ($q) use ($request) {
            return $q->where('cpf_cnpj', 'LIKE', "%$request->cpf_cnpj%");
        })
        ->where('tipo_contador', 0)
		->orderBy('created_at', 'desc')
        ->paginate(env("PAGINACAO"));
        return view('empresas.index', compact('data'));
    }

    public function create()
    {
        $usuarios = User::all();
        $segmentos = Segmento::orderBy('nome', 'asc')
        ->get();
        return view('empresas.create', compact('usuarios', 'segmentos'));
    }

    public function edit($id)
    {
        $usuarios = User::all();
        $item = Empresa::findOrFail($id);
        $infoCertificado = null;
        if ($item != null && $item->arquivo != null) {
            $infoCertificado = $this->getInfoCertificado($item);
        }
        $segmentos = Segmento::orderBy('nome', 'desc')
        ->get();
        return view('empresas.edit', compact('item', 'infoCertificado', 'usuarios', 'segmentos'));
    }

    private function getInfoCertificado($item)
    {
        try {
            $infoCertificado = Certificate::readPfx($item->arquivo, $item->senha);
            $publicKey = $infoCertificado->publicKey;
            $inicio =  $publicKey->validFrom->format('Y-m-d H:i:s');
            $expiracao =  $publicKey->validTo->format('Y-m-d H:i:s');
            return [
                'serial' => $publicKey->serialNumber,
                'inicio' => \Carbon\Carbon::parse($inicio)->format('d-m-Y H:i'),
                'expiracao' => \Carbon\Carbon::parse($expiracao)->format('d-m-Y H:i'),
                'id' => $publicKey->commonName
            ];
        } catch (\Exception $e) {
            // echo $e->getMessage();
            // die;
            return [];
        }
    }

  public function store(Request $request)
{
    $this->__validate($request);

    try {
        DB::transaction(function () use ($request) {
            if ($request->hasFile('certificado')) {
                $file = $request->file('certificado');
                $fileTemp = file_get_contents($file);
                $request->merge([
                    'arquivo' => $fileTemp ?? '',
                    'cpf_cnpj' => preg_replace('/[^0-9]/', '', $request->cpf_cnpj),
                    'senha' => $request->senha_certificado,
                    'token' => $request->token ?? '',
                    'csc' => $request->csc ? $request->csc : 'AAAAAA',
                    'csc_id' => $request->csc_id ? $request->csc_id : '000001',
                ]);
            }

            $email = $request->email;
            $request->merge([
                'email' => $request->email_empresa,
                'cargo_funcao' => $request->cargo_funcao,
                'atividade' => $request->atividade,
                'qtd_funcionarios' => $request->qtd_funcionarios,
            ]);

            $empresa = Empresa::create($request->all());

            if ($request->usuario) {
                $usuario = User::create([
                    'name' => $request->usuario ?? null,
                    'email' => $email ?? null,
                    'password' => Hash::make($request['password']) ?? '',
                    'remember_token' => Hash::make($request['remember_token']) ?? ''
                ]);

                UsuarioEmpresa::create([
                    'empresa_id' => $empresa->id,
                    'usuario_id' => $usuario->id ?? null
                ]);
            }

            if ($request->segmento_id) {
                SegmentoEmpresa::create([
                    'segmento_id' => $request->segmento_id,
                    'empresa_id' => $empresa->id
                ]);
            }

            $this->empresaUtil->initLocation($empresa);
            $this->empresaUtil->defaultPermissions($empresa->id);

            return true;
        });

        session()->flash("flash_success", "Empresa cadastrada!");
    } catch (\Exception $e) {
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }

    return redirect()->route('empresas.index');
}


    public function update(Request $request, $id)
    {
        $item = Empresa::findOrFail($id);

        try {
            // $request->merge([
            //     'cpf_cnpj' => preg_replace('/[^0-9]/', '', $request->cpf_cnpj)
            // ]);
            if ($request->hasFile('certificado')) {

                $file = $request->file('certificado');
                $fileTemp = file_get_contents($file);
                $request->merge([
                    'arquivo' => $fileTemp,
                    'senha' => $request->senha_certificado
                ]);
            }

            if($request->segmento_id){
                $item->segmentos()->delete();
                SegmentoEmpresa::create([
                    'segmento_id' => $request->segmento_id,
                    'empresa_id' => $id
                ]);
            }
            $item->fill($request->all())->save();
            session()->flash("flash_success", "Empresa atualizada!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('empresas.index');
    }

    public function destroy($id)
    {

        $item = Empresa::findOrFail($id);
        try {

            foreach($item->usuarios as $u){
                $u->usuario->acessos()->delete();
            }
            $item->usuarios()->delete();
            // $item->user()->delete();
            $item->plano()->delete();
            $this->deleteRegistros($item->id);

            $item->delete();
            session()->flash("flash_success", "Empresa removida!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu Errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    private function deleteRegistros($empresa_id){

        $nfe = \App\Models\Nfe::where('empresa_id', $empresa_id)->get();
        foreach($nfe as $n){
            $n->itens()->delete();
            $n->fatura()->delete();
            $n->delete();
        }

        $nfce = \App\Models\Nfce::where('empresa_id', $empresa_id)->get();
        foreach($nfce as $n){
            $n->itens()->delete();
            $n->fatura()->delete();
            $n->delete();
        }
        Caixa::where('empresa_id', $empresa_id)->delete();
        $produtos = \App\Models\Produto::where('empresa_id', $empresa_id)->get();
        \App\Models\ProdutoCombo::
        select('produto_combos.*')
        ->join('produtos', 'produtos.id', '=', 'produto_combos.produto_id')
        ->where('produtos.empresa_id', $empresa_id)->delete();
        foreach($produtos as $p){
            $p->movimentacoes()->delete();
            $p->locais()->delete();
            $p->variacoes()->delete();

            if($p->estoque){
                $p->estoque->delete();
            }
            $p->delete();
        }

        \App\Models\Marca::where('empresa_id', $empresa_id)->delete();
        \App\Models\CategoriaProduto::where('empresa_id', $empresa_id)->delete();
        \App\Models\Cliente::where('empresa_id', $empresa_id)->delete();
        \App\Models\Fornecedor::where('empresa_id', $empresa_id)->delete();
        \App\Models\NaturezaOperacao::where('empresa_id', $empresa_id)->delete();
        \App\Models\PadraoTributacaoProduto::where('empresa_id', $empresa_id)->delete();
        \App\Models\Role::where('empresa_id', $empresa_id)->delete();
        \App\Models\FinanceiroPlano::where('empresa_id', $empresa_id)->delete();

        $usuarios = UsuarioEmpresa::where('empresa_id', $empresa_id)->get();
        // echo $usuarios;
        // die;
        
        \App\Models\UsuarioLocalizacao::
        select('usuario_localizacaos.*')
        ->join('localizacaos', 'localizacaos.id', '=', 'usuario_localizacaos.localizacao_id')
        ->where('localizacaos.empresa_id', $empresa_id)->delete();

        \App\Models\Localizacao::where('empresa_id', $empresa_id)->delete();

    }

    private function __validate(Request $request)
    {
        $rules = [
            'nome' => 'required',
            'cpf_cnpj' => 'required',
            'ie' => 'required',
            'celular' => 'required',
            'cep' => 'required',
            'rua' => 'required',
            'numero' => 'required',
            'bairro' => 'required',
            'cidade_id' => 'required',
            'email' => 'unique:users',
        ];
        $messages = [
            'nome.required' => 'Campo Obrigatório',
            'cpf_cnpj.required' => 'Campo Obrigatório',
            'ie.required' => 'Campo Obrigatório',
            'email.required' => 'Campo Obrigatório',
            'celular.required' => 'Campo Obrigatório',
            'csc.required' => 'Campo Obrigatório',
            'csc_id.required' => 'Campo Obrigatório',
            'cep.required' => 'Campo Obrigatório',
            'rua.required' => 'Campo Obrigatório',
            'numero.required' => 'Campo Obrigatório',
            'bairro.required' => 'Campo Obrigatório',
            'cidade_id.required' => 'Campo Obrigatório',
            'numero_ultima_nfe_producao.required' => 'Campo Obrigatório',
            'numero_ultima_nfe_homologacao.required' => 'Campo Obrigatório',
            'numero_serie_nfe.required' => 'Campo Obrigatório',
            'numero_ultima_nfce_producao.required' => 'Campo Obrigatório',
            'numero_ultima_nfce_homologacao.required' => 'Campo Obrigatório',
            'numero_serie_nfce.required' => 'Campo Obrigatório',
            'email.unique' => 'Já existe um usuário com este email',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function painel($id)
    {
        $empresa = Empresa::findOrFail($id);
        return view('empresas.painel', compact('empresa'));
    }

    public function config($id)
    {
        $item = Empresa::findOrFail($id);
        return view('empresas.configuracao', compact('item'));
    }

        public function findByCnpj($cnpj)
    {
        $empresa = Empresa::where('cpf_cnpj', $cnpj)->first();

        if (!$empresa) {
            return response()->json(null, 404);
        }

        return response()->json($empresa);
    }

}
