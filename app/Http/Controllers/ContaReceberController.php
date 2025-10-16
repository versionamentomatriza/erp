<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\CategoriaConta;
use App\Models\Cliente;
use App\Models\ContaReceber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Utils\ContaEmpresaUtil;
use App\Models\ItemContaEmpresa;
use App\Utils\UploadUtil;
use Illuminate\Database\QueryException;
use App\Models\CentroCusto;
use Illuminate\Support\Facades\DB;


class ContaReceberController extends Controller
{
    protected $util;
    protected $uploadUtil;

    public function __construct(ContaEmpresaUtil $util, UploadUtil $uploadUtil)
    {
        $this->util = $util;
        $this->uploadUtil = $uploadUtil;

        $this->middleware('permission:conta_receber_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:conta_receber_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:conta_receber_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:conta_receber_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $query = ContaReceber::query();
        $locais = __getLocaisAtivoUsuario();
        $locais = $locais->pluck(['id']);

        $cliente_id = $request->cliente_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $local_id = $request->get('local_id');
        $ordenar_por = $request->get('ordenar_por'); // Captura o campo para ordenação

        // Query para buscar as contas a receber com os filtros aplicados
        $data = ContaReceber::where('empresa_id', request()->empresa_id)
            ->when(!empty($cliente_id), function ($query) use ($cliente_id) {
                return $query->where('cliente_id', $cliente_id);
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
            ->where('descricao', '!=', 'Venda PDV')
            // Adicionando a ordenação com base na escolha do usuário
            ->when($ordenar_por, function ($query) use ($ordenar_por) {
                if ($ordenar_por === 'data_vencimento_asc') {
                    return $query->orderBy('data_vencimento', 'asc');
                } elseif ($ordenar_por === 'data_vencimento_desc') {
                    return $query->orderBy('data_vencimento', 'desc');
                }
            })
            ->paginate(env("PAGINACAO"));

        // Retorna a view com os dados filtrados e ordenados
        return view('conta-receber.index', compact('data'));
    }


    public function create(Request $request)
    {
        $centrosCusto = CentroCusto::where('empresa_id', request()->empresa_id)->get();
        $clientes = Cliente::where('empresa_id', request()->empresa_id)->get();

        $item = null;
        $diferenca = null;
        if ($request->id) {
            $item = ContaReceber::findOrFail($request->id);
            $item->valor_integral = $request->diferenca;
        }

        if ($request->diferenca) {
            $diferenca = $request->diferenca;
        }

        return view('conta-receber.create', compact('clientes', 'item', 'diferenca', 'centrosCusto'));
    }

    public function store(Request $request)
    {
        $this->__validate($request);

        try {
            DB::transaction(function () use ($request) {
                // Upload do arquivo (se houver)
                $file_name = $request->hasFile('file')
                    ? $this->uploadUtil->uploadFile($request->file, '/financeiro')
                    : '';

                // Prepara dados base
                $request->merge([
                    'data_competencia'   => $request->data_competencia ?: $request->data_vencimento,
                    'categoria_conta_id' => $request->categoria_conta_id,
                    'valor_integral'     => __convert_value_bd($request->valor_integral),
                    'valor_recebido'     => $request->status ? __convert_value_bd($request->valor_recebido) : 0,
                    'arquivo'            => $file_name,
                ]);

                // Cria conta principal
                $conta = ContaReceber::create($request->all());

                // ======================================
                // RECORRÊNCIA
                // ======================================
                if (!empty($request->dt_recorrencia)) {
                    foreach ($request->dt_recorrencia as $i => $data) {
                        ContaReceber::create([
                            'descricao'          => $request->descricao,
                            'data_vencimento'    => $data,
                            'data_competencia'   => $data,
                            'valor_integral'     => __convert_value_bd($request->valor_recorrencia[$i]),
                            'status'             => 0,
                            'empresa_id'         => $request->empresa_id,
                            'cliente_id'         => $request->cliente_id,
                            'centro_custo_id'    => $request->centro_custo_id,
                            'local_id'           => $conta->local_id,
                            'categoria_conta_id' => $request->categoria_conta_id,
                        ]);
                    }
                }

                // ======================================
                // PARCELAMENTO
                // ======================================
                if (!empty($request->parcelas) && $request->parcelas > 1) {
                    $parcelas     = (int) $request->parcelas;
                    $valorTotal   = __convert_value_bd($request->valor_integral);
                    $valorParcela = round($valorTotal / $parcelas, 2);
                    $ultimoValor  = $valorTotal - ($valorParcela * ($parcelas - 1));

                    $dataBase = \Carbon\Carbon::parse($request->data_vencimento);

                    for ($i = 1; $i <= $parcelas; $i++) {
                        $statusRecebido = ($i === 1 && $request->status) ? $request->status : 0;
                        $dataRecebimento = ($i === 1 && $request->status) ? $request->data_recebimento : null;
                        $valorRecebido = ($i === 1 && $request->status) ? $request->valor_recebido : null;

                        $valor = ($i === $parcelas) ? $ultimoValor : $valorParcela;
                        $dataVenc = $dataBase->copy()->addMonths($i - 1);

                        ContaReceber::create([
                            'descricao'          => "{$request->descricao} ({$i}/{$parcelas})",
                            'data_vencimento'    => $dataVenc,
                            'data_competencia'   => $request->data_competencia,
                            'valor_integral'     => $valor,
                            'status'             => $statusRecebido,
                            'empresa_id'         => $request->empresa_id,
                            'cliente_id'         => $request->cliente_id,
                            'centro_custo_id'    => $request->centro_custo_id,
                            'local_id'           => $conta->local_id,
                            'categoria_conta_id' => $request->categoria_conta_id,
                            'tipo_pagamento'     => $request->tipo_pagamento,
                            'observacao'         => $request->observacao,
                            'data_recebimento'   => $dataRecebimento,
                            'valor_recebido'     => $valorRecebido,
                        ]);
                    }

                    // Exclui a conta original (já foi substituída pelas parcelas)
                    $conta->delete();
                }

                session()->flash("flash_success", "Conta a receber cadastrada com sucesso!");
            });
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }

        return redirect()->route('conta-receber.index');
    }

    public function edit($id)
    {
        $item = ContaReceber::findOrFail($id);
        $clientes = Cliente::where('empresa_id', request()->empresa_id)->get();
        $centrosCusto = CentroCusto::where('empresa_id', request()->empresa_id)->get();

        return view('conta-receber.edit', compact('item', 'clientes', 'centrosCusto'));
    }

    public function update(Request $request, $id)
    {
        $item = ContaReceber::findOrFail($id);
        try {
            $file_name = $item->arquivo;
            if ($request->hasFile('file')) {
                $this->uploadUtil->unlinkImage($item, '/financeiro');
                $file_name = $this->uploadUtil->uploadFile($request->file, '/financeiro');
            }
            $request->merge([
                'valor_integral' => __convert_value_bd($request->valor_integral),
                'valor_recebido' => __convert_value_bd($request->valor_recebido) ? __convert_value_bd($request->valor_recebido) : 0,
                'arquivo' => $file_name
            ]);
            $item->fill($request->all())->save();
            session()->flash("flash_success", "Conta a receber atualizada!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('conta-receber.index');
    }

    public function downloadFile($id)
    {
        $item = ContaReceber::findOrFail($id);
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
            'cliente_id' => 'required',
            'valor_integral' => 'required',
            'data_vencimento' => 'required',
            'status' => 'required',
            'tipo_pagamento' => 'required',
            'categoria_conta_id' => 'required',
            'data_competencia' => 'required'
        ];
        $messages = [
            'cliente_id.required' => 'Campo obrigatório',
            'valor_integral.required' => 'Campo obrigatório',
            'data_vencimento.required' => 'Campo obrigatório',
            'status.required' => 'Campo obrigatório',
            'tipo_pagamento.required' => 'Campo obrigatório',
            'categoria_conta_id.required' => 'Campo obrigatório',
            'data_competencia.required' => 'Campo obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = ContaReceber::findOrFail($id);
        try {
            $item->delete();
            session()->flash("flash_success", "Conta removida!");
        } catch (QueryException $e) {
            // Verifica se o erro é de chave estrangeira (código 23000)
            if ($e->getCode() == "23000") {
                session()->flash("flash_error", "Não é possível excluir esta conta, pois ela está vinculada a um boleto. Exclua o boleto primeiro.");
            } else {
                session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
            }
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }

        return redirect()->route('conta-receber.index');
    }

    public function destroySelecet(Request $request)
    {
        $removidos = 0;

        for ($i = 0; $i < sizeof($request->item_delete); $i++) {
            $item = ContaReceber::findOrFail($request->item_delete[$i]);
            try {
                $item->delete();
                $removidos++;
            } catch (QueryException $e) {
                if ($e->getCode() == "23000") {
                    session()->flash("flash_error", "A conta ID {$item->id} está vinculada a um boleto e não pode ser excluída. Exclua o boleto primeiro.");
                } else {
                    session()->flash("flash_error", "Erro ao excluir a conta ID {$item->id}: " . $e->getMessage());
                }
                return redirect()->back();
            } catch (\Exception $e) {
                session()->flash("flash_error", "Erro ao excluir a conta ID {$item->id}: " . $e->getMessage());
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
        $item = ContaReceber::findOrFail($id);
        if ($item->status) {
            session()->flash("flash_warning", "Esta conta já esta recebida!");
            return redirect()->route('conta-receber.index');
        }
        return view('conta-receber.pay', compact('item'));
    }

    public function payPut(Request $request, $id)
    {
        $usuario = Auth::user()->id;
        $caixa = Caixa::where('usuario_id', $usuario)->where('status', 1)->first();
        $item = ContaReceber::findOrFail($id);

        try {
            $item->valor_recebido = __convert_value_bd($request->valor_pago);
            $item->status = true;
            $item->data_recebimento = $request->data_recebimento;
            $item->tipo_pagamento = $request->tipo_pagamento;
            $item->caixa_id = $caixa->id;
            $item->save();

            $valorMenor = $item->valor_recebido < $item->valor_integral;

            if (isset($request->conta_empresa_id)) {

                $data = [
                    'conta_id' => $request->conta_empresa_id,
                    'descricao' => "Recebimento da conta " . $item->id,
                    'tipo_pagamento' => $request->tipo_pagamento,
                    'valor' => $item->valor_recebido,
                    'tipo' => 'entrada'
                ];
                $itemContaEmpresa = ItemContaEmpresa::create($data);
                $this->util->atualizaSaldo($itemContaEmpresa);
            }

            if ($valorMenor) {
                $diferenca = $item->valor_integral - $item->valor_recebido;
                session()->flash("flash_warning", "Conta recebida com valor parcial!");

                return redirect()->route('conta-receber.create', ['diferenca=' . $diferenca . '&id=' . $item->id]);
            }
            session()->flash("flash_success", "Conta recebida!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('conta-receber.index');
    }
}
