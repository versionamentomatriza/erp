<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cidade;
use App\Models\Nfe;
use App\Models\Nfce;
use App\Models\ItemNfe;
use App\Models\ItemNfce;
use App\Models\ContaReceber;
use Illuminate\Http\Request;
use App\Imports\ProdutoImport;
use Maatwebsite\Excel\Facades\Excel;

class ClienteController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:clientes_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:clientes_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:clientes_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:clientes_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $query = Cliente::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->razao_social), function ($q) use ($request) {
                return $q->where('razao_social', 'LIKE', "%{$request->razao_social}%");
            })
            ->when(!empty($request->cpf_cnpj), function ($q) use ($request) {
                return $q->where('cpf_cnpj', 'LIKE', "%{$request->cpf_cnpj}%");
            });

        // aplica ordenação só se clicar
        if ($request->filled('sort') && $request->filled('direction')) {
            $query->orderBy($request->get('sort'), $request->get('direction'));
        }

        $data = $query->paginate(env("PAGINACAO"));

        return view('clientes.index', compact('data'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function edit($id)
    {
        $item = Cliente::findOrFail($id);
        __validaObjetoEmpresa($item);
        return view('clientes.edit', compact('item'));
    }

    public function store(Request $request)
    {
        $this->__validate($request);
        try {
            $request->merge([
                'ie' => $request->ie ?? '',
                'nome_fantasia' => $request->nome_fantasia ?? '',
                'valor_cashback' => $request->valor_cashback ? __convert_value_bd($request->valor_cashback) : 0,
                'valor_credito' => $request->valor_credito ? __convert_value_bd($request->valor_credito) : 0
            ]);
            Cliente::create($request->all());
            session()->flash("flash_success", "Cliente cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('clientes.index');
    }

    public function update(Request $request, $id)
    {
        $this->__validate($request);
        $item = Cliente::findOrFail($id);
        try {
            $request->merge([
                'ie' => $request->ie ?? '',
                'valor_cashback' => $request->valor_cashback ? __convert_value_bd($request->valor_cashback) : 0,
                'valor_credito' => $request->valor_credito ? __convert_value_bd($request->valor_credito) : 0
            ]);
            $item->fill($request->all())->save();
            session()->flash("flash_success", "Cliente atualizado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('clientes.index');
    }

    private function __validate(Request $request)
    {
        $rules = [
            'razao_social' => 'required',
            'cpf_cnpj' => 'required',
            // 'ie' => 'required',
            'telefone' => 'required',
            'cidade_id' => 'required',
            'rua' => 'required',
            'cep' => 'required',
            'numero' => 'required',
            'bairro' => 'required',
        ];

        $messages = [
            'razao_social.required' => 'Campo Obrigatório',
            'cpf_cnpj.required' => 'Campo Obrigatório',
            'ie.required' => 'Campo Obrigatório',
            'telefone.required' => 'Campo Obrigatório',
            'cidade_id.required' => 'Campo Obrigatório',
            'rua.required' => 'Campo Obrigatório',
            'cep.required' => 'Campo Obrigatório',
            'numero.required' => 'Campo Obrigatório',
            'bairro.required' => 'Campo Obrigatório',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function historico($id)
    {
        $item = Cliente::findOrFail($id);
        __validaObjetoEmpresa($item);

        $nves = Nfe::where('cliente_id', $id)->get();
        $nfces = Nfce::where('cliente_id', $id)->get();

        $data = [];
        foreach($nves as $n){
            $n->tipo = 'nfe';
            array_push($data, $n);
        }

        foreach($nfces as $n){
            $n->tipo = 'nfce';
            array_push($data, $n);
        }

        usort($data, function($a, $b){
            return $a->created_at < $b->created_at ? 1 : -1;
        });

        $produtos = $this->getProdutos($id);
        $faturas = $this->getFaturas($id);

        return view('clientes.historico', compact('item', 'data', 'produtos', 'faturas'));
    }

    private function getProdutos($id){

        $data = [];
        $dataIds = [];

        $itens = ItemNfe::select('item_nves.*')
        ->join('nves', 'nves.id', '=', 'item_nves.nfe_id')
        ->where('nves.cliente_id', $id)
        ->get();

        foreach($itens as $i){
            if(!in_array($i->produto_id, $dataIds)){
                $data[] = $i;
                $dataIds[] = $i->produto_id;
            }else{
                for($j=0; $j<sizeof($data); $j++){
                    if($data[$j]['produto_id'] == $i->produto_id){
                        $data[$j]['quantidade'] += $i->quantidade;
                    }
                }
            }
        }

        $itens = ItemNfce::select('item_nfces.*')
        ->join('nfces', 'nfces.id', '=', 'item_nfces.nfce_id')
        ->where('nfces.cliente_id', $id)
        ->get();

        foreach($itens as $i){
            if(!in_array($i->produto_id, $dataIds)){
                $data[] = $i;
                $dataIds[] = $i->produto_id;
            }else{
                for($j=0; $j<sizeof($data); $j++){
                    if($data[$j]['produto_id'] == $i->produto_id){
                        $data[$j]['quantidade'] += $i->quantidade;
                    }
                }
            }
        }
        // dd($data);
        return $data;
    }

    private function getFaturas($id){
        return ContaReceber::where('cliente_id', $id)
        ->orderBy('id', 'desc')
        ->get();
    }

    public function destroy($id)
    {
        $item = Cliente::findOrFail($id);
        __validaObjetoEmpresa($item);
        
        try {
            $item->delete();
            session()->flash("flash_success", "Cliente removido!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu Errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    public function import(){
        return view('clientes.import');
    }

    public function downloadModelo(){
        return response()->download(public_path('files/') . 'import_clients_csv_template.xlsx');
    }

	 public function storeModelo(Request $request)
	{
		if ($request->hasFile('file')) {
			ini_set('max_execution_time', 0);
			ini_set('memory_limit', -1);

			$rows = Excel::toArray(new ProdutoImport, $request->file);
			$retornoErro = $this->validaArquivo($rows);
			$cont = 0;

			if ($retornoErro == "") {
				foreach ($rows as $row) {
					foreach ($row as $key => $r) {
						// Ignorar a primeira linha
						if ($key > 0) { 
							try {
								$data = $this->preparaObjeto($r, $request->empresa_id);
								$item = Cliente::create($data);
								$cont++;
							} catch (\Exception $e) {
								session()->flash('flash_error', $e->getMessage());
							}
						}
					}
				}

				session()->flash('flash_success', 'Total de clientes importados: ' . $cont);
				return redirect()->back();
			} else {
				session()->flash('flash_error', $retornoErro);
				return redirect()->back();
			}
		} else {
			session()->flash('flash_error', 'Nenhum Arquivo!!');
			return redirect()->back();
		}
	}


    private function preparaObjeto($linha, $empresa_id){
        $cpf_cnpj = trim((string)$linha[2]);
        $mask = '##.###.###/####-##';

        if(strlen($cpf_cnpj) == 11){
            $mask = '###.###.###.##';
        }
        if(!str_contains($cpf_cnpj, ".")){
            $cpf_cnpj = __mask($cpf_cnpj, $mask); 
        }

        $cidade = Cidade::where('nome', $linha[7])
        ->where('uf', $linha[8])->first();
        $data = [
            'empresa_id' => $empresa_id,
            'razao_social' => $linha[0],
            'nome_fantasia' => $linha[1] != '' ? $linha[1] : '',
            'cpf_cnpj' => $cpf_cnpj,
            'ie' => $linha[3] != '' ? $linha[3] : '',
            'contribuinte' => $linha[13] != '' ? $linha[13] : 0,
            'consumidor_final' => $linha[14] != '' ? $linha[14] : 0,
            'email' => $linha[10] != '' ? $linha[10] : '',
            'telefone' => $linha[9] != '' ? $linha[9] : '',
            'cidade_id' => $cidade != null ? $cidade->id : 1,
            'rua' => $linha[4],
            'cep' => $linha[11],
            'numero' => $linha[5],
            'bairro' => $linha[6],
            'complemento' => $linha[12] != '' ? $linha[12] : ''
        ];

        return $data;
    }

    private function validaArquivo($rows){
        $cont = 0;
        $msgErro = "";
        foreach($rows as $row){
            foreach($row as $key => $r){

                $razaoSocial = $r[0];
                $cpfCnpj = $r[2];
                $rua = $r[4];
                $numero = $r[5];
                $bairro = $r[6];
                $cidade = $r[7];
                $uf = $r[8];
                $cep = $r[11];
                
                if(strlen($razaoSocial) == 0){
                    $msgErro .= "Coluna razão social em branco na linha: $cont | "; 
                }

                if(strlen($cpfCnpj) == 0){
                    $msgErro .= "Coluna CPF/CNPJ em branco na linha: $cont | "; 
                }

                if(strlen($rua) == 0){
                    $msgErro .= "Coluna rua em branco na linha: $cont | "; 
                }

                if(strlen($numero) == 0){
                    $msgErro .= "Coluna numero em branco na linha: $cont | "; 
                }
                if(strlen($bairro) == 0){
                    $msgErro .= "Coluna bairro em branco na linha: $cont | "; 
                }
                if(strlen($cidade) == 0){
                    $msgErro .= "Coluna cidade em branco na linha: $cont | "; 
                }
                if(strlen($cep) == 0){
                    $msgErro .= "Coluna CEP em branco na linha: $cont | "; 
                }
                
                if($msgErro != ""){
                    return $msgErro;
                }
                $cont++;
            }
        }

        return $msgErro;
    }

    public function cashBack($id){
        $item = Cliente::findOrFail($id);
        return view('clientes.cash_back', compact('item'));

    }

    public function destroySelecet(Request $request)
    {
        $removidos = 0;
        for($i=0; $i<sizeof($request->item_delete); $i++){
            $item = Cliente::findOrFail($request->item_delete[$i]);
            try {
                $item->enderecosEcommerce()->delete();
                $item->delete();
                $removidos++;
            } catch (\Exception $e) {
                session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
                return redirect()->route('clientes.index');
            }
        }

        session()->flash("flash_success", "Total de itens removidos: $removidos!");
        return redirect()->route('clientes.index');
    }

}
