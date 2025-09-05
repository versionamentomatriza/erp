<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\UsuarioEmpresa;
use App\Models\User;
use App\Models\FinanceiroContador;
use App\Models\ContadorEmpresa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ContadorController extends Controller
{
    public function index(Request $request)
    {
        $data = Empresa::when(!empty($request->nome), function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->nome%");
        })
        ->when(!empty($request->cpf_cnpj), function ($q) use ($request) {
            return $q->where('cpf_cnpj', 'LIKE', "%$request->cpf_cnpj%");
        })
        ->where('tipo_contador', 1)
        ->paginate(env("PAGINACAO"));

        return view('contadores.index', compact('data'));
    }

    public function create()
    {
        return view('contadores.create');
    }

    public function edit($id)
    {
        $item = Empresa::findOrFail($id);

        return view('contadores.edit', compact('item'));
    }

    public function store(Request $request)
    {
        $this->__validate($request);
        try {

            DB::transaction(function () use ($request) {

                $email = $request->email;
                $request->merge([
                    'email' => $request->email_empresa,
                    'tipo_contador' => 1
                ]);

                $empresa = Empresa::create($request->all());

                if ($request->usuario) {

                    $usuario = User::create([
                        'name' => $request->usuario ?? null,
                        'email' => $email ?? null,
                        'password' => Hash::make($request['password']) ?? '',
                        'remember_token' => Hash::make($request['remember_token']) ?? '',
                        'tipo_contador' => 1
                    ]);

                    UsuarioEmpresa::create([
                        'empresa_id' => $empresa->id,
                        'usuario_id' => $usuario->id ?? null
                    ]);
                }
                return true;
            });
            session()->flash("flash_success", "Contador cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('contadores.index');
    }

    public function update(Request $request, $id)
    {
        $item = Empresa::findOrFail($id);

        try {
            $request->merge([
                'percentual_comissao' => __convert_value_bd($request->percentual_comissao)
            ]);
            
            $item->fill($request->all())->save();
            session()->flash("flash_success", "Contador atualizado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('contadores.index');
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

    public function destroy($id)
    {

        $item = Empresa::findOrFail($id);
        $item->usuarios()->delete();
        $item->user()->delete();
        $item->plano()->delete();

        try {
            $item->delete();
            session()->flash("flash_success", "Contador removido!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu Errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    public function show($id)
    {
        $item = Empresa::findOrFail($id);
        return view('contadores.show', compact('item'));
    }

    public function addBusiness(Request $request, $id){
        try{
            ContadorEmpresa::create([
                'empresa_id' => $request->empresa_contador_id,
                'contador_id' => $id
            ]);
            session()->flash("flash_success", "Empresa atribuída!");

        }catch(\Exception $e){
            session()->flash("flash_error", "Algo deu Errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    public function destroyBusiness($id)
    {
        $item = ContadorEmpresa::findOrFail($id);
        try {
            $item->delete();
            session()->flash("flash_success", "Empresa removida do contador!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu Errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    public function financeiro(Request $request, $id)
    {
        $item = Empresa::findOrFail($id);
        $financeiro = FinanceiroContador::
        join('empresas', 'empresas.id', '=', 'financeiro_contadors.contador_id')
        ->where('empresas.id', $id)
        ->select('financeiro_contadors.*')
        ->when(!empty($request->mes), function ($query) use ($request) {
            return $query->where('mes', $request->mes);
        })
        ->when(!empty($request->ano), function ($query) use ($request) {
            return $query->where('ano', $request->ano);
        })
        ->orderBy('financeiro_contadors.id', 'desc')
        ->get();
        return view('contadores.financeiro', compact('item', 'financeiro'));
    }

    public function createFinanceiro($id){
        $contador = Empresa::findOrFail($id);
        $ultimo = FinanceiroContador::where('contador_id', $id)
        ->orderBy('created_at', 'desc')
        ->first();
        $mesAtual = (int)date('m') - 1;
        $mesAtual = $this->meses()[$mesAtual];

        $data = $this->calculoTotalComissao($contador);

        return view('contadores.financeiro_create', compact('contador', 'ultimo', 'mesAtual', 'data'));
    }

    private function calculoTotalComissao($contador){
        $data = [
            'total' => 0,
            'comissao' => 0,
        ];
        foreach($contador->empresasAtribuidas as $e){
            if($e->empresa->plano){
                $data['total'] += $e->empresa->plano->valor;
            }
        }
        $data['comissao'] = $data['total']*($contador->percentual_comissao/100);
        return (object)$data;
    }

    private function meses(){
        return [
            'janeiro',
            'fevereiro',
            'março',
            'abril',
            'maio',
            'junho',
            'julho',
            'agosto',
            'setembro',
            'outubro',
            'novembro',
            'dezembro',
        ];
    }

    public function storeFinanceiro(Request $request, $id){

        $ano = $request->ano;
        $mes = $request->mes;

        $item = FinanceiroContador::where('ano', $ano)
        ->where('mes', $mes)
        ->where('contador_id', $id)->first();

        if($item != null){
            session()->flash("flash_error", "Já existe um pagamento para o mês de $mes do ano de $ano para este contador");
            return redirect()->back();
        }
        $request->merge([
            'contador_id' => $id,
            'total_venda' => __convert_value_bd($request->total_venda),
            'valor_comissao' => __convert_value_bd($request->valor_comissao)
        ]);

        try{
            FinanceiroContador::create($request->all());
            session()->flash("flash_success", "Financeiro registrado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu Errado: " . $e->getMessage());
        }
        return redirect()->route('contadores.financeiro', [$id]);

    }

    public function destroyFincanceiro($id)
    {
        $item = FinanceiroContador::findOrFail($id);
        try {
            $item->delete();
            session()->flash("flash_success", "Registro removido!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu Errado: " . $e->getMessage());
        }
        return redirect()->back();
    }
    

}
