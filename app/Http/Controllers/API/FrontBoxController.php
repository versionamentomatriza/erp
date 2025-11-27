<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Caixa;
use App\Models\ComissaoVenda;
use App\Models\ContaReceber;
use App\Models\Empresa;
use App\Models\FaturaNfce;
use App\Models\ItemServicoNfce;
use App\Models\ItemNfce;
use App\Models\ItemPedido;
use App\Models\Agendamento;
use App\Models\CashBackCliente;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\ItemListaPreco;
use App\Models\PedidoDelivery;
use App\Models\MotoboyComissao;
use App\Models\ItemPedidoDelivery;
use App\Models\Nfce;
use App\Models\ProdutoVariacao;
use App\Models\Produto;
use App\Models\UsuarioAcesso;
use App\Models\CashBackConfig;
use App\Models\CategoriaConta;
use App\Models\Funcionario;
use App\Utils\EstoqueUtil;
use Dflydev\DotAccessData\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Utils\WhatsAppUtil;

class FrontBoxController extends Controller
{
    protected $util;
    protected $utilWhatsApp;

    public function __construct(EstoqueUtil $util, WhatsAppUtil $utilWhatsApp)
    {
        $this->util = $util;
        $this->utilWhatsApp = $utilWhatsApp;
    }

    public function linhaProdutoVenda(Request $request)
    {
        try {

            $qtd = $request->qtd;
            $value_unit = __convert_value_bd($request->value_unit);
            $sub_total = __convert_value_bd($request->sub_total);
            $product_id = $request->product_id;
            $variacao_id = $request->variacao_id;
            $key = $request->key;

            $variacao = null;
            if ($variacao_id) {
                $variacao = ProdutoVariacao::findOrfail($variacao_id);
            }

            $product = Produto::findOrFail($product_id);
            if ($product->gerenciar_estoque == true) {
                if ($product->combo) {
                    $estoqueMsg = $this->util->verificaEstoqueCombo($product, (float)$qtd);
                    if ($estoqueMsg != "") {
                        return response()->json($estoqueMsg, 401);
                    }
                } else {
                    if (!isset($product->estoque)) {
                        return response()->json("Produto com estoque insuficiente", 401);
                    } else if ($product->estoque->quantidade < $qtd) {
                        return response()->json("Produto com estoque insuficiente", 401);
                    }
                }
            }
            return view(
                'front_box.partials.row_frontBox',
                compact('product', 'qtd', 'value_unit', 'sub_total', 'key', 'variacao_id', 'variacao')
            );
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function linhaProdutoVendaAdd(Request $request)
    {
        $product = Produto::findOrFail($request->id);
        $lista_id = $request->lista_id;

        if ($product->variacao_modelo_id) {
            return response("produto com variação", 402);
        }
        $qtd = __convert_value_bd($request->qtd);
        try {
            $qtd = (float)$qtd + 1;
        } catch (\Exception $e) {
        }
        try {

            if ($lista_id) {

                $itemLista = ItemListaPreco::where('lista_id', $lista_id)
                    ->where('produto_id', $product->id)
                    ->first();
                if ($itemLista != null) {
                    $product->valor_unitario = $itemLista->valor;
                }
            }

            $value_unit = $product->valor_unitario;
            $sub_total = $product->valor_unitario;
            $variacao_id = $request->variacao_id;
            $product_id = $product->id;

            // $key = $request->key;
            if ($product->gerenciar_estoque == true) {
                if ($product->combo) {
                    $estoqueMsg = $this->util->verificaEstoqueCombo($product, (float)$qtd);
                    if ($estoqueMsg != "") {
                        return response()->json($estoqueMsg, 401);
                    }
                } else {
                    if (!isset($product->estoque)) {
                        return response()->json("Produto com estoque insuficiente", 401);
                    } else if ($product->estoque->quantidade < $qtd) {
                        return response()->json("Produto com estoque insuficiente", 401);
                    }
                }
            }
            $variacao = null;

            $qtd = __moeda($qtd);
            return view(
                'front_box.partials.row_frontBox',
                compact('product', 'qtd', 'value_unit', 'sub_total', 'variacao_id', 'variacao')
            );
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function linhaParcelaVenda(Request $request)
    {
        try {
            $tipo_pagamento_row = $request->tipo_pagamento_row;
            $data_vencimento_row = $request->data_vencimento_row;
            $valor_integral_row = $request->valor_integral_row;
            $quantidade = $request->quantidade;
            $obs_row = $request->obs_row;

            $tipo = Nfce::getTipoPagamento($tipo_pagamento_row);
            return view('front_box.partials.row_pagamento_multiplo', compact(
                'valor_integral_row',
                'data_vencimento_row',
                'quantidade',
                'tipo',
                'obs_row',
                'tipo_pagamento_row'
            ));
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    private function rateioCashBack($valor_cashback, $nfce)
    {
        $data = CashBackCliente::where('empresa_id', $nfce->empresa_id)
            ->where('status', 1)
            ->where('cliente_id', $nfce->cliente_id)
            ->get();

        $cliente = Cliente::findOrFail($nfce->cliente_id);
        $cliente->valor_cashback -= $valor_cashback;
        $cliente->save();

        $soma = 0;
        foreach ($data as $i) {
            if ($soma < $valor_cashback) {
                $valorCredito = $i->valor_credito;
                if ($valorCredito <= $valor_cashback) {
                    $i->status = 0;
                    $i->valor_credito = 0;
                    $i->save();
                    $soma += $valorCredito;
                } else {
                    $i->valor_credito -= ($valor_cashback - $soma);
                    $i->save();
                    $soma = $valor_cashback;
                }
            }
        }
    }

    private function saveCashBack($venda)
    {
        $config = CashBackConfig::where('empresa_id', $venda->empresa_id)
            ->first();
        if ($venda->cliente && $config != null) {

            if ($venda->total >= $config->valor_minimo_venda) {
                $valor_percentual = $config->valor_percentual;
                $dias_expiracao = $config->dias_expiracao;

                $valor_credito = $venda->total * ($valor_percentual / 100);
                $data = [
                    'empresa_id' => $venda->empresa_id,
                    'cliente_id' => $venda->cliente_id,
                    'tipo' => 'pdv',
                    'venda_id' => $venda->id,
                    'valor_venda' => $venda->total,
                    'valor_credito' => $valor_credito,
                    'valor_percentual' => $valor_percentual,
                    'status' => 1,
                    'data_expiracao' => date('Y-m-d', strtotime("+$dias_expiracao days"))
                ];
                $cashBackCliente = CashBackCliente::create($data);

                $cliente = $venda->cliente;
                $cliente->valor_cashback = $cliente->valor_cashback + $valor_credito;
                $cliente->save();

                $this->sendWhatsMessage($cashBackCliente);
            }
        }
    }

    private function sendWhatsMessage($cashBackCliente)
    {
        if ($cashBackCliente->cliente->celular != '') {

            $config = CashBackConfig::where('empresa_id', $cashBackCliente->cliente->empresa_id)
                ->first();

            $message = $config->mensagem_padrao_whatsapp;
            $telefone = "55" . preg_replace('/[^0-9]/', '', $cashBackCliente->cliente->telefone);

            $nomeCliente = $cashBackCliente->cliente->razao_social;
            if ($cashBackCliente->cliente->nome_fantasia != '') {
                $nomeCliente = $cashBackCliente->cliente->nome_fantasia;
            }

            $message = str_replace("{credito}", moeda($cashBackCliente->valor_credito), $message);
            $message = str_replace("{expiracao}", __date($cashBackCliente->data_expiracao, 0), $message);
            $message = str_replace("{nome}", $nomeCliente, $message);

            $retorno = $this->utilWhatsApp->sendMessage($telefone, $message, $cashBackCliente->cliente->empresa_id);
        }
    }

    public function store(Request $request)
    {
        try {
            $nfce = DB::transaction(function () use ($request) {
                // ------- Pré-processamento / configurações -------
                $config = Empresa::find($request->empresa_id);
                $caixa = Caixa::where('usuario_id', $request->usuario_id)
                    ->where('status', 1)
                    ->first();
                $categoriaContaDefault = CategoriaConta::where('nome', 'Receita de Mercadorias')->first();

                // Número sequencial conforme ambiente
                $numero_nfce = $config->numero_ultima_nfce_producao;
                if ($config->ambiente == 2) {
                    $numero_nfce = $config->numero_ultima_nfce_homologacao;
                }

                // Merge campos para criar NFCE
                $request->merge([
                    'natureza_id' => $config->natureza_id_pdv,
                    'emissor_nome' => $config->nome,
                    'emissor_cpf_cnpj' => $config->cpf_cnpj,
                    'ambiente' => $config->ambiente,
                    'chave' => '',
                    'cliente_id' => $request->cliente_id,
                    'numero_serie' => $config->numero_serie_nfce ?: 1,
                    'lista_id' => $request->lista_id,
                    'numero' => $numero_nfce + 1,
                    'cliente_nome' => $request->cliente_nome ?? '',
                    'cliente_cpf_cnpj' => $request->cliente_cpf_cnpj ?? '',
                    'estado' => 'novo',
                    'total' => __convert_value_bd($request->valor_total),
                    'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                    'valor_cashback' => $request->valor_cashback ? __convert_value_bd($request->valor_cashback) : 0,
                    'acrescimo' => $request->acrescimo ? __convert_value_bd($request->acrescimo) : 0,
                    'valor_produtos' => __convert_value_bd($request->valor_total) ?? 0,
                    'valor_frete' => $request->valor_frete ? __convert_value_bd($request->valor_frete) : 0,
                    'caixa_id' => $caixa->id ?? null,
                    'local_id' => $caixa->local_id ?? null,
                    'observacao' => $request->observacao,
                    'dinheiro_recebido' => $request->valor_recebido ? __convert_value_bd($request->valor_recebido) : 0,
                    'troco' => $request->troco ? __convert_value_bd($request->troco) : 0,
                    // se houver pagamento múltiplo, marca tipo como '99' (outros)
                    'tipo_pagamento' => $request->tipo_pagamento_row ? '99' : $request->tipo_pagamento,
                    'cnpj_cartao' => $request->cnpj_cartao ?? '',
                    'bandeira_cartao' => $request->bandeira_cartao ?? '',
                    'cAut_cartao' => $request->cAut_cartao ?? '',
                    'user_id' => $request->usuario_id
                ]);

                // Cria NFCE
                $nfce = Nfce::create($request->all());

                // ------- Itens produtos -------
                if (!empty($request->produto_id)) {
                    foreach ($request->produto_id as $i => $produtoId) {
                        $product = Produto::findOrFail($produtoId);
                        $variacao_id = $request->variacao_id[$i] ?? null;

                        ItemNfce::create([
                            'nfce_id' => $nfce->id,
                            'produto_id' => (int) $produtoId,
                            'quantidade' => __convert_value_bd($request->quantidade[$i]),
                            'valor_unitario' => __convert_value_bd($request->valor_unitario[$i]),
                            'sub_total' => __convert_value_bd($request->subtotal_item[$i]),
                            'perc_icms' => __convert_value_bd($product->perc_icms),
                            'perc_pis' => __convert_value_bd($product->perc_pis),
                            'perc_cofins' => __convert_value_bd($product->perc_cofins),
                            'perc_ipi' => __convert_value_bd($product->perc_ipi),
                            'cst_csosn' => $product->cst_csosn,
                            'cst_pis' => $product->cst_pis,
                            'cst_cofins' => $product->cst_cofins,
                            'cst_ipi' => $product->cst_ipi,
                            'cfop' => $product->cfop_estadual,
                            'ncm' => $product->ncm,
                            'variacao_id' => $variacao_id,
                        ]);

                        if ($product->gerenciar_estoque) {
                            $this->util->reduzEstoque($product->id, __convert_value_bd($request->quantidade[$i]), $variacao_id, $caixa->local_id);
                        }

                        // movimentação do produto
                        $this->util->movimentacaoProduto(
                            $product->id,
                            __convert_value_bd($request->quantidade[$i]),
                            'reducao',
                            $nfce->id,
                            'venda_nfce',
                            $request->usuario_id,
                            $variacao_id
                        );
                    }
                }

                // ------- Itens serviços -------
                if (!empty($request->servico_id)) {
                    foreach ($request->servico_id as $i => $servicoId) {
                        ItemServicoNfce::create([
                            'nfce_id' => $nfce->id,
                            'servico_id' => $servicoId,
                            'quantidade' => __convert_value_bd($request->quantidade_servico[$i]),
                            'valor_unitario' => __convert_value_bd($request->valor_unitario_servico[$i]),
                            'sub_total' => __convert_value_bd($request->valor_unitario_servico[$i]) * __convert_value_bd($request->quantidade_servico[$i]),
                            'observacao' => ''
                        ]);
                    }
                }

                // ------- Agendamento -------
                if (!empty($request->agendamento_id)) {
                    $agendamento = Agendamento::findOrFail($request->agendamento_id);
                    $agendamento->status = 1;
                    $agendamento->nfce_id = $nfce->id;
                    $agendamento->save();
                }

                // ------- Categoria receita (corrigido) -------
                $categoriaConta = CategoriaConta::where('nome', 'LIKE', 'Receita de Mercadorias')->first();

                // ------- Regras de datas / pagamentos -------
                // Helper interno para calcular dias (tratamento PIX maquininha)
                // DÚVIDA AQUI
                $calcularDias = function (string $tipo, ?string $subtipo = null) {
                    $map = [
                        '01' => 0,
                        '17' => 0,
                        '02' => 3,
                        '03' => 30,
                        '04' => 1,
                        '05' => 30,
                        '06' => 30,
                        '10' => 30,
                        '11' => 30,
                        '12' => 30,
                        '13' => 30,
                        '14' => 3,
                        '15' => 3,
                        '16' => 0,
                        '90' => 0,
                        '99' => 0
                    ];

                    // caso especial: PIX via maquininha
                    if ($tipo === '17' && $subtipo === 'maquininha') {
                        return 1; // por padrão D+1 (ajuste se necessário)
                    }

                    return $map[$tipo] ?? 0;
                };

                $naoRecebidoImediato = ['06', '90', '99'];

                // ------- Criação de contas e faturas -------
                if (!empty($request->tipo_pagamento_row)) {
                    // pagamento múltiplo / parcelado: cria uma conta por parcela e fatura correspondente
                    foreach ($request->tipo_pagamento_row as $i => $tipoRow) {
                        $subtipoRow = $request->subtipo_pagamento_row[$i] ?? null; // pode vir do front
                        $dias = $calcularDias($tipoRow, $subtipoRow);
                        $dataRecebimento = now()->copy()->addDays($dias);

                        $valorParcela = __convert_value_bd($request->valor_integral_row[$i]);
                        $valorRecebido = !in_array($tipoRow, $naoRecebidoImediato) ? $valorParcela : 0;
                        $status = !in_array($tipoRow, $naoRecebidoImediato) ? 1 : 0;

                        ContaReceber::create([
                            'descricao' => 'Venda PDV',
                            'nfe_id' => null,
                            'nfce_id' => $nfce->id,
                            'cliente_id' => $request->cliente_id,
                            'data_vencimento' => $request->data_vencimento_row[$i],
                            'data_competencia' => $request->data_vencimento_row[$i],
                            'data_recebimento' => $dataRecebimento,
                            'valor_integral' => $valorParcela,
                            'valor_recebido' => $valorRecebido,
                            'status' => $status,
                            'referencia' => "Parcela " . ($i + 1) . " da venda código {$nfce->id}",
                            'empresa_id' => $request->empresa_id,
                            'tipo_pagamento' => $tipoRow,
                            'observacao' => $request->obs_row[$i] ?? '',
                            'local_id' => $caixa->local_id,
                            'categoria_conta_id' => $categoriaConta->id ?? null
                        ]);

                        // cria fatura correspondente
                        FaturaNfce::create([
                            'nfce_id' => $nfce->id,
                            'tipo_pagamento' => $tipoRow,
                            'data_vencimento' => $request->data_vencimento_row[$i],
                            'valor' => $valorParcela
                        ]);
                    }
                } else {
                    // pagamento único: cria apenas uma conta e uma fatura
                    $tipo = $request->tipo_pagamento;
                    $subtipo = $request->subtipo_pagamento ?? null;
                    $dias = $calcularDias($tipo, $subtipo);
                    $dataRecebimento = now()->copy()->addDays($dias);

                    $valorIntegral = __convert_value_bd($request->valor_total);
                    $valorRecebido = !in_array($tipo, $naoRecebidoImediato) ? __convert_value_bd($request->valor_total) : 0;
                    $status = !in_array($tipo, $naoRecebidoImediato) ? 1 : 0;

                    ContaReceber::create([
                        'descricao' => 'Venda PDV',
                        'nfe_id' => null,
                        'nfce_id' => $nfce->id,
                        'data_vencimento' => $request->data_vencimento,
                        'data_competencia' => $request->data_vencimento,
                        'data_recebimento' => $dataRecebimento,
                        'valor_integral' => __convert_value_bd($request->valor_total),
                        'valor_recebido' => $valorRecebido,
                        'referencia' => $request->referencia,
                        'status' => $status,
                        'empresa_id' => $request->empresa_id,
                        'cliente_id' => $request->cliente_id,
                        'tipo_pagamento' => $tipo,
                        'observacao' => $request->observacao,
                        'local_id' => $caixa->local_id,
                        'categoria_conta_id' => $categoriaConta->id ?? null
                    ]);

                    FaturaNfce::create([
                        'nfce_id'        => $nfce->id,
                        'tipo_pagamento' => $tipo,
                        'data_vencimento' => date('Y-m-d'),
                        'valor'          => $valorIntegral
                    ]);
                }

                // ------- Comissões -------
                if (!empty($request->funcionario_id)) {
                    $funcionario = Funcionario::where('empresa_id', $request->empresa_id)->first();
                    $comissao = $funcionario->comissao ?? 0;
                    $valorRetorno = $this->calcularComissaoVenda($nfce, $comissao);
                    ComissaoVenda::create([
                        'funcionario_id' => $request->funcionario_id,
                        'nfe_id'         => null,
                        'nfce_id'        => $nfce->id,
                        'tabela'         => 'nfce',
                        'valor'          => $valorRetorno,
                        'valor_venda'    => __convert_value_bd($request->valor_total),
                        'status'         => 0,
                        'empresa_id'     => $request->empresa_id
                    ]);
                }

                // ------- Cashback / rateio -------
                if (isset($request->valor_cashback) && $request->valor_cashback == 0 && ($request->permitir_credito ?? false)) {

                    $this->saveCashBack($nfce);
                } elseif (isset($request->valor_cashback) && $request->valor_cashback > 0) {
                    $this->rateioCashBack($request->valor_cashback, $nfce);
                }

                // ------- Pedido / Delivery updates -------
                if (isset($request->pedido_id)) {
                    $pedido = Pedido::findOrfail($request->pedido_id);
                    $pedido->status = 0;
                    $pedido->em_atendimento = 0;
                    $pedido->nfce_id = $nfce->id;
                    ItemPedido::where('pedido_id', $pedido->id)->update(['estado' => 'finalizado']);
                    $pedido->save();
                }

                if (!empty($request->pedido_delivery_id)) {
                    $pedido = PedidoDelivery::findOrfail($request->pedido_delivery_id);
                    $pedido->estado = 'finalizado';
                    $pedido->finalizado = 1;
                    $pedido->horario_entrega = date('H:i');
                    $pedido->nfce_id = $nfce->id;

                    if ($pedido->motoboy_id) {
                        MotoboyComissao::create([
                            'empresa_id'         => $request->empresa_id,
                            'pedido_id'          => $pedido->id,
                            'motoboy_id'         => $pedido->motoboy_id,
                            'valor'              => $pedido->comissao_motoboy,
                            'valor_total_pedido' => __convert_value_bd($request->valor_total),
                            'status'             => 0
                        ]);
                    }

                    $this->sendMessageWhatsApp($pedido, "Seu pedido foi concluído e logo sera entregue!");
                    ItemPedidoDelivery::where('pedido_id', $pedido->id)->update(['estado' => 'finalizado']);
                    $pedido->save();
                }

                return $nfce;
            });

            return response()->json($nfce, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage() . ", line: " . $e->getLine() . ", file: " . $e->getFile(), 401);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $nfce = DB::transaction(function () use ($request, $id) {
                $item = Nfce::findOrFail($id);
                $config = Empresa::find($request->empresa_id);

                $numero_nfce = $config->numero_ultima_nfce_producao;
                if ($config->ambiente == 2) {
                    $numero_nfce = $config->numero_ultima_nfce_homologacao;
                }

                $caixa = Caixa::where('usuario_id', $request->usuario_id)
                    ->where('status', 1)
                    ->first();

                // merge dos dados no request
                $request->merge([
                    'natureza_id' => $config->natureza_id_pdv,
                    'emissor_nome' => $config->nome,
                    'emissor_cpf_cnpj' => $config->cpf_cnpj,
                    'ambiente' => $config->ambiente,
                    'numero_serie' => $config->numero_serie_nfce,
                    'numero' => $numero_nfce + 1,
                    'estado' => 'novo',
                    'total' => __convert_value_bd($request->valor_total),
                    'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                    'valor_cashback' => $request->valor_cashback ? __convert_value_bd($request->valor_cashback) : 0,
                    'acrescimo' => $request->acrescimo ? __convert_value_bd($request->acrescimo) : 0,
                    'valor_produtos' => __convert_value_bd($request->valor_total) ?? 0,
                    'valor_frete' => $request->valor_frete ? __convert_value_bd($request->valor_frete) : 0,
                    'dinheiro_recebido' => $request->valor_recebido ? __convert_value_bd($request->valor_recebido) : 0,
                    'troco' => $request->troco ? __convert_value_bd($request->troco) : 0,
                    'tipo_pagamento' => isset($request->tipo_pagamento_row[0]) ? $request->tipo_pagamento_row[0] : $request->tipo_pagamento,
                    'cnpj_cartao' => $request->cnpj_cartao ?? '',
                    'bandeira_cartao' => $request->bandeira_cartao ?? '',
                    'cAut_cartao' => $request->cAut_cartao ?? '',
                ]);

                // Atualiza dados principais da NFC-e
                $item->fill($request->all())->save();

                /**
                 * ===============================
                 * ITENS DE PRODUTO
                 * ===============================
                 */
                if ($request->produto_id) {
                    // devolve estoque dos itens antigos
                    foreach ($item->itens as $i) {
                        if ($i->produto->gerenciar_estoque) {
                            $this->util->incrementaEstoque($i->produto->id, $i->quantidade, $i->variacao_id, $item->local_id);
                        }
                    }
                    $item->itens()->delete();

                    // adiciona os novos itens
                    for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                        $product = Produto::findOrFail($request->produto_id[$i]);
                        $variacao_id = $request->variacao_id[$i] ?? null;

                        ItemNfce::create([
                            'nfce_id' => $item->id,
                            'produto_id' => (int)$request->produto_id[$i],
                            'quantidade' => __convert_value_bd($request->quantidade[$i]),
                            'valor_unitario' => __convert_value_bd($request->valor_unitario[$i]),
                            'sub_total' => __convert_value_bd($request->subtotal_item[$i]),
                            'perc_icms' => __convert_value_bd($product->perc_icms),
                            'perc_pis' => __convert_value_bd($product->perc_pis),
                            'perc_cofins' => __convert_value_bd($product->perc_cofins),
                            'perc_ipi' => __convert_value_bd($product->perc_ipi),
                            'cst_csosn' => $product->cst_csosn,
                            'cst_pis' => $product->cst_pis,
                            'cst_cofins' => $product->cst_cofins,
                            'cst_ipi' => $product->cst_ipi,
                            'cfop' => $product->cfop_estadual,
                            'ncm' => $product->ncm,
                            'variacao_id' => $variacao_id,
                        ]);

                        if ($product->gerenciar_estoque) {
                            $this->util->reduzEstoque($product->id, __convert_value_bd($request->quantidade[$i]), $variacao_id, $item->local_id);
                        }

                        $this->util->movimentacaoProduto(
                            $product->id,
                            __convert_value_bd($request->quantidade[$i]),
                            'reducao',
                            $item->id,
                            'venda_nfce',
                            $request->usuario_id
                        );
                    }
                }

                /**
                 * ===============================
                 * FATURA + CONTAS A RECEBER
                 * ===============================
                 */
                $item->fatura()->delete();
                $item->contaReceber()->delete();

                if ($request->tipo_pagamento_row) {
                    for ($i = 0; $i < sizeof($request->tipo_pagamento_row); $i++) {
                        ContaReceber::create([
                            'nfe_id' => null,
                            'nfce_id' => $item->id,
                            'cliente_id' => $request->cliente_id,
                            'data_vencimento' => $request->data_vencimento_row[$i],
                            'data_competencia' => $request->data_vencimento_row[$i],
                            'data_recebimento' => $request->data_vencimento_row[$i],
                            'valor_integral' => __convert_value_bd($request->valor_integral_row[$i]),
                            'valor_recebido' => 0,
                            'status' => 0,
                            'referencia' => "Parcela " . ($i + 1) . " da venda código $item->id",
                            'empresa_id' => $request->empresa_id,
                            'observacao' => $request->obs_row[$i] ?? '',
                            'tipo_pagamento' => $request->tipo_pagamento_row[$i],
                            'local_id' => $item->local_id
                        ]);

                        FaturaNfce::create([
                            'nfce_id' => $item->id,
                            'tipo_pagamento' => $request->tipo_pagamento_row[$i],
                            'data_vencimento' => $request->data_vencimento_row[$i],
                            'valor' => __convert_value_bd($request->valor_integral_row[$i])
                        ]);
                    }
                } else {
                    FaturaNfce::create([
                        'nfce_id' => $item->id,
                        'tipo_pagamento' => $request->tipo_pagamento,
                        'data_vencimento' => date('Y-m-d'),
                        'valor' => __convert_value_bd($request->valor_total)
                    ]);
                }

                /**
                 * ===============================
                 * OUTRAS REGRAS (comissão, cashback, pedidos, delivery etc.)
                 * ===============================
                 */
                if ($request->funcionario_id != null) {
                    $funcionario = Funcionario::where('empresa_id', $request->empresa_id)->first();
                    if ($funcionario) {
                        $valorRetorno = $this->calcularComissaoVenda($item, $funcionario->comissao);
                        ComissaoVenda::create([
                            'funcionario_id' => $request->funcionario_id,
                            'nfe_id' => null,
                            'nfce_id' => $item->id,
                            'tabela' => 'nfce',
                            'valor' => $valorRetorno,
                            'valor_venda' => __convert_value_bd($request->valor_total),
                            'status' => 0,
                            'empresa_id' => $request->empresa_id
                        ]);
                    }
                }

                if ($request->valor_cashback > 0) {
                    $this->rateioCashBack($request->valor_cashback, $item);
                } elseif ($request->permitir_credito) {
                    $this->saveCashBack($item);
                }

                if (isset($request->pedido_id)) {
                    $pedido = Pedido::findOrFail($request->pedido_id);
                    $pedido->status = 0;
                    $pedido->em_atendimento = 0;
                    $pedido->nfce_id = $item->id;
                    ItemPedido::where('pedido_id', $pedido->id)->update(['estado' => 'finalizado']);
                    $pedido->save();
                }

                if (isset($request->pedido_delivery_id)) {
                    $pedido = PedidoDelivery::findOrFail($request->pedido_delivery_id);
                    $pedido->estado = 'finalizado';
                    $pedido->finalizado = 1;
                    $pedido->horario_entrega = date('H:i');
                    $pedido->nfce_id = $item->id;

                    if ($pedido->motoboy_id) {
                        MotoboyComissao::create([
                            'empresa_id' => $request->empresa_id,
                            'pedido_id' => $pedido->id,
                            'motoboy_id' => $pedido->motoboy_id,
                            'valor' => $pedido->comissao_motoboy,
                            'valor_total_pedido' => __convert_value_bd($request->valor_total),
                            'status' => 0
                        ]);
                    }

                    $this->sendMessageWhatsApp($pedido, "Seu pedido foi concluído e logo será entregue!");
                    ItemPedidoDelivery::where('pedido_id', $pedido->id)->update(['estado' => 'finalizado']);
                    $pedido->save();
                }

                return $item;
            });

            return response()->json($nfce, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage() . ", line: " . $e->getLine() . ", file: " . $e->getFile(), 401);
        }
    }

    private function sendMessageWhatsApp($pedido, $texto)
    {
        $telefone = "55" . preg_replace('/[^0-9]/', '', $pedido->cliente->telefone);
        $retorno = $this->utilWhatsApp->sendMessage($telefone, $texto, $pedido->empresa_id);
        // dd($retorno);
    }

    private function calcularComissaoVenda($nfce, $comissao)
    {
        $valorRetorno = 0;
        foreach ($nfce->itens as $i) {
            if ($i->produto->perc_comissao > 0) {
                $valorRetorno += (($i->valor_unitario * $i->quantidade) * $i->produto->perc_comissao) / 100;
            } else {
                $valorRetorno += (($i->valor_unitario * $i->quantidade) * $comissao) / 100;
            }
        }
        return $valorRetorno;
    }

    public function buscaFuncionario($id)
    {
        $item = Funcionario::findOrFail($id);
        return response()->json($item, 200);
    }
}