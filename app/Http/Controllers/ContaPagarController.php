<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Cliente;
use App\Models\ContaPagar;
use App\Models\Fornecedor;
use App\Models\ItemContaEmpresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Utils\ContaEmpresaUtil;
use App\Utils\UploadUtil;
use App\Models\CentroCusto;

class ContaPagarController extends Controller
{

    protected $util;
    protected $uploadUtil;
    public function __construct(ContaEmpresaUtil $util, UploadUtil $uploadUtil){
        $this->util = $util;
        $this->uploadUtil = $uploadUtil;
        $this->middleware('permission:conta_pagar_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:conta_pagar_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:conta_pagar_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:conta_pagar_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $query = ContaPagar::query();
        $locais = __getLocaisAtivoUsuario();
        $locais = $locais->pluck(['id']);
    
        $fornecedor_id = $request->fornecedor_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $local_id = $request->get('local_id');
        $ordenar_por = $request->get('ordenar_por'); // Novo campo para ordenação
    
        // Query para buscar as contas a pagar com os filtros aplicados
        $data = ContaPagar::where('empresa_id', request()->empresa_id)
            ->when(!empty($fornecedor_id), function ($query) use ($fornecedor_id) {
                return $query->where('fornecedor_id', $fornecedor_id);
            })
            ->when(!empty($start_date), function ($query) use ($start_date) {
                return $query->whereDate('data_vencimento', '>=', $start_date);
            })
            ->when(!empty($end_date), function ($query) use ($end_date) {
                return $query->whereDate('data_vencimento', '<=', $end_date);
            })
            ->when($local_id, function ($query) use ($local_id) {
                return $query->where('local_id', $local_id);
            })
            ->when(!$local_id, function ($query) use ($locais) {
                return $query->whereIn('local_id', $locais);
            })
            /* Adicionando a ordenação com base na escolha do usuário*/
            ->when($ordenar_por, function ($query) use ($ordenar_por) {
                if ($ordenar_por === 'data_vencimento_asc') {
                    return $query->orderBy('data_vencimento', 'asc');
                } elseif ($ordenar_por === 'data_vencimento_desc') {
                    return $query->orderBy('data_vencimento', 'desc');
                }
            })
			->when($request->estado == 'pago', function ($query) {
                return $query->where('status', true);
            })
            ->when($request->estado == 'pendente', function ($query) {
                return $query->where('status', false);
            })
            ->paginate(env("PAGINACAO"));
    
        // Retorna a view com os dados filtrados e ordenados
        return view('conta-pagar.index', compact('data'));
    }
    

    public function create()
    {
        $centrosCusto = CentroCusto::where('empresa_id', request()->empresa_id)->get();
        $fornecedores = Fornecedor::where('empresa_id', request()->empresa_id)->get();
        return view('conta-pagar.create', compact('fornecedores','centrosCusto'));
    }

    public function store(Request $request)
    {
        $this->__validate($request);

        try {
            $file_name = '';
            if ($request->hasFile('file')) $file_name = $this->uploadUtil->uploadFile($request->file, '/financeiro');

            $request->merge([
                'categoria_conta_id' => $request->categoria_conta_id,
                'valor_integral' => __convert_value_bd($request->valor_integral),
                'valor_pago' => $request->status ? __convert_value_bd($request->valor_pago) : 0,
                'arquivo' => $file_name
            ]);

            $conta = ContaPagar::create($request->all());
            if ($request->dt_recorrencia) {
                for ($i = 0; $i < sizeof($request->dt_recorrencia); $i++) {
                    $data = $request->dt_recorrencia[$i];
                    $valor = __convert_value_bd($request->valor_recorrencia[$i]);
                    $data = [
                        'descricao' => $request->descricao,
                        'data_vencimento' => $data,
                        'valor_integral' => $valor,
                        'status' => 0,
                        'empresa_id' => $request->empresa_id,
                        'fornecedor_id' => $request->fornecedor_id,
                        'centro_custo_id' => $request->centro_custo_id,
                        'local_id' => $conta->local_id,
                    ];
                    ContaPagar::create($data);
                }
            }
            session()->flash("flash_success", "Conta a pagar cadastrada!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('conta-pagar.index');
    }

   public function edit($id)
    {
        $centrosCusto = CentroCusto::where('empresa_id', request()->empresa_id)->get();
        $item = ContaPagar::findOrFail($id);
        $fornecedores = Fornecedor::where('empresa_id', request()->empresa_id)->get();

        return view('conta-pagar.edit', compact('item', 'fornecedores','centrosCusto'));
    }

    public function update(Request $request, $id)
    {
        $item = ContaPagar::findOrFail($id);
        try {
            $file_name = $item->arquivo;
            if ($request->hasFile('file')) {
                $this->uploadUtil->unlinkImage($item, '/financeiro');
                $file_name = $this->uploadUtil->uploadFile($request->file, '/financeiro');
            }
            $request->merge([
                'valor_integral' => __convert_value_bd($request->valor_integral),
                'valor_pago' => __convert_value_bd($request->valor_pago) ? __convert_value_bd($request->valor_pago) : 0,
                'arquivo' => $file_name
            ]);
            $item->fill($request->all())->save();
            session()->flash("flash_success", "Conta a pagar atualizada!");
        } catch (\Exception $e) {
            // echo $e->getMessage();
            // die;
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('conta-pagar.index');
    }

    public function downloadFile($id){
        $item = ContaPagar::findOrFail($id);
        if (file_exists(public_path('uploads/financeiro/') . $item->arquivo)) {
            return response()->download(public_path('uploads/financeiro/') . $item->arquivo);
        } else {
            session()->flash("flash_error", "Arquivo não encontrado");
            return redirect()->back();
        }
    }

    private function __validate(Request $request)
    {
        $rules = [
            'fornecedor_id' => 'required',
            'valor_integral' => 'required',
            'data_vencimento' => 'required',
            'status' => 'required',
            'tipo_pagamento' => 'required'
        ];
        $messages = [
            'fornecedor_id.required' => 'Campo obrigatório',
            'valor_integral.required' => 'Campo obrigatório',
            'data_vencimento.required' => 'Campo obrigatório',
            'status.required' => 'Campo obrigatório',
            'tipo_pagamento.required' => 'Campo obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = ContaPagar::findOrFail($id);
        try {
            $item->delete();
            session()->flash("flash_success", "Conta removida!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('conta-pagar.index');
    }

    public function destroySelecet(Request $request)
    {
        $removidos = 0;
        for($i=0; $i<sizeof($request->item_delete); $i++){
            $item = ContaPagar::findOrFail($request->item_delete[$i]);
            try {
                $item->delete();
                $removidos++;
            } catch (\Exception $e) {
                session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
                return redirect()->back();
            }
        }

        session()->flash("flash_success", "Total de itens removidos: $removidos!");
        return redirect()->back();
    }

    public function pay($id)
    {
        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }
        $item = ContaPagar::findOrFail($id);

        if($item->status){
            session()->flash("flash_warning", "Esta conta já esta paga!");
            return redirect()->route('conta-pagar.index');
        }
        return view('conta-pagar.pay', compact('item'));
    }

    public function payPut(Request $request, $id)
    {
        $usuario = Auth::user()->id;
        $caixa = Caixa::where('usuario_id', $usuario)->where('status', 1)->first();
        $item = ContaPagar::findOrFail($id);

        try {
            $item->valor_pago = __convert_value_bd($request->valor_pago);
            $item->status = true;
            $item->data_pagamento = $request->data_pagamento;
            $item->tipo_pagamento = $request->tipo_pagamento;
            $item->caixa_id = $caixa->id;
            $item->save();

            if(isset($request->conta_empresa_id)){

                $data = [
                    'conta_id' => $request->conta_empresa_id,
                    'descricao' => "Pagamento da conta " . $item->id,
                    'tipo_pagamento' => $request->tipo_pagamento,
                    'valor' => $item->valor_pago,
                    'tipo' => 'saida'
                ];
                $itemContaEmpresa = ItemContaEmpresa::create($data);
                $this->util->atualizaSaldo($itemContaEmpresa);
            }
            session()->flash("flash_success", "Conta paga!");
        } catch (\Exception $e) {
            // echo $e->getLine();
            // die;
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('conta-pagar.index');
    }
}
