<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Models\Fornecedor;
use App\Models\Cliente;
use App\Models\ProdutoFornecedor;
use App\Models\Empresa;
use App\Models\FaturaNfe;
use App\Models\ItemNfe;
use App\Models\ProdutoLocalizacao;
use App\Models\NaturezaOperacao;
use Illuminate\Http\Request;
use App\Models\Nfe;
use App\Models\PedidoEcommerce;
use App\Models\Cotacao;
use App\Models\Reserva;
use App\Models\Produto;
use App\Models\Inutilizacao;
use App\Models\Transportadora;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Laravel\Ui\Presets\React;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\NFe\DanfeSimples;
use NFePHP\DA\NFe\DanfeEtiqueta;
use App\Models\Funcionario;
use App\Services\NFeService;
use NFePHP\DA\NFe\Daevento;
use App\Utils\EstoqueUtil;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\NuvemShopPedido;
use App\Models\WoocommercePedido;
use App\Models\OrdemServico;
use App\Models\PedidoMercadoLivre;
use Dompdf\Dompdf;
use carbon\Carbon;
use App\Models\CentroCusto;
use Illuminate\Support\Facades\Log;
use File;

class NfeController extends Controller
{
    protected $util;


    public function __construct(EstoqueUtil $util)
    {
        $this->util = $util;

        if (!is_dir(public_path('xml_nfe'))) {
            mkdir(public_path('xml_nfe'), 0777, true);
        }
        if (!is_dir(public_path('xml_nfe_cancelada'))) {
            mkdir(public_path('xml_nfe_cancelada'), 0777, true);
        }
        if (!is_dir(public_path('xml_nfe_correcao'))) {
            mkdir(public_path('xml_nfe_correcao'), 0777, true);
        }
        if (!is_dir(public_path('danfe_temp'))) {
            mkdir(public_path('danfe_temp'), 0777, true);
        }

        if (!is_dir(public_path('danfe'))) {
            mkdir(public_path('danfe'), 0777, true);
        }

        $this->middleware('permission:nfe_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:nfe_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:nfe_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:nfe_delete', ['only' => ['destroy']]);
    }

    private function setNumeroSequencial()
    {
        $docs = Nfe::where('empresa_id', request()->empresa_id)
            ->where('numero_sequencial', null)
            ->get();

        $last = Nfe::where('empresa_id', request()->empresa_id)
            ->orderBy('numero_sequencial', 'desc')
            ->where('numero_sequencial', '>', 0)->first();
        $numero = $last != null ? $last->numero_sequencial : 0;
        $numero++;

        foreach ($docs as $d) {
            $d->numero_sequencial = $numero;
            $d->save();
            $numero++;
        }
    }



    public function index(Request $request)
    {
        $locais = __getLocaisAtivoUsuario();
        $locais = $locais->pluck(['id']);

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $cliente_id = $request->get('cliente_id');
        $estado = $request->get('estado');
        $tpNF = $request->get('tpNF');
        $local_id = $request->get('local_id');
        $ordenar_por = $request->get('ordenar_por'); // Captura o parâmetro de ordenação

        $this->setNumeroSequencial();
        if ($tpNF == "") {
            $tpNF = 1;
        }

        // Query para buscar as NFes com os filtros aplicados
        $data = Nfe::join('clientes', 'nves.cliente_id', '=', 'clientes.id') // Fazendo join com a tabela viewclientes
            ->where('clientes.empresa_id', request()->empresa_id) // Especifica que o empresa_id é da tabela nves
            ->where('nves.orcamento', 0)
            // Filtro por timestamp completo (data + hora)
            ->when(!empty($start_date), function ($query) use ($start_date) {
                return $query->where('nves.created_at', '>=', $start_date); // Considera data e hora de início
            })
            ->when(!empty($end_date), function ($query) use ($end_date) {
                $end_date = Carbon::parse($end_date)->endOfDay();
                return $query->where('nves.created_at', '<=', $end_date); // Considera data e hora de fim
            })
            ->when(!empty($cliente_id), function ($query) use ($cliente_id) {
                return $query->where('nves.cliente_id', $cliente_id);
            })
            ->when($estado != "", function ($query) use ($estado) {
                return $query->where('nves.estado', $estado);
            })
            ->when($tpNF != "-", function ($query) use ($tpNF) {
                return $query->where('nves.tpNF', $tpNF);
            })
            ->when($local_id, function ($query) use ($local_id) {
                return $query->where('nves.local_id', $local_id);
            })
            ->when(!$local_id, function ($query) use ($locais) {
                return $query->whereIn('nves.local_id', $locais);
            })
            // Adicionando a ordenação com base na escolha do usuário
            ->when($ordenar_por, function ($query) use ($ordenar_por) {
                if ($ordenar_por === 'razao_social_asc') {
                    // Ordenar pela razão social da tabela viewclientes em ordem alfabética (A-Z)
                    return $query->orderBy('clientes.razao_social', 'asc');
                } elseif ($ordenar_por === 'data_cadastro_asc') {
                    // Ordenar pela data de cadastro (mais antigas primeiro)
                    return $query->orderBy('nves.created_at', 'asc');
                } elseif ($ordenar_por === 'data_cadastro_desc') {
                    // Ordenar pela data de cadastro (mais recentes primeiro)
                    return $query->orderBy('nves.created_at', 'desc');
                }
            })
            ->orderBy('created_at', 'desc')
            ->select('nves.*') // Seleciona todas as colunas da tabela nves//
            ->paginate(env("PAGINACAO"));


        return view('nfe.index', compact('data'));
    }



    public function create(Request $request)
    {
        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }
        $clientes = Cliente::where('empresa_id', request()->empresa_id)->get();
        if (sizeof($clientes) == 0) {
            session()->flash("flash_warning", "Primeiro cadastre um cliente!");
            return redirect()->route('clientes.create');
        }
        $sizeProdutos = Produto::where('empresa_id', request()->empresa_id)->count();
        if ($sizeProdutos == 0) {
            session()->flash("flash_warning", "Primeiro cadastre um produto!");
            return redirect()->route('produtos.create');
        }
        $transportadoras = Transportadora::where('empresa_id', request()->empresa_id)->get();
        $cidades = Cidade::all();
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        if (sizeof($naturezas) == 0) {
            session()->flash("flash_warning", "Primeiro cadastre um natureza de operação!");
            return redirect()->route('natureza-operacao.create');
        }
        $centrosCusto = CentroCusto::where('empresa_id', request()->empresa_id)->get();

        $empresa = Empresa::findOrFail(request()->empresa_id);
        $caixa = __isCaixaAberto();
        $empresa = __objetoParaEmissao($empresa, $caixa->local_id);
        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();


        $numeroNfe = Nfe::lastNumero($empresa);

