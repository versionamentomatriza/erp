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
                $config = Empresa::find($request->empresa_id);
                $caixa = Caixa::where('usuario_id', $request->usuario_id)
                    ->where('status', 1)
                    ->first();

                $numero_nfce = $config->numero_ultima_nfce_producao;
                if ($config->ambiente == 2) {
                    $numero_nfce = $config->numero_ultima_nfce_homologacao;
                }

                $request->merge([
                    'natureza_id'       => $config->natureza_id_pdv,
                    'emissor_nome'      => $config->nome,
                    'emissor_cpf_cnpj'  => $config->cpf_cnpj,
                    'ambiente'          => $config->ambiente,
                    'chave'             => '',
                    'cliente_id'        => $request->cliente_id,
                    'numero_serie'      => $config->numero_serie_nfce ?: 1,
                    'lista_id'          => $request->lista_id,
                    'numero'            => $numero_nfce + 1,
                    'cliente_nome'      => $request->cliente_nome ?? '',
                    'cliente_cpf_cnpj'  => $request->cliente_cpf_cnpj ?? '',
                    'estado'            => 'novo',
                    'total'             => __convert_value_bd($request->valor_total),
                    'desconto'          => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                    'valor_cashback'    => $request->valor_cashback ? __convert_value_bd($request->valor_cashback) : 0,
                    'acrescimo'         => $request->acrescimo ? __convert_value_bd($request->acrescimo) : 0,
                    'valor_produtos'    => __convert_value_bd($request->valor_total) ?? 0,
                    'valor_frete'       => $request->valor_frete ? __convert_value_bd($request->valor_frete) : 0,
                    'caixa_id'          => $caixa->id,
                    'local_id'          => $caixa->local_id,
                    'observacao'        => $request->observacao,
                    'dinheiro_recebido' => $request->valor_recebido ? __convert_value_bd($request->valor_recebido) : 0,
                    'troco'             => $request->troco ? __convert_value_bd($request->troco) : 0,
                    'tipo_pagamento'    => $request->tipo_pagamento_row ? '99' : $request->tipo_pagamento,
                    'cnpj_cartao'       => $request->cnpj_cartao ?? '',
                    'bandeira_cartao'   => $request->bandeira_cartao ?? '',
                    'cAut_cartao'       => $request->cAut_cartao ?? '',
                    'user_id'           => $request->usuario_id
                ]);

                $nfce = Nfce::create($request->all());

                /*
             * ========== CALCULAR DIAS POR TIPO ==========
             */
                $calcularDias = function (string $tipo, ?string $subtipo = null) {
                    $map = [
                        '01' => 0,   // Dinheiro
                        '17' => 0,   // PIX
                        '02' => 3,   // Cheque
                        '03' => 30,  // Crédito à vista
                        '04' => 1,   // Cartão de crédito
                        '05' => 30,  // Boleto
                        '06' => 30,  // Credito loja
                        '10' => 30,  // Vale Alimentação
                        '11' => 30,  // Vale Refeição
                        '12' => 30,  // Vale Presente
                        '13' => 30,  // Vale Combustível
                        '14' => 3,   // Duplicata Mercantil
                        '15' => 3,   // Transferência bancária
                        '16' => 0,   // Depósito bancário
                        '90' => 0,   // Sem pagamento
                        '99' => 0    // Outros
                    ];
                    return $map[$tipo] ?? 0;
                };

                /*
             * ========== ITENS PRODUTOS ==========
             */
                if ($request->produto_id) {
                    foreach ($request->produto_id as $i => $produtoId) {
                        $product = Produto::findOrFail($produtoId);
                        $variacao_id = $request->variacao_id[$i] ?? null;

                        ItemNfce::create([
                            'nfce_id'       => $nfce->id,
                            'produto_id'    => (int) $produtoId,
                            'quantidade'    => __convert_value_bd($request->quantidade[$i]),
                            'valor_unitario' => __convert_value_bd($request->valor_unitario[$i]),
                            'sub_total'     => __convert_value_bd($request->subtotal_item[$i]),
                            'perc_icms'     => __convert_value_bd($product->perc_icms),
                            'perc_pis'      => __convert_value_bd($product->perc_pis),
                            'perc_cofins'   => __convert_value_bd($product->perc_cofins),
                            'perc_ipi'      => __convert_value_bd($product->perc_ipi),
                            'cst_csosn'     => $product->cst_csosn,
                            'cst_pis'       => $product->cst_pis,
                            'cst_cofins'    => $product->cst_cofins,
                            'cst_ipi'       => $product->cst_ipi,
                            'cfop'          => $product->cfop_estadual,
                            'ncm'           => $product->ncm,
                            'variacao_id'   => $variacao_id,
                        ]);

                        if ($product->gerenciar_estoque) {
                            $this->util->reduzEstoque($product->id, __convert_value_bd($request->quantidade[$i]), $variacao_id, $caixa->local_id);
                        }

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

                /*
             * ========== ITENS SERVIÇOS ==========
             */
                if ($request->servico_id) {
                    foreach ($request->servico_id as $i => $servicoId) {
                        ItemServicoNfce::create([
                            'nfce_id'       => $nfce->id,
                            'servico_id'    => $servicoId,
                            'quantidade'    => __convert_value_bd($request->quantidade_servico[$i]),
                            'valor_unitario' => __convert_value_bd($request->valor_unitario_servico[$i]),
                            'sub_total'     => __convert_value_bd($request->valor_unitario_servico[$i]) * __convert_value_bd($request->quantidade_servico[$i]),
                            'observacao'    => ''
                        ]);
                    }
                }

                /*
             * ========== CONTA RECEBER E FATURA ==========
             * Sempre cria conta + fatura
             */
                if ($request->tipo_pagamento_row) {
                    foreach ($request->tipo_pagamento_row as $i => $tipoRow) {
                        $valorParcela = __convert_value_bd($request->valor_integral_row[$i]);
                        $dias         = $calcularDias($tipoRow);
                        $status       = $dias === 0 ? 1 : 0;
                        $valorRecebido = $status ? $valorParcela : 0;

                        ContaReceber::create([
                            'nfe_id'            => null,
                            'nfce_id'           => $nfce->id,
                            'cliente_id'        => $request->cliente_id,
                            'data_vencimento'   => $request->data_vencimento_row[$i],
                            'data_recebimento'  => $status ? now()->copy()->addDays($dias) : null,
                            'valor_integral'    => $valorParcela,
                            'valor_recebido'    => $valorRecebido,
                            'status'            => $status,
                            'referencia'        => "Parcela " . ($i + 1) . " da venda código {$nfce->id}",
                            'empresa_id'        => $request->empresa_id,
                            'tipo_pagamento'    => $tipoRow,
                            'observacao'        => $request->obs_row[$i] ?? '',
                            'local_id'          => $caixa->local_id
                        ]);

                        FaturaNfce::create([
                            'nfce_id'           => $nfce->id,
                            'tipo_pagamento'    => $tipoRow,
                            'data_vencimento'   => $request->data_vencimento_row[$i],
                            'valor'             => $valorParcela
                        ]);
                    }
                } else {
                    $tipo          = $request->tipo_pagamento;
                    $valorIntegral = __convert_value_bd($request->valor_total);
                    $dias          = $calcularDias($tipo);
                    $status        = $dias === 0 ? 1 : 0;
                    $valorRecebido = $status ? $valorIntegral : 0;

                    ContaReceber::create([
                        'nfe_id'            => null,
                        'nfce_id'           => $nfce->id,
                        'cliente_id'        => $request->cliente_id,
                        'data_vencimento'   => $request->data_vencimento,
                        'data_recebimento'  => $status ? now()->copy()->addDays($dias) : null,
                        'valor_integral'    => $valorIntegral,
                        'valor_recebido'    => $valorRecebido,
                        'status'            => $status,
                        'referencia'        => $request->referencia,
                        'empresa_id'        => $request->empresa_id,
                        'tipo_pagamento'    => $tipo,
                        'observacao'        => $request->observacao,
                        'local_id'          => $caixa->local_id
                    ]);

                    FaturaNfce::create([
                        'nfce_id'           => $nfce->id,
                        'tipo_pagamento'    => $tipo,
                        'data_vencimento'   => date('Y-m-d'),
                        'valor'             => $valorIntegral
                    ]);
                }

                /*
             * Continua com comissão, cashback, pedidos e delivery...
             */

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
                $request->merge([
                    'natureza_id' => $config->natureza_id_pdv,
                    'emissor_nome' => $config->nome,
                    'emissor_cpf_cnpj' => $config->cpf_cnpj,
                    'ambiente' => $config->ambiente,
                    'cliente_id' => $request->cliente_id,
                    'numero_serie' => $config->numero_serie_nfce,
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
                    'observacao' => $request->observacao,
                    'dinheiro_recebido' => $request->valor_recebido ? __convert_value_bd($request->valor_recebido) : 0,
                    'troco' => $request->troco ? __convert_value_bd($request->troco) : 0,
                    'tipo_pagamento' => isset($request->tipo_pagamento_row[0]) ? $request->tipo_pagamento_row[0] : $request->tipo_pagamento,
                    'cnpj_cartao' => $request->cnpj_cartao ?? '',
                    'bandeira_cartao' => $request->bandeira_cartao ?? '',
                    'cAut_cartao' => $request->cAut_cartao ?? '',
                ]);

                $item->fill($request->all())->save();

                if ($request->produto_id) {
                    foreach ($item->itens as $i) {
                        if ($i->produto->gerenciar_estoque) {
                            $this->util->incrementaEstoque($i->produto->id, $i->quantidade, $i->variacao_id, $item->local_id);
                        }
                    }
                    $item->itens()->delete();
                    for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                        $product = Produto::findOrFail($request->produto_id[$i]);
                        $variacao_id = isset($request->variacao_id[$i]) ? $request->variacao_id[$i] : null;
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

                        $tipo = 'reducao';
                        $codigo_transacao = $item->id;
                        $tipo_transacao = 'venda_nfce';

                        $this->util->movimentacaoProduto($product->id, __convert_value_bd($request->quantidade[$i]), $tipo, $codigo_transacao, $tipo_transacao, $request->usuario_id);
                    }
                }

                if ($request->tipo_pagamento_row) {
                    $item->fatura()->delete();
                    $item->contaReceber()->delete();
                    for ($i = 0; $i < sizeof($request->tipo_pagamento_row); $i++) {

                        $vencimento = $request->data_vencimento_row[$i];
                        $dataAtual = date('Y-m-d');
                        if (strtotime($vencimento) > strtotime($dataAtual)) {
                            ContaReceber::create([
                                'nfe_id' => null,
                                'nfce_id' => $item->id,
                                'cliente_id' => $request->cliente_id,
                                'data_vencimento' => $request->data_vencimento_row[$i],
                                'data_recebimento' => $request->data_vencimento_row[$i],
                                'valor_integral' => __convert_value_bd($request->valor_integral_row[$i]),
                                'valor_recebido' => 0,
                                'status' => 0,
                                'referencia' => "Parcela $i+1 da venda código $item->id",
                                'empresa_id' => $request->empresa_id,
                                'juros' => 0,
                                'multa' => 0,
                                'observacao' => $request->obs_row[$i] ?? '',
                                'tipo_pagamento' => $request->tipo_pagamento_row[$i],
                                'local_id' => $item->local_id
                            ]);
                        }
                    }
                    for ($i = 0; $i < sizeof($request->tipo_pagamento_row); $i++) {
                        if ($request->tipo_pagamento_row[$i]) {
                            FaturaNfce::create([
                                'nfce_id' => $item->id,
                                'tipo_pagamento' => $request->tipo_pagamento_row[$i],
                                'data_vencimento' => $request->data_vencimento_row[$i],
                                'valor' => __convert_value_bd($request->valor_integral_row[$i])
                            ]);
                        }
                    }
                } else {
                    $item->fatura()->delete();
                    FaturaNfce::create([
                        'nfce_id' => $item->id,
                        'tipo_pagamento' => $request->tipo_pagamento,
                        'data_vencimento' => date('Y-m-d'),
                        'valor' => __convert_value_bd($request->valor_total)
                    ]);
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