        $isOrcamento = 0;
        if (isset($request->orcamento)) {
            $isOrcamento = 1;
        }
        return view(
            'nfe.create',
            compact('clientes', 'transportadoras', 'centrosCusto', 'cidades', 'naturezas', 'numeroNfe', 'empresa', 'caixa', 'isOrcamento', 'funcionarios')
        );
    }

    public function edit($id, Request $request)
    {
        $item = Nfe::findOrFail($id);
        __validaObjetoEmpresa($item);
        $transportadoras = Transportadora::where('empresa_id', request()->empresa_id)->get();
        $cidades = Cidade::all();
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        $centrosCusto = CentroCusto::where('empresa_id', request()->empresa_id)->get();
        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();


        $caixa = __isCaixaAberto();
        $backTo = $request->input('back_to', 'nfe'); // Padrão para 'nfe'


        return view('nfe.edit', compact('item', 'transportadoras', 'cidades', 'naturezas', 'caixa', 'centrosCusto', 'backTo','funcionarios'));
    }


    public function imprimir($id)
    {
        
        $item = Nfe::findOrFail($id);
        $empresa = $item->empresa;
        if (file_exists(public_path('xml_nfe/') . $item->chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_nfe/') . $item->chave . '.xml');

            $danfe = new Danfe($xml);
            if($empresa->logo){
                $logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(public_path('/uploads/logos/') . 
                    $empresa->logo));
                $danfe->logoParameters($logo, 'L');
            }
            $pdf = $danfe->render();
            header("Content-Disposition: ; filename=DANFE $item->numero.pdf");
            return response($pdf)
            ->header('Content-Type', 'application/pdf');
        } else {
            session()->flash("flash_error", "Arquivo não encontrado");
            return redirect()->back();
        }
    }

    public function downloadXml($id)
    {
        $item = Nfe::findOrFail($id);
        if ($item->estado == 'aprovado') {
            if (file_exists(public_path('xml_nfe/') . $item->chave . '.xml')) {
                return response()->download(public_path('xml_nfe/') . $item->chave . '.xml');
            } else {
                session()->flash("flash_error", "Arquivo não encontrado");
                return redirect()->back();
            }
        } elseif ($item->estado == 'cancelado') {
            if (file_exists(public_path('xml_nfe/') . $item->chave . '.xml')) {
                return response()->download(public_path('xml_nfe_cancelada/') . $item->chave . '.xml');
            } else {
                session()->flash("flash_error", "Arquivo não encontrado");
                return redirect()->back();
            }
        } else {
            session()->flash("flash_error", "Nada encontrado");
            return redirect()->back();
        }
    }

    public function danfeSimples($id)
    {
        $item = Nfe::findOrFail($id);

        if (file_exists(public_path('xml_nfe/') . $item->chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_nfe/') . $item->chave . '.xml');
            try {
                $danfe = new DanfeSimples($xml);
                $danfe->debugMode(false);
                $pdf = $danfe->render();
                return response($pdf)
                    ->header('Content-Type', 'application/pdf');
            } catch (InvalidArgumentException $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            session()->flash("flash_error", "Arquivo não encontrado");
            return redirect()->back();
        }
    }

    public function danfeEtiqueta($id)
    {
        $item = Nfe::findOrFail($id);

        if (file_exists(public_path('xml_nfe/') . $item->chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_nfe/') . $item->chave . '.xml');
            try {
                $danfe = new DanfeEtiqueta($xml);
                $danfe->debugMode(false);
                $pdf = $danfe->render();
                return response($pdf)
                    ->header('Content-Type', 'application/pdf');
            } catch (InvalidArgumentException $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            session()->flash("flash_error", "Arquivo não encontrado");
            return redirect()->back();
        }
    }

    public function danfeEtiquetaCorreio($id)
    {
        $item = Nfe::findOrFail($id);

        if (file_exists(public_path('xml_nfe/') . $item->chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_nfe/') . $item->chave . '.xml');

            try {
                $xml2 = simplexml_load_string($xml);

                // Dados do Emitente
                $emitente_nome = (string) $xml2->NFe->infNFe->emit->xNome;
                $emitente_fantasia = (string) $xml2->NFe->infNFe->emit->xFant;
                $emitente_cnpj = (string) $xml2->NFe->infNFe->emit->CNPJ;

                $endereco_emitente = $xml2->NFe->infNFe->emit->enderEmit;
                $emitente_endereco = (string) $endereco_emitente->xLgr . ", " . (string) $endereco_emitente->nro . " - " . (string) $endereco_emitente->xBairro;
                $emitente_cidade = (string) $endereco_emitente->xMun . " - " . (string) $endereco_emitente->UF;
                $emitente_cep = (string) $endereco_emitente->CEP;

                // Dados do Destinatário
                $destinatario_nome = (string) $xml2->NFe->infNFe->dest->xNome;
                $destinatario_cpf = (string) $xml2->NFe->infNFe->dest->CPF;

                $endereco_destinatario = $xml2->NFe->infNFe->dest->enderDest;
                $destinatario_endereco = (string) $endereco_destinatario->xLgr . ", " . (string) $endereco_destinatario->nro . " - " . (string) $endereco_destinatario->xBairro;
                $destinatario_cidade = (string) $endereco_destinatario->xMun . " - " . (string) $endereco_destinatario->UF;
                $destinatario_cep = (string) $endereco_destinatario->CEP;
                $destinatario_fone = isset($endereco_destinatario->fone) ? (string) $endereco_destinatario->fone : "Não informado";

                // Criando a query string
                $query = http_build_query([
                    'emitente_nome' => $emitente_nome,
                    'emitente_fantasia' => $emitente_fantasia,
                    'emitente_cnpj' => $emitente_cnpj,
                    'emitente_endereco' => $emitente_endereco,
                    'emitente_cidade' => $emitente_cidade,
                    'emitente_cep' => $emitente_cep,

                    'destinatario_nome' => $destinatario_nome,
                    'destinatario_cpf' => $destinatario_cpf,
                    'destinatario_endereco' => $destinatario_endereco,
                    'destinatario_cidade' => $destinatario_cidade,
                    'destinatario_cep' => $destinatario_cep,
                    'destinatario_fone' => $destinatario_fone
                ]);

                // Redireciona para a URL com os parâmetros
                return redirect()->to("https://matriza.net/etiquetas/etiquetas.html?$query");
            } catch (Exception $e) {
                return redirect()->back()->with("flash_error", "Erro ao processar XML: " . $e->getMessage());
            }
        } else {
            return redirect()->back()->with("flash_error", "Arquivo não encontrado");
        }
    }




    public function imprimirCancela($id)
    {
        $item = Nfe::findOrFail($id);

        $xml = file_get_contents(public_path('xml_nfe_cancelada/') . $item->chave . '.xml');
        $dadosEmitente = $this->getEmitente($item->empresa);

        $daevento = new Daevento($xml, $dadosEmitente);
        $daevento->debugMode(true);
        $pdf = $daevento->render();
        return response($pdf)
            ->header('Content-Type', 'application/pdf');
    }

    public function imprimirCorrecao($id)
    {
        $item = Nfe::findOrFail($id);
        $xml = file_get_contents(public_path('xml_nfe_correcao/') . $item->chave . '.xml');
        $dadosEmitente = $this->getEmitente($item->empresa);
        $daevento = new Daevento($xml, $dadosEmitente);
        $daevento->debugMode(true);
        $pdf = $daevento->render();
        return response($pdf)
            ->header('Content-Type', 'application/pdf');
    }

    private function getEmitente($empresa)
    {
        return [
            'razao' => $empresa->nome,
            'logradouro' => $empresa->rua,
            'numero' => $empresa->numero,
            'complemento' => '',
            'bairro' => $empresa->bairro,
            'CEP' => preg_replace('/[^0-9]/', '', $empresa->cep),
            'municipio' => $empresa->cidade->nome,
            'UF' => $empresa->cidade->uf,
            'telefone' => $empresa->telefone,
            'email' => ''
        ];
    }

    public function store(Request $request)
    {

        try {
            $nfe = DB::transaction(function () use ($request) {
                $cliente_id = isset($request->cliente_id) ? $request->cliente_id : null;
                $fornecedor_id = isset($request->fornecedor_id) ? $request->fornecedor_id : null;
                $empresa = Empresa::findOrFail($request->empresa_id);

                if (isset($request->cliente_id)) {
                    if ($request->cliente_id == null) {
                        $cliente_id = $this->cadastrarCliente($request);
                    } else {
                        $this->atualizaCliente($request);
                    }
                }
                if (isset($request->fornecedor_id)) {
                    if ($request->fornecedor_id == null) {
                        $fornecedor_id = $this->cadastrarFornecedor($request);
                    } else {
                        $this->atualizaFornecedor($request);
                    }
                }
                $transportadora_id = $request->transportadora_id;
                if ($request->transportadora_id == null) {
                    $transportadora_id = $this->cadastrarTransportadora($request);
                } else {
                    $this->atualizaTransportadora($request);
                }
                $config = Empresa::find($request->empresa_id);

                $tipoPagamento = $request->tipo_pagamento;

                $caixa = __isCaixaAberto();
				
				//dd($request->valor_produtos);
                $valor_produto =  number_format($request->valor_produtos, 2);

                if ($caixa != null) {
                    $empresa = __objetoParaEmissao($empresa, $caixa->local_id);
                }
                $request->merge([
                    'emissor_nome' => $config->nome,
                    'emissor_cpf_cnpj' => $config->cpf_cnpj,
                    'ambiente' => $config->ambiente,
                    'chave' => '',
                    'cliente_id' => $cliente_id,
                    'fornecedor_id' => $fornecedor_id,
                    'transportadora_id' => $transportadora_id,
                    'numero_serie' => $empresa->numero_serie_nfe ? $empresa->numero_serie_nfe : 0,
                    'numero' => $request->numero_nfe ? $request->numero_nfe : 0,
                    'estado' => 'novo',
                    'total' => __convert_value_bd($request->valor_total),
                    'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                    'acrescimo' => $request->acrescimo ? __convert_value_bd($request->acrescimo) : 0,
                    'valor_produtos' => $request->valor_produtos,
                    'valor_frete' => $request->valor_frete ? __convert_value_bd($request->valor_frete) : 0,
                    'caixa_id' => $caixa ? $caixa->id : null,
                    'local_id' => $caixa->local_id,
                    // 'numero' => $request->numero ?? 0,
                    'tipo_pagamento' => $request->tipo_pagamento[0],
                    'user_id' => \Auth::user()->id
                    // 'bandeira_cartao' => $request->bandeira_cartao ?? null,
                    // 'cnpj_cartao' => $request->cnpj_cartao ?? null,
                    // 'cAut_cartao' => $request->cAut_cartao ?? null
                ]);

                if ($request->orcamento) {
                    $request->merge([
                        'gerar_conta_receber' => 0,
                    ]);
                }

                $nfe = Nfe::create($request->all());

                for ($i = 0; $i < sizeof($request->produto_id); $i++) {

                    $product = Produto::findOrFail($request->produto_id[$i]);
                    $variacao_id = isset($request->variacao_id[$i]) ? $request->variacao_id[$i] : null;
                    ItemNfe::create([
                        'nfe_id' => $nfe->id,
                        'produto_id' => (int)$request->produto_id[$i],
                        'quantidade' => __convert_value_bd($request->quantidade[$i]),
                        'valor_unitario' => __convert_value_bd($request->valor_unitario[$i]),
                        'sub_total' => __convert_value_bd($request->sub_total[$i]),
                        'perc_icms' => __convert_value_bd($request->perc_icms[$i]),
                        'perc_pis' => __convert_value_bd($request->perc_pis[$i]),
                        'perc_cofins' => __convert_value_bd($request->perc_cofins[$i]),
                        'perc_ipi' => __convert_value_bd($request->perc_ipi[$i]),
                        'cst_csosn' => $request->cst_csosn[$i],
                        'cst_pis' => $request->cst_pis[$i],
                        'cst_cofins' => $request->cst_cofins[$i],
                        'cst_ipi' => $request->cst_ipi[$i],
                        'perc_red_bc' => $request->perc_red_bc[$i] ? __convert_value_bd($request->perc_red_bc[$i]) : 0,
                        'cfop' => $request->cfop[$i],
                        'ncm' => $request->ncm[$i],
                        'codigo_beneficio_fiscal' => $request->codigo_beneficio_fiscal[$i],
                        'variacao_id' => $variacao_id,
                        'cEnq' => $product->cEnq
                    ]);
                    if (isset($request->is_compra)) {

                        $product->valor_compra = __convert_value_bd($request->valor_unitario[$i]);
                        $product->save();

                        ProdutoFornecedor::updateOrCreate([
                            'produto_id' => $product->id,
                            'fornecedor_id' => $fornecedor_id
                        ]);
                    }

                    if ($product->gerenciar_estoque && $request->orcamento == 0) {
                        if (isset($request->is_compra)) {

                            $this->util->incrementaEstoque(
                                $product->id,
                                __convert_value_bd($request->quantidade[$i]),
                                $variacao_id,
                                $caixa->local_id
                            );
                        } else {
                            $this->util->reduzEstoque(
                                $product->id,
                                __convert_value_bd($request->quantidade[$i]),
                                $variacao_id,
                                $caixa->local_id
                            );
                        }
                    }

                    if ($request->is_compra) {

                        $tipo = 'incremento';
                        $codigo_transacao = $nfe->id;
                        $tipo_transacao = 'compra';
                        $this->util->movimentacaoProduto($product->id, __convert_value_bd($request->quantidade[$i]), $tipo, $codigo_transacao, $tipo_transacao, \Auth::user()->id, $variacao_id);
                    } else {
                        $tipo = 'reducao';
                        $codigo_transacao = $nfe->id;
                        $tipo_transacao = 'venda_nfe';
                        $this->util->movimentacaoProduto($product->id, __convert_value_bd($request->quantidade[$i]), $tipo, $codigo_transacao, $tipo_transacao, \Auth::user()->id, $variacao_id);
                    }
                }


                if ($request->tipo_pagamento) {
		
					// Verifica se todos os pagamentos são do tipo "sem pagamento" (90)
					$todosSemPagamento = collect($tipoPagamento)->every(function ($tipo) {
						return (int)$tipo === 90;
					});
					
					if (!$todosSemPagamento) {
						for ($i = 0; $i < sizeof($tipoPagamento); $i++) {
							if ((int)$tipoPagamento[$i] !== 90) {
								FaturaNfe::create([
									'nfe_id' => $nfe->id,
									'tipo_pagamento' => $tipoPagamento[$i],
									'data_vencimento' => $request->data_vencimento[$i],
									'valor' => __convert_value_bd($request->valor_fatura[$i])
								]);
							}
						}

                        if ($request->tpNF == 1) {
                            if ($request->gerar_conta_receber) {
                                for ($i = 0; $i < sizeof($tipoPagamento); $i++) {
                                    ContaReceber::create([
                                        'empresa_id' => $request->empresa_id,
                                        'nfe_id' => $nfe->id,
                                        'cliente_id' => $cliente_id,
                                        'valor_integral' => __convert_value_bd($request->valor_fatura[$i]),
                                        'tipo_pagamento' => $tipoPagamento[$i],
                                        'data_vencimento' => $request->data_vencimento[$i],
                                        'local_id' => $caixa->local_id,
                                    ]);
                                }
                            }
                        } else {
                            for ($i = 0; $i < sizeof($tipoPagamento); $i++) {
                                if ($request->gerar_conta_pagar) {
                                    ContaPagar::create([
                                        'empresa_id' => $request->empresa_id,
                                        'nfe_id' => $nfe->id,
                                        'fornecedor_id' => $fornecedor_id,
                                        'valor_integral' => __convert_value_bd($request->valor_fatura[$i]),
                                        'tipo_pagamento' => $tipoPagamento[$i],
                                        'data_vencimento' => $request->data_vencimento[$i],
                                        'local_id' => $caixa->local_id,
                                    ]);
                                }
                            }
                        }
                    }
                }
                // dd($request->all());
                if ($request->ordem_servico_id) {
                    $ordem = OrdemServico::findOrFail($request->ordem_servico_id);
                    $ordem->nfe_id = $nfe->id;
                    $ordem->save();
                }

                if ($request->pedido_ecommerce_id) {
                    $pedido = PedidoEcommerce::findOrFail($request->pedido_ecommerce_id);
                    $pedido->nfe_id = $nfe->id;
                    $pedido->estado = 'finalizado';
                    $pedido->save();
                }
                if ($request->pedido_mercado_livre_id) {
                    $pedido = PedidoMercadoLivre::findOrFail($request->pedido_mercado_livre_id);
                    $pedido->nfe_id = $nfe->id;
                    $pedido->save();
                }
                if ($request->pedido_nuvem_shop_id) {
                    $pedido = NuvemShopPedido::findOrFail($request->pedido_nuvem_shop_id);
                    $pedido->nfe_id = $nfe->id;
                    $pedido->save();
                }
                if ($request->cotacao_id) {
                    $cotacao = Cotacao::findOrFail($request->cotacao_id);
                    $cotacao->nfe_id = $nfe->id;
                    $cotacao->escolhida = 1;
                    $cotacao->estado = 'aprovada';
                    $cotacao->save();
                }
                if ($request->reserva_id) {
                    $reserva = Reserva::findOrFail($request->reserva_id);
                    $reserva->nfe_id = $nfe->id;

                    $reserva->save();
                }

                if ($request->pedido_woocommerce_id) {
                    $pedido = WoocommercePedido::findOrFail($request->pedido_woocommerce_id);
                    $pedido->nfe_id = $nfe->id;
                    $pedido->save();
                }

                return $nfe;
            });
            session()->flash("flash_success", "NFe cadastrada!");
        } catch (\Exception $e) {
            // echo $e->getMessage() . '<br>' . $e->getLine();
            // die;
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
            return redirect()->back();
        }

        if ($request->orcamento == 1) {
            session()->flash("flash_success", "Orçamento cadastrada!");
            return redirect()->route('orcamentos.index');
        }
        if (isset($request->is_compra)) {
            session()->flash("flash_success", "Compra cadastrada!");
            if ($nfe) {
                if ($nfe->isItemValidade()) {
                    return redirect()->route('compras.info-validade', $nfe->id);
                }
            }
            return redirect()->route('compras.index');
        } else {
            return redirect()->route('nfe.index');
        }
    }

    private function cadastrarCliente($request)
    {
        $cliente = Cliente::create([
            'empresa_id' => $request->empresa_id,
            'razao_social' => $request->cliente_nome,
            'nome_fantasia' => $request->nome_fantasia ?? '',
            'cpf_cnpj' => $request->cliente_cpf_cnpj,
            'ie' => $request->ie,
            'contribuinte' => $request->contribuinte,
            'consumidor_final' => $request->consumidor_final,
            'email' => $request->email ?? '',
            'telefone' => $request->telefone ?? '',
            'cidade_id' => $request->cliente_cidade,
            'rua' => $request->cliente_rua,
            'cep' => $request->cep,
            'numero' => $request->cliente_numero,
            'bairro' => $request->cliente_bairro,
            'complemento' => $request->complemento
        ]);
        return $cliente->id;
    }

    private function atualizaCliente($request)
    {
        $cliente = Cliente::findOrFail($request->cliente_id);
        $cliente->update([
            'razao_social' => $request->cliente_nome,
            'nome_fantasia' => $request->nome_fantasia ?? '',
            'cpf_cnpj' => $request->cliente_cpf_cnpj,
            'ie' => $request->ie,
            'contribuinte' => $request->contribuinte,
            'consumidor_final' => $request->consumidor_final,
            'email' => $request->email ?? '',
            'telefone' => $request->telefone ?? '',
            'cidade_id' => $request->cliente_cidade,
            'rua' => $request->cliente_rua,
            'cep' => $request->cep,
            'numero' => $request->cliente_numero,
            'bairro' => $request->cliente_bairro,
            'complemento' => $request->complemento
        ]);
        return $cliente->id;
    }

    private function cadastrarFornecedor($request)
    {
        $fornecedor = Fornecedor::create([
            'empresa_id' => $request->empresa_id,
            'razao_social' => $request->fornecedor_nome,
            'nome_fantasia' => $request->nome_fantasia ?? '',
            'cpf_cnpj' => $request->fornecedor_cpf_cnpj,
            'ie' => $request->ie,
            'contribuinte' => $request->contribuinte,
            'consumidor_final' => $request->consumidor_final,
            'email' => $request->email ?? '',
            'telefone' => $request->telefone ?? '',
            'cidade_id' => $request->fornecedor_cidade,
            'rua' => $request->fornecedor_rua,
            'cep' => $request->cep,
            'numero' => $request->fornecedor_numero,
            'bairro' => $request->fornecedor_bairro,
            'complemento' => $request->complemento
        ]);
        return $fornecedor->id;
    }

    private function atualizaFornecedor($request)
    {
        $fornecedor = Fornecedor::findOrFail($request->fornecedor_id);
        $fornecedor->update([
            'razao_social' => $request->fornecedor_nome,
            'nome_fantasia' => $request->nome_fantasia ?? '',
            'cpf_cnpj' => $request->fornecedor_cpf_cnpj,
            'ie' => $request->ie,
            'contribuinte' => $request->contribuinte,
            'consumidor_final' => $request->consumidor_final,
            'email' => $request->email ?? '',
            'telefone' => $request->telefone ?? '',
            'cidade_id' => $request->fornecedor_cidade,
            'rua' => $request->fornecedor_rua,
            'cep' => $request->cep,
            'numero' => $request->fornecedor_numero,
            'bairro' => $request->fornecedor_bairro,
            'complemento' => $request->complemento
        ]);
        return $fornecedor->id;
    }

    private function cadastrarTransportadora($request)
    {
        if ($request->razao_social_transp) {
            $transportadora = Transportadora::create([
                'empresa_id' => $request->empresa_id,
                'razao_social' => $request->razao_social_transp,
                'nome_fantasia' => $request->nome_fantasia_transp ?? '',
                'cpf_cnpj' => $request->cpf_cnpj_transp,
                'ie' => $request->ie_transp,
                'antt' => $request->antt,
                'email' => $request->email_transp,
                'cidade_id' => $request->cidade_transp,
                'telefone' => $request->telefone_transp,
                'rua' => $request->rua_transp,
                'cep' => $request->cep_transp,
                'numero' => $request->numero_transp,
                'bairro' => $request->bairro_transp,
                'complemento' => $request->complemento_transp
            ]);
            return $transportadora->id;
        }
        return null;
    }

    private function atualizaTransportadora($request)
    {
        if ($request->razao_social_transp) {
            $transportadora = Transportadora::findOrFail($request->transportadora_id);
            $transportadora->update([
                'empresa_id' => $request->empresa_id,
                'razao_social' => $request->razao_social_transp,
                'nome_fantasia' => $request->nome_fantasia_transp ?? '',
                'cpf_cnpj' => $request->cpf_cnpj_transp,
                'ie' => $request->ie_transp,
                'antt' => $request->antt,
                'email' => $request->email,
                'telefone' => $request->telefone,
                'cidade_id' => $request->cidade_transp,
                'rua' => $request->rua_transp,
                'cep' => $request->cep_transp,
                'numero' => $request->numero_transp,
                'bairro' => $request->bairro_transp,
                'complemento' => $request->complemento_transp
            ]);
            return $transportadora->id;
        }
        return null;
    }

    public function update(Request $request, $id)
    {
        // dd($request);
        try {
            DB::transaction(function () use ($request, $id) {
                $item = Nfe::findOrFail($id);
                $transportadora_id = $request->transportadora_id;
                if ($request->transportadora_id == null) {
                    $transportadora_id = $this->cadastrarTransportadora($request);
                }
                $config = Empresa::find($request->empresa_id);
                $tipoPagamento = $request->tipo_pagamento;

                $request->merge([
                    'emissor_nome' => $config->nome,
                    'emissor_cpf_cnpj' => $config->cpf_cnpj,
                    'ambiente' => $config->ambiente,
                    'chave' => '',
                    'transportadora_id' => $transportadora_id,
                    'numero' => $request->numero_nfe ? $request->numero_nfe : 0,
                    'total' => __convert_value_bd($request->valor_total),
                    'desconto' => __convert_value_bd($request->desconto),
                    'acrescimo' => __convert_value_bd($request->acrescimo),
                    'valor_produtos' => __convert_value_bd($request->valor_total) ?? 0,
                    'valor_frete' => $request->valor_frete ? __convert_value_bd($request->valor_frete) : 0,
                    'tipo_pagamento' => $request->tipo_pagamento[0],
                ]);

                $item->fill($request->all())->save();

                foreach ($item->itens as $x) {
                    $product = $x->produto;
                    if ($product->gerenciar_estoque && $item->orcamento == 0) {
                        if (isset($request->is_compra)) {
                            $this->util->reduzEstoque($product->id, $x->quantidade, $x->variacao_id, $item->local_id);
                        } else {
                            $this->util->incrementaEstoque($product->id, $x->quantidade, $x->variacao_id, $item->local_id);
                        }
                    }
                }

                $item->itens()->delete();
                $item->fatura()->delete();

                for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                    $product = Produto::findOrFail($request->produto_id[$i]);
                    $variacao_id = isset($request->variacao_id[$i]) ? $request->variacao_id[$i] : null;

                    ItemNfe::create([
                        'nfe_id' => $item->id,
                        'produto_id' => (int)$request->produto_id[$i],
                        'quantidade' => __convert_value_bd($request->quantidade[$i]),
                        'valor_unitario' => __convert_value_bd($request->valor_unitario[$i]),
                        'sub_total' => __convert_value_bd($request->sub_total[$i]),
                        'perc_icms' => __convert_value_bd($request->perc_icms[$i]),
                        'perc_pis' => __convert_value_bd($request->perc_pis[$i]),
                        'perc_cofins' => __convert_value_bd($request->perc_cofins[$i]),
                        'perc_ipi' => __convert_value_bd($request->perc_ipi[$i]),
                        'cst_csosn' => $request->cst_csosn[$i],
                        'cst_pis' => $request->cst_pis[$i],
                        'cst_cofins' => $request->cst_cofins[$i],
                        'cst_ipi' => $request->cst_ipi[$i],
                        'perc_red_bc' => $request->perc_red_bc[$i] ? __convert_value_bd($request->perc_red_bc[$i]) : 0,
                        'cfop' => $request->cfop[$i],
                        'ncm' => $request->ncm[$i],
                        'codigo_beneficio_fiscal' => $request->codigo_beneficio_fiscal[$i],
                        'variacao_id' => $variacao_id
                    ]);

                    if ($product->gerenciar_estoque && $item->orcamento == 0) {
                        if (isset($request->is_compra)) {
                            $this->util->incrementaEstoque($product->id, __convert_value_bd($request->quantidade[$i]), $variacao_id, $item->local_id);
                        } else {
                            $this->util->reduzEstoque($product->id, __convert_value_bd($request->quantidade[$i]), $variacao_id, $item->local_id);
                        }
                    }
                }

                ContaReceber::where('nfe_id', $item->id)->delete();
                ContaPagar::where('nfe_id', $item->id)->delete();
                FaturaNfe::where('nfe_id', $item->id)->delete();

                if ($request->tpNF == 1) {

                    if ($request->gerar_conta_receber) {
                        for ($i = 0; $i < sizeof($tipoPagamento); $i++) {
                            ContaReceber::create([
                                'empresa_id' => $request->empresa_id,
                                'nfe_id' => $item->id,
                                'cliente_id' => $request->cliente_id,
                                'valor_integral' => __convert_value_bd($request->valor_fatura[$i]),
                                'tipo_pagamento' => $request->tipo_pagamento[$i],
                                'data_vencimento' => $request->data_vencimento[$i],
                                'local_id' => $item->local_id
                            ]);
                        }
                    }
                } else {
                    if ($request->gerar_conta_pagar) {
                        ContaPagar::create([
                            'empresa_id' => $request->empresa_id,
                            'nfe_id' => $item->id,
                            'fornecedor_id' => $request->fornecedor_id,
                            'valor_integral' => __convert_value_bd($request->valor_fatura[$i]),
                            'tipo_pagamento' => $request->tipo_pagamento[$i],
                            'data_vencimento' => $request->data_vencimento[$i],
                            'local_id' => $item->local_id
                        ]);
                    }
                }
				
				if ($request->tipo_pagamento) {
					$todosSemPagamento = collect($tipoPagamento)->every(function ($tipo) {
						return (int)$tipo === 90;
					});

					if (!$todosSemPagamento) {
						for ($i = 0; $i < sizeof($tipoPagamento); $i++) {
							if ((int)$tipoPagamento[$i] !== 90) {
								FaturaNfe::create([
									'nfe_id' => $item->id,
									'tipo_pagamento' => $tipoPagamento[$i],
									'data_vencimento' => $request->data_vencimento[$i],
									'valor' => __convert_value_bd($request->valor_fatura[$i])
								]);
							}
						}
					}
				}

            });
            session()->flash("flash_success", "NFe alterada com sucesso!");
        } catch (\Exception $e) {
            echo $e->getMessage() . '<br>' . $e->getLine();
            die;
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
        }

        $item = Nfe::findOrFail($id);

        if ($item->orcamento == 1) {
            session()->flash("flash_success", "Orçamento atualizado!");
            return redirect()->route('orcamentos.index');
        }
        if (isset($request->is_compra)) {
            session()->flash("flash_success", "Compra atualizada!");
            return redirect()->route('compras.index');
        } else {
            return redirect()->route('nfe.index');
        }
    }




    public function xmlTemp($id)
    {
        $item = Nfe::findOrFail($id);

        $empresa = $item->empresa;
        $empresa = __objetoParaEmissao($empresa, $item->local_id);

        if ($empresa->arquivo == null) {
            session()->flash("flash_error", "Certificado não encontrado para este emitente");
            return redirect()->route('config.index');
        }

        $nfe_service = new NFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$empresa->ambiente,
            "razaosocial" => $empresa->nome,
            "siglaUF" => $empresa->cidade->uf,
            "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
        ], $empresa);

        $doc = $nfe_service->gerarXml($item);

        if (!isset($doc['erros_xml'])) {
            $xml = $doc['xml'];

            return response($xml)
                ->header('Content-Type', 'application/xml');
        } else {
            return response()->json($doc['erros_xml'], 401);
        }
    }

    public function danfeTemporaria($id)
    {
        $item = Nfe::findOrFail($id);

        $empresa = $item->empresa;

        if ($empresa->arquivo == null) {
            session()->flash("flash_error", "Certificado não encontrado para este emitente");
            return redirect()->route('config.index');
        }

        $nfe_service = new NFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$empresa->ambiente,
            "razaosocial" => $empresa->nome,
            "siglaUF" => $empresa->cidade->uf,
            "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
        ], $empresa);

        $doc = $nfe_service->gerarXml($item);

        if (!isset($doc['erros_xml'])) {
            $xml = $doc['xml'];

            $danfe = new Danfe($xml);
            if ($empresa->logo) {
                $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents(public_path('/uploads/logos/') . $empresa->logo));
                $danfe->logoParameters($logo, 'L');
            }
            $pdf = $danfe->render();
            return response($pdf)
                ->header('Content-Type', 'application/pdf');
        } else {
            return response()->json($doc['erros_xml'], 401);
        }
    }

    public function inutilizar(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $data = Inutilizacao::where('empresa_id', request()->empresa_id)
            ->where('modelo', '55')->orderBy('id', 'desc')
            ->when(!empty($start_date), function ($query) use ($start_date) {
                return $query->whereDate('created_at', '>=', $start_date);
            })
            ->when(!empty($end_date), function ($query) use ($end_date,) {
                return $query->whereDate('created_at', '<=', $end_date);
            })
            ->get();
        $modelo = '55';
        return view('inutilizacao.index', compact('data', 'modelo'));
    }

    public function inutilStore(Request $request)
    {
        $request->merge([
            'estado' => 'novo',
            'modelo' => '55'
        ]);
        try {

            Inutilizacao::create($request->all());
            session()->flash("flash_success", "Inutilização criada!");
        } catch (\Exception $e) {
            echo $e->getMessage() . '<br>' . $e->getLine();
            die;
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
        }
        return redirect()->back();
    }

    public function inutilDestroy($id)
    {
        $item = Inutilizacao::findOrFail($id);
        try {
            $item->delete();
            session()->flash("flash_success", "Inutilização removida!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
        }
        return redirect()->back();
    }

    public function alterarEstado($id)
    {
        $item = Nfe::findOrFail($id);
        $tipo = request()->tipo;

        return view('nfe.estado_fiscal', compact('item', 'tipo'));
    }

    public function storeEstado(Request $request, $id)
    {
        $item = Nfe::findOrFail($id);
        try {
            $item->estado = $request->estado_emissao;
            if ($request->hasFile('file')) {
                $xml = simplexml_load_file($request->file);
                $chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
                $file = $request->file;
                $file->move(public_path('xml_nfe/'), $chave . '.xml');
                $item->chave = $chave;
                $item->data_emissao = date('Y-m-d H:i:s');
                $item->numero = (int)$xml->NFe->infNFe->ide->nNF;
            }
            $item->save();
            session()->flash("flash_success", "Estado alterado");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        if ($request->tipo == 'devolucao') {
            return redirect()->route('devolucao.index');
        }
        return redirect()->route('nfe.index');
    }

    public function imprimirVenda($id)
    {
		
        $item = Nfe::findOrFail($id);

    $empresa = Empresa::findOrFail($item->empresa_id);
    $config = __objetoParaEmissao($empresa, $item->local_id);
		
        $p = view('nfe.imprimir', compact('config', 'item'));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper("A4");
        $domPdf->render();
        $domPdf->stream("Venda de Produtos $id.pdf", array("Attachment" => false));
    }

    public function show($id)
    {
        $data = Nfe::findOrFail($id);
        return view('nfe.show', compact('data'));
    }

    public function importZip()
    {

        $zip_loaded = extension_loaded('zip') ? true : false;
        if ($zip_loaded === false) {
            session()->flash('flash_error', "Por favor instale/habilite o PHP zip para importar");
            return redirect()->back();
        }
        return view('nfe.import_zip');
    }

    public function importZipStore(Request $request)
    {
        if ($request->hasFile('file')) {

            if (!is_dir(public_path('extract'))) {
                mkdir(public_path('extract'), 0777, true);
            }

            $zip = new \ZipArchive();
            $zip->open($request->file);
            $destino = public_path('extract');

            $this->clearFolder($destino);

            if ($zip->extractTo($destino) == TRUE) {

                $data = $this->preparaXmls($destino);

                if (sizeof($data) == 0) {
                    session()->flash('flash_error', "Algo errado com o arquivo!");
                    return redirect()->back();
                }

                return view('nfe.import_zip_view', compact('data'));
            } else {
                session()->flash('flash_error', "Erro ao desconpactar arquivo");
                return redirect()->back();
            }
            $zip->close();
        } else {
            session()->flash('flash_error', 'Nenhum arquivo selecionado!');
            return redirect()->back();
        }
    }

    private function clearFolder($destino)
    {
        $files = glob($destino . "/*");
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        }
    }

    private function preparaXmls($destino)
    {
        $files = glob($destino . "/*");
        $data = [];
        foreach ($files as $file) {
            if (is_file($file)) {
                try {
                    $xml = simplexml_load_file($file);

                    $cliente = $this->getCliente($xml);

                    $produtos = $this->getProdutos($xml);
                    $fatura = $this->getFatura($xml);

                    if ($produtos != null) {
                        $item = [
                            'data' => (string)$xml->NFe->infNFe->ide->dhEmi,
                            'serie' => (string)$xml->NFe->infNFe->ide->serie,
                            'chave' => substr($xml->NFe->infNFe->attributes()->Id, 3, 44),
                            'valor_total' => (float)$xml->NFe->infNFe->total->ICMSTot->vProd,
                            'numero_nfe' => (int)$xml->NFe->infNFe->ide->nNF,
                            'desconto' => (float)$xml->NFe->infNFe->total->ICMSTot->vDesc,
                            'cliente' => $cliente,
                            'produtos' => $produtos,
                            'fatura' => $fatura,
                            'file' => $file,
                            'natureza' => (string)$xml->NFe->infNFe->ide->natOp[0],
                            'observacao' => (string)$xml->NFe->infNFe->infAdic ? $xml->NFe->infNFe->infAdic->infCpl[0] : '',
                            'tipo_pagamento' => (string)$xml->NFe->infNFe->pag->detPag->tPag,
                            'finNFe' => (string)$xml->NFe->infNFe->ide->finNFe,
                            'data_emissao'
                        ];
                        array_push($data, $item);
                        // dd($item);
                    }
                } catch (\Exception $e) {
                }
            }
        }

        return $data;
    }

    private function getCliente($xml)
    {

        if (!isset($xml->NFe->infNFe->dest->enderDest->cMun)) {
            return null;
        }
        $cidade = Cidade::where('codigo', $xml->NFe->infNFe->dest->enderDest->cMun)->first();

        $dadosCliente = [
            'cpf_cnpj' => isset($xml->NFe->infNFe->dest->CNPJ) ? (string)$xml->NFe->infNFe->dest->CNPJ : (string)$xml->NFe->infNFe->dest->CPF,
            'razao_social' => (string)$xml->NFe->infNFe->dest->xNome,
            'nome_fantasia' => (string)$xml->NFe->infNFe->dest->xFant,
            'rua' => (string)$xml->NFe->infNFe->dest->enderDest->xLgr,
            'numero' => (string)$xml->NFe->infNFe->dest->enderDest->nro,
            'bairro' => (string)$xml->NFe->infNFe->dest->enderDest->xBairro,
            'cep' => (string)$xml->NFe->infNFe->dest->enderDest->CEP,
            'telefone' => (string)$xml->NFe->infNFe->dest->enderDest->fone,
            'ie_rg' => (string)$xml->NFe->infNFe->dest->IE,
            'cidade_id' => $cidade->id,
            'cidade_info' => "$cidade->nome ($cidade->uf)",
            'consumidor_final' => (string)$xml->NFe->infNFe->dest->IE ? 1 : 0,
            'status' => 1,
            'contribuinte' => 1,
            'empresa_id' => request()->empresa_id
        ];
        return $dadosCliente;
    }

    private function getProdutos($xml)
    {
        $itens = [];
        try {

            foreach ($xml->NFe->infNFe->det as $item) {

                $produto = Produto::verificaCadastrado(
                    $item->prod->cEAN,
                    $item->prod->xProd,
                    $item->prod->cProd,
                    request()->empresa_id
                );

                $trib = Produto::getTrib($item->imposto);
                // dd($trib);
                $cfops = $this->getCfpos((string)$item->prod->CFOP);
                $item = [
                    'codigo' => (string)$item->prod->cProd,
                    'nome' => (string)$item->prod->xProd,
                    'ncm' => (string)$item->prod->NCM,
                    'cfop' => (string)$item->prod->CFOP,
                    'cfop_estadual' => $cfops['cfop_estadual'],
                    'cfop_outro_estado' => $cfops['cfop_outro_estado'],
                    'cfop_entrada_estadual' => $cfops['cfop_entrada_estadual'],
                    'cfop_entrada_outro_estado' => $cfops['cfop_entrada_outro_estado'],
                    'unidade' => (string)$item->prod->uCom,
                    'valor_unitario' => (float)$item->prod->vUnCom,
                    'sub_total' => (float)$item->prod->vProd,
                    'quantidade' => (float)$item->prod->qCom,
                    'codigo_barras' => (string)$item->prod->cEAN == 'SEM GTIN' ? '' : (string)$item->prod->cEAN,
                    'produto_id' => $produto ? $produto->id : 0,
                    'cest' => (string)$item->prod->CEST ? (string)$item->prod->CEST : '',
                    'empresa_id' => request()->empresa_id,

                    'cst_csosn' => $trib['cst_csosn'],
                    'cst_ipi' => $trib['cst_ipi'],
                    'cst_pis' => $trib['cst_pis'],
                    'cst_cofins' => $trib['cst_cofins'],

                    'perc_icms' => $trib['pICMS'],
                    'perc_pis' => $trib['pPIS'],
                    'perc_cofins' => $trib['pCOFINS'],
                    'perc_ipi' => $trib['pIPI'],
                    'perc_red_bc' => $trib['pRedBC'],
                    'origem' => $trib['orig'],
                    'cEnq' => '999'

                ];
                array_push($itens, $item);
            }
            return $itens;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getFatura($xml)
    {
        $fatura = [];

        try {
            if (!empty($xml->NFe->infNFe->cobr->dup)) {
                foreach ($xml->NFe->infNFe->cobr->dup as $dup) {
                    $titulo = $dup->nDup;
                    $vencimento = $dup->dVenc;

                    $vlr_parcela = number_format((float) $dup->vDup, 2, ",", ".");

                    $parcela = [
                        'numero' => (int)$titulo,
                        'vencimento' => (string)$dup->dVenc,
                        'valor_parcela' => $vlr_parcela,
                        'rand' => rand(0, 10000)
                    ];
                    array_push($fatura, $parcela);
                }
            } else {

                $vencimento = explode('-', substr($xml->NFe->infNFe->ide->dhEmi[0], 0, 10));

                $parcela = [
                    'numero' => 1,
                    'vencimento' => substr($xml->NFe->infNFe->ide->dhEmi[0], 0, 10),
                    'valor_parcela' => (float)$xml->NFe->infNFe->pag->detPag->vPag[0],
                    'rand' => rand(0, 10000)
                ];
                array_push($fatura, $parcela);
            }
        } catch (\Exception $e) {
        }

        return $fatura;
    }

    private function getCfpos($cfop)
    {

        $n = substr($cfop, 1, 4);
        return [
            'cfop_estadual' => '5' . $n,
            'cfop_outro_estado' => '6' . $n,
            'cfop_entrada_estadual' => '1' . $n,
            'cfop_entrada_outro_estado' => '2' . $n,
        ];
    }

    public function importZipStoreFiles(Request $request)
    {
        try {

            $cont = DB::transaction(function () use ($request) {
                $selecionados = [];
                for ($i = 0; $i < sizeof($request->file_id); $i++) {
                    $selecionados[] = $request->file_id[$i];
                }
                $cont = 0;
                for ($i = 0; $i < sizeof($request->data); $i++) {
                    $data = json_decode($request->data[$i]);
                    if (in_array($data->chave, $selecionados)) {

                        $cliente = $this->insereCliente($data->cliente);
                        $produtos = $this->insereProdutos($data->produtos, $request->local_id);

                        $nfe = $this->salvarVenda($data, $cliente, $request->local_id);
                        if ($nfe != 0) {
                            File::copy($data->file, public_path("xml_nfe/") . $data->chave . ".xml");
                            $cont++;
                        }
                    }
                }
                return $cont;
            });
            session()->flash("flash_success", 'Total de vendas salvas: ' . $cont);
            return redirect()->route('nfe.index');
        } catch (\Exception $e) {
            // echo $e->getLine();
            // die;
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
            return redirect()->route('nfe.index');
        }
    }

    private function salvarVenda($venda, $cliente, $local_id)
    {
        $natureza = $this->insereNatureza($venda->natureza);
        $empresa = Empresa::findOrFail($cliente->empresa_id);
        $empresa = __objetoParaEmissao($empresa, $local_id);

        $dataVenda = [
            'cliente_id' => $cliente->id,
            'estado' => 'aprovado',
            'empresa_id' => $cliente->empresa_id,
            'numero' => $venda->numero_nfe,
            'natureza_id' => $natureza->id,
            'chave' => $venda->chave,
            'emissor_nome' => $empresa->nome,
            'emissor_cpf_cnpj' => $empresa->cpf_cnpj,
            'numero_serie' => $venda->serie,
            'total' => $venda->valor_total,
            'desconto' => $venda->desconto,
            'tipo_pagamento' => $venda->tipo_pagamento,
            'observacao' => $venda->observacao,
            'tpNF' => 1,
            'finNFe' => $venda->finNFe,
            'local_id' => $local_id,
        ];

        $nfe = Nfe::where('empresa_id', $cliente->empresa_id)
            ->where('chave', $venda->chave)->first();
        if ($nfe == null) {
            $nfe = Nfe::create($dataVenda);

            $nfe->data_emissao = $venda->data;
            $nfe->created_at = $venda->data;
            $nfe->save();
        } else {

            $nfe->data_emissao = $venda->data;
            $nfe->created_at = $venda->data;
            $nfe->save();
            return 0;
        }

        $nfe->data_emissao = $venda->data;
        $nfe->save();
        foreach ($venda->produtos as $i) {
            $p = Produto::where('empresa_id', $cliente->empresa_id)
                ->where('nome', $i->nome)->first();
            if ($p != null) {
                $ncm = $i->ncm;
                $mask = '####.##.##';
                if (!str_contains($ncm, ".")) {
                    $ncm = __mask($ncm, $mask);
                }

                ItemNfe::create([
                    'nfe_id' => $nfe->id,
                    'produto_id' => $p->id,
                    'quantidade' => $i->quantidade,
                    'valor_unitario' => $i->valor_unitario,
                    'sub_total' => $i->sub_total,
                    'perc_icms' => $i->perc_icms,
                    'perc_pis' => $i->perc_pis,
                    'perc_cofins' => $i->perc_pis,
                    'perc_ipi' => $i->perc_pis,
                    'cst_csosn' => $i->perc_pis,
                    'cst_pis' => $i->perc_pis,
                    'cst_cofins' => $i->perc_pis,
                    'cst_ipi' => $i->perc_pis,
                    'perc_red_bc' => $i->perc_pis,
                    'cfop' => $i->cfop,
                    'ncm' => $ncm,
                    'cEnq' => $i->cEnq,
                    'origem' => $i->origem,
                    'cest' => $i->cest

                ]);
            }
        }

        foreach ($venda->fatura as $f) {
            FaturaNfe::create([
                'nfe_id' => $nfe->id,
                'tipo_pagamento' => $venda->tipo_pagamento,
                'data_vencimento' => $f->vencimento,
                'valor' => __convert_value_bd($f->valor_parcela)
            ]);

            if (strtotime($f->vencimento) >= strtotime(date('Y-m-d'))) {
                ContaReceber::create([
                    'empresa_id' => $nfe->empresa_id,
                    'nfe_id' => $nfe->id,
                    'cliente_id' => $cliente->id,
                    'valor_integral' => __convert_value_bd($f->valor_parcela),
                    'tipo_pagamento' => $venda->tipo_pagamento,
                    'data_vencimento' => $f->vencimento,
                    'local_id' => $local_id,
                ]);
            }
        }

        return 1;
    }

    private function insereNatureza($descricao)
    {
        $natureza = NaturezaOperacao::where('descricao', $descricao)
            ->where('empresa_id', request()->empresa_id)
            ->first();

        if ($natureza != null) return $natureza;

        $data = [
            'descricao' => $descricao,
            'empresa_id' => request()->empresa_id,
        ];
        return NaturezaOperacao::create($data);
    }

    private function insereCliente($data)
    {

        if (!isset($data->cpf_cnpj)) return null;
        $cpf_cnpj = $data->cpf_cnpj;

        $mask = '##.###.###/####-##';

        if (strlen($cpf_cnpj) == 11) {
            $mask = '###.###.###.##';
        }

        if (!str_contains($cpf_cnpj, ".")) {
            $cpf_cnpj = __mask($cpf_cnpj, $mask);
        }

        $data->cpf_cnpj = $cpf_cnpj;

        $cliente = Cliente::where('cpf_cnpj', $cpf_cnpj)->where('empresa_id', request()->empresa_id)
            ->first();

        if ($cliente != null) return $cliente;

        return Cliente::create((array)$data);
    }

    private function insereProdutos($data, $local_id)
    {
        $produtos = [];
        foreach ($data as $item) {
            $produto = Produto::where('empresa_id', request()->empresa_id)
                ->where('nome', $item->nome)->first();

            if ($produto == null) {

                $ncm = $item->ncm;
                $mask = '####.##.##';
                if (!str_contains($ncm, ".")) {
                    $item->ncm = __mask($ncm, $mask);
                }

                $p = Produto::create((array)$item);
                ProdutoLocalizacao::updateOrCreate([
                    'produto_id' => $p->id,
                    'localizacao_id' => $local_id
                ]);
                array_push($produtos, $p);
            } else {
                array_push($produtos, $produto);
            }
        }
        return $produtos;
    }

    public function destroyBatch(Request $request)
    {
        $removidos = 0;
        $itensContaReceber = []; // NFes bloqueadas por conta a receber
        $itensContaPagar = [];   // NFes bloqueadas por conta a pagar

        if (!$request->has('item_delete') || count($request->item_delete) === 0) {
            return redirect()->back()->with('error', 'Nenhum item selecionado para exclusão.');
        }

        try {
            DB::beginTransaction();

            foreach ($request->item_delete as $itemId) {
                $item = Nfe::findOrFail($itemId);

                // Verifica se há uma conta a receber associada
                if (ContaReceber::where('nfe_id', $item->id)->exists()) {
                    $itensContaReceber[] = $item->numero_sequencial;
                    continue; // Pula para o próximo item se não puder excluir
                }

                // Verifica se há uma conta a pagar associada
                if (ContaPagar::where('nfe_id', $item->id)->exists()) {
                    $itensContaPagar[] = $item->numero_sequencial;
                    continue; // Pula para o próximo item se não puder excluir
                }

                $descricaoLog = "Número {$item->numero_sequencial} excluído";

                // Excluir os itens associados
                $item->itens()->delete();

                // Excluir fatura, se existir
                if ($item->fatura) {
                    $item->fatura()->delete();
                }

                // Excluir a NFe/Compra
                $item->delete();

                // Registrar o log da exclusão
                Log::info($descricaoLog, ['empresa_id' => request()->empresa_id, 'acao' => 'excluir']);

                $removidos++;
            }

            DB::commit();

            // Exibe mensagens específicas para cada tipo de bloqueio
            if (!empty($itensContaReceber)) {
                sort($itensContaReceber);
                session()->flash(
                    "flash_error",
                    "As seguintes NFe estão associadas a uma conta a receber e não podem ser excluídas: " .
                        implode(', ', $itensContaReceber) .
                        ". Exclua a conta a receber primeiro."
                );
            }

            if (!empty($itensContaPagar)) {
                sort($itensContaPagar);
                session()->flash(
                    "flash_error",
                    "As seguintes NFe estão associadas a uma conta a pagar e não podem ser excluídas: " .
                        implode(', ', $itensContaPagar) .
                        ". Exclua a conta a pagar primeiro."
                );
            }

            // Mensagem de sucesso
            if ($removidos > 0) {
                session()->flash("flash_success", "Total de {$removidos} NFe removidas com sucesso.");
            }

            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao excluir NFe: " . $e->getMessage(), ['empresa_id' => request()->empresa_id]);

            session()->flash("flash_error", "Erro ao excluir NFe: " . $e->getMessage());
            return redirect()->back();
        }
    }



    /**
     * Retorna o tipo do item com base na página onde o usuário está (NFe ou Compra).
     */
    private function tipoItem($request)
    {
        return request()->routeIs('compras.*') ? 'Compra' : 'NFe';
    }

    /**
     * Retorna o tipo do item no plural para mensagens dinâmicas.
     */
    private function tipoItemPlural($request)
    {
        return request()->routeIs('compras.*') ? 'compras' : 'NFes';
    }

    /**
     * Retorna a mensagem correta para "Nenhum item selecionado".
     */
    private function mensagemNenhumSelecionado($request)
    {
        return request()->routeIs('compras.*') ? 'Nenhuma compra selecionada.' : 'Nenhuma Nota Fiscal selecionada.';
    }

    public function destroy($id)
    {
        $item = Nfe::findOrFail($id);

        try {
            // Verifica se a NFe está associada a uma Conta a Receber
            if (ContaReceber::where('nfe_id', $item->id)->exists()) {
                session()->flash(
                    "flash_error",
                    "A NFe {$item->numero_sequencial} está associada a uma conta a receber e não pode ser excluída. Exclua a conta a receber primeiro."
                );
                return redirect()->back();
            }

            // Verifica se a NFe está associada a uma Conta a Pagar
            if (ContaPagar::where('nfe_id', $item->id)->exists()) {
                session()->flash(
                    "flash_error",
                    "A NFe {$item->numero_sequencial} está associada a uma conta a pagar e não pode ser excluída. Exclua a conta a pagar primeiro."
                );
                return redirect()->back();
            }

            DB::beginTransaction();

            // Repor estoque dos produtos, se necessário
            foreach ($item->itens as $i) {
                if ($i->produto->gerenciar_estoque) {
                    $this->util->incrementaEstoque($i->produto_id, $i->quantidade, $i->variacao_id, $item->local_id);
                }
            }

            // Excluir itens associados
            $item->itens()->delete();

            // Excluir fatura, se existir
            if ($item->fatura) {
                $item->fatura()->delete();
            }

            // Excluir a NFe/Compra
            $item->delete();

            DB::commit();

            session()->flash("flash_success", "NFe {$item->numero_sequencial} removida com sucesso.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao excluir NFe: " . $e->getMessage(), ['empresa_id' => request()->empresa_id]);

            session()->flash("flash_error", "Erro ao excluir NFe: " . $e->getMessage());
        }

        return redirect()->back();
    }
}
