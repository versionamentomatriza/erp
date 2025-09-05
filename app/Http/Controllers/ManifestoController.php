<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProduto;
use App\Models\Empresa;
use App\Models\Fornecedor;
use App\Models\ManifestoDfe;
use App\Models\NaturezaOperacao;
use App\Models\Produto;
use App\Models\Cidade;
use App\Models\Transportadora;
use App\Services\DFeService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use NFePHP\NFe\Common\Standardize;
use NFePHP\DA\NFe\Danfe;
use Svg\Tag\Rect;

class ManifestoController extends Controller
{
    public function __construct()
    {
         $this->middleware('permission:manifesto_view', ['only' => ['index']]);
        if (!is_dir(public_path('xml_dfe'))) {
            mkdir(public_path('xml_dfe'), 0777, true);
        }
    }

    public function index(Request $request)
    {

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $tipo = $request->tipo;

        $data = ManifestoDfe::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('data_emissao', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('data_emissao', '<=', $end_date);
        })
        ->orderBy('data_emissao', 'desc')
        ->paginate(getenv("PAGINACAO"));

        return view('manifesto.index', compact('data'));
    }

    public function novaConsulta()
    {
        return view('manifesto.nova_consulta');
    }

    public function manifestar(Request $request)
    {
        $config = Empresa::where('id', $request->empresa_id)
        ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cpf_cnpj);

        $dfe_service = new DFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => 1,
            "razaosocial" => $config->nome,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], $config);
        $evento = $request->tipo;

        $manifestaAnterior = $this->verificaAnterior($request->chave);
        $numEvento = $manifestaAnterior != null ? ((int)$manifestaAnterior->sequencia_evento + 1) : 1;

        if ($manifestaAnterior != null && $manifestaAnterior->tipo != $evento) {
            $numEvento--;
        }

        if ($numEvento == 0) $numEvento++;

        if ($evento == 1) {
            $res = $dfe_service->manifesta($request->chave,    $numEvento);
        } else if ($evento == 2) {
            $res = $dfe_service->confirmacao($request->chave, $numEvento);
        } else if ($evento == 3) {
            $res = $dfe_service->desconhecimento($request->chave, $numEvento, $request->justificativa);
        } else if ($evento == 4) {
            $res = $dfe_service->operacaoNaoRealizada($request->chave, $numEvento, $request->justificativa);
        }
        try {
            if ($res['retEvento']['infEvento']['cStat'] == '135') { //sucesso
                $manifesto = ManifestoDfe::where('empresa_id', $request->empresa_id)
                ->where('chave', $request->chave)
                ->first();
                $manifesto->sequencia_evento = $manifestaAnterior != null ? ($manifestaAnterior->sequencia_evento + 1) : 1;
                $manifesto->tipo = $evento;
                $manifesto->save();

                // ManifestaDfe::create($manifesta);
                session()->flash('flash_success', $res['retEvento']['infEvento']['xMotivo'] . ": " . $request->chave);
            } else {

                $manifesto = ManifestoDfe::where('empresa_id', $request->empresa_id)
                ->where('chave', $request->chave)
                ->first();

                // $manifesto->tipo = $evento;
                $manifesto->save();

                $erro = "[" . $res['retEvento']['infEvento']['cStat'] . "] " . $res['retEvento']['infEvento']['xMotivo'];

                session()->flash("flash_error", $erro . " - Chave: " . $request->chave);
            }
            return redirect()->route('manifesto.index');
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    private function verificaAnterior($chave)
    {
        return ManifestoDfe::where('empresa_id', request()->empresa_id)
        ->where('chave', $chave)->first();
    }

    public function download($id)
    {
        $naturezaPadrao = NaturezaOperacao::where('empresa_id', request()->empresa_id)->first();

        if ($naturezaPadrao == null) {
            session()->flash('flash_error', 'Cadastre uma naturezaz de operação!');
            return redirect()->route('naturezas.index');
        }
        
        $config = Empresa::where('id', request()->empresa_id)
        ->first();
        $dfe = ManifestoDfe::findOrFail($id);

        if($dfe->compra_id != null){
            session()->flash('flash_error', 'XML já foi importado!');
            return redirect()->back();
        }

        $chave = $dfe->chave;
        $cnpj = preg_replace('/[^0-9]/', '', $config->cpf_cnpj);

        $dfe_service = new DFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => 1,
            "razaosocial" => $config->nome,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], $config);

        try {

            $file_exists = false;
            if (file_exists(public_path('xml_dfe/') . $chave . '.xml')) {
                $file_exists = true;
            }
            if (!$file_exists) {

                $response = $dfe_service->download($chave);
                $stz = new Standardize($response);
                $std = $stz->toStd();
            } else {
                $std = null;
            }
            if ($std != null && ($std->cStat != 138)) {
                session()->flash("flash_error", "Documento não retornado. [$std->cStat] $std->xMotivo!");
                return redirect()->back();
            } else {
                if (!$file_exists) {
                    $zip = $std->loteDistDFeInt->docZip;
                    $xml = gzdecode(base64_decode($zip));
                    file_put_contents(public_path('xml_dfe/') . $chave . '.xml', $xml);
                } else {
                    $xml = file_get_contents(public_path('xml_dfe/') . $chave . '.xml');
                }
                if (strlen($xml) < 1000) {
                    unlink(public_path('xml_dfe/') . $chave . '.xml');
                }

                return $this->renderizaXml($xml);
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . '<br>' . $e->getLine();
            die;
        }
    }

    private function renderizaXml($xml){

        $xml = simplexml_load_string($xml);

        if ($xml->NFe->infNFe == null) {
            session()->flash('flash_error', 'Este XML parece inválido!');
            return redirect()->back();
        }

        $chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);

        file_put_contents(public_path('xml_entrada/') . $chave . '.xml', $xml);

        $cidade = Cidade::where('codigo', $xml->NFe->infNFe->emit->enderEmit->cMun)
        ->first();

        $doc = $xml->NFe->infNFe->emit->CNPJ ? $xml->NFe->infNFe->emit->CNPJ : $xml->NFe->infNFe->emit->CPF;
        $doc = trim($doc);
        $mask = '##.###.###/####-##';
        if (strlen($doc) == 11) {
            $mask = '###.###.###-##';
        }
        $doc = __mask($doc, $mask);

        $dataFornecedor = [

            'empresa_id' => request()   ->empresa_id,
            'razao_social' => $xml->NFe->infNFe->emit->xNome,
            'nome_fantasia' => $xml->NFe->infNFe->emit->xFant,
            'cpf_cnpj' => $doc,
            'ie' => $xml->NFe->infNFe->emit->IE,
            'contribuinte' => $xml->NFe->infNFe->emit->IE != '' ? 1 : 0,
            'consumidor_final' => 0,
            'email' => $xml->NFe->infNFe->emit->enderEmit->xBairro,
            'telefone' => $xml->NFe->infNFe->emit->enderEmit->fone,
            'cidade_id' => $cidade->id,
            'rua' => $xml->NFe->infNFe->emit->enderEmit->xLgr,
            'cep' => $xml->NFe->infNFe->emit->enderEmit->CEP,
            'numero' => $xml->NFe->infNFe->emit->enderEmit->nro,
            'bairro' => $xml->NFe->infNFe->emit->enderEmit->xBairro,
            'complemento' => $xml->NFe->infNFe->emit->enderEmit->xBairro
        ];

        $fornecedor = $this->cadastraFornecedor($dataFornecedor);
        $vFrete = (float)$xml->NFe->infNFe->total->ICMSTot->vFrete;
        $vDesc = (float)$xml->NFe->infNFe->total->ICMSTot->vDesc;

        $itens = [];
        $contSemRegistro = 0;
        foreach ($xml->NFe->infNFe->det as $item) {

            $produto = Produto::verificaCadastrado(
                $item->prod->cEAN,
                $item->prod->xProd,
                $item->prod->cProd,
                request()->empresa_id
            );

            $vIpi = 0;
            $vICMSST = 0;
            if (isset($item->imposto->IPI)) {
                $valor = (float)$item->imposto->IPI->IPITrib->vIPI;
                if ($valor > 0)
                    $vIpi = $valor / (float)$item->prod->qCom;
            }

            if (isset($item->imposto->ICMS)) {
                $arr = (array_values((array)$item->imposto->ICMS));
                $cst = $arr[0]->CST ? $arr[0]->CST : $arr[0]->CSOSN;
                $valor = (float)$arr[0]->vICMSST ?? 0;
                if ($valor > 0)
                    $vICMSST = $valor / $item->prod->qCom;
            }

            $nomeProduto = $item->prod->xProd;
            $nomeProduto = str_replace("'", "", $nomeProduto);
            $codigo = preg_replace('/[^0-9]/', '', $item->prod->cProd);

            if ($produto == null) {
                $contSemRegistro++;
            }

            $prod = new \stdClass();

            $prod->id = $produto != null ? $produto->id : 0;
            $prod->codigo = $codigo;
            $prod->xProd = $produto == null ? $nomeProduto : $produto->nome;
            $prod->ncm = (string)$item->prod->NCM;
            $prod->cest = (string)$item->prod->CEST;
            $prod->cfop = (string)$item->prod->CFOP;
            $prod->unidade = (string)$item->prod->uCom;
            $prod->valor_unitario = number_format((float)$item->prod->vUnCom + $vIpi + $vICMSST, 2, '.', '');
            $prod->quantidade = (float)$item->prod->qCom;
            $prod->sub_total = (float)$item->prod->vProd;
            $prod->codigo_barras = (string)$item->prod->cEAN;
            $prod->valor_venda = $produto == null ? 0 : $produto->valor_venda;
            $prod->valor_compra = $produto == null ? 0 : $produto->valor_compra;


            $arr = (array_values((array)$item->imposto->ICMS));
            $cst = (string)($arr[0]->CST ? $arr[0]->CST : $arr[0]->CSOSN);
            $pICMS = (float)$arr[0]->pICMS ?? 0;

            $prod->perc_red_bc = 0;
            $prod->perc_icms = $pICMS;
            $prod->cst_csosn = $cst;

            $arr = (array_values((array)$item->imposto->PIS));

            $prod->cst_pis = (string)$arr[0]->CST;
            $prod->perc_pis = (float)$arr[0]->pPIS ?? 0;

            $arr = (array_values((array)$item->imposto->COFINS));
            $prod->cst_cofins = $arr[0]->CST;
            $pCOFINS = $arr[0]->COFINS ?? 0;
            if ($pCOFINS == 0) {
                $pCOFINS = $arr[0]->pCOFINS ?? 0;
            }
            $prod->perc_cofins = $arr[0]->pPIS ?? 0;

            $arr = (array_values((array)$item->imposto->IPI));
            if (isset($arr[1])) {

                $cst_ipi = $arr[1]->CST ?? '99';
                $pIPI = $arr[0]->IPI ?? 0;
                if ($pIPI == 0) {
                    $pIPI = $arr[0]->pIPI ?? 0;
                }

                if (isset($arr[1]->pIPI)) {
                    $pIPI = $arr[1]->pIPI ?? 0;
                } else {
                    if (isset($arr[4]->pIPI)) {
                        $ipi = $arr[4]->CST;
                        $pIPI = $arr[4]->pIPI;
                    } else {
                        $pIPI = 0;
                    }
                }
            } else {
                $cst_ipi = '99';
                $pIPI = 0;
            }

            $prod->perc_ipi = $pIPI;
            $prod->cst_ipi = $cst_ipi;

            $prod->codigo_beneficio_fiscal = '';

            array_push($itens, $prod);
        }

        $dadosXml = [
            'chave' => $chave,
            'vProd' => (float)$xml->NFe->infNFe->total->ICMSTot->vNF,
            'indPag' => (int)$xml->NFe->infNFe->ide->indPag,
            'nNf' => (int)$xml->NFe->infNFe->ide->nNF,
            'vFrete' => $vFrete,
            'vDesc' => $vDesc,
            'contSemRegistro' => $contSemRegistro,
            'data_emissao' => substr($xml->NFe->infNFe->ide->dhEmi[0], 0, 16),
            'itens' => $itens
        ];

        if (!is_dir(public_path('xml_entrada'))) {
            mkdir(public_path('xml_entrada'), 0777, true);
        }
        $tPag = '';
        if($xml->NFe->infNFe->pag->detPag){
            $tPag = $xml->NFe->infNFe->pag->detPag->tPag;
        }
        $fatura = [];
        if (!empty($xml->NFe->infNFe->cobr->dup)) {
            foreach ($xml->NFe->infNFe->cobr->dup as $dup) {
                $titulo = $dup->nDup;
                $vencimento = (string)$dup->dVenc;
                // $vencimento = explode('-', $vencimento);
                // $vencimento = $vencimento[2] . "/" . $vencimento[1] . "/" . $vencimento[0];
                $valor_parcela = number_format((float) $dup->vDup, 2, ".", "");
                $parcela = [
                    'numero' => (int)$titulo,
                    'vencimento' => $vencimento,
                    'valor_parcela' => $valor_parcela,
                    'rand' => rand(0, 10000),
                    'tipo_pagamento' => $tPag
                ];
                array_push($fatura, $parcela);
            }
        } else {

            $vencimento = (string)substr($xml->NFe->infNFe->ide->dhEmi[0], 0, 10);
            // $vencimento = $vencimento[2] . "/" . $vencimento[1] . "/" . $vencimento[0];
            $parcela = [
                'numero' => 1,
                'vencimento' => $vencimento,
                'valor_parcela' => (float)$xml->NFe->infNFe->total->ICMSTot->vProd,
                'rand' => rand(0, 10000),
                'tipo_pagamento' => $tPag
            ];
            array_push($fatura, $parcela);
        }
        // dd($fatura);
        $dadosXml['fatura'] = $fatura;

        $transportadoras = Transportadora::where('empresa_id', request()->empresa_id)->get();
        $cidades = Cidade::all();
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        if (sizeof($naturezas) == 0) {
            session()->flash("flash_warning", "Primeiro cadastre um natureza de operação!");
            return redirect()->route('natureza-operacao.create');
        }

        return view('compras.import_xml', compact('dadosXml', 'transportadoras', 'cidades', 'naturezas', 'fornecedor'));

    }

    public function danfe($id)
    {
        $item = ManifestoDfe::findOrFail($id);
        $config = Empresa::where('id', request()->empresa_id)
        ->first();
        $cnpj = preg_replace('/[^0-9]/', '', $config->cpf_cnpj);
        $dfe_service = new DFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => 1,
            "razaosocial" => $config->nome,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], $config);

        // $response = $dfe_service->download($chave);

        $chave = $item->chave;
        $file_exists = false;
        if (file_exists(public_path('xml_dfe/') . $chave . '.xml')) {
            $file_exists = true;
        }
        if (!$file_exists) {
            $response = $dfe_service->download($chave);
            $stz = new Standardize($response);
            $std = $stz->toStd();
        } else {
            $std = null;
        }
        // print_r($response);
        try {
            if (!$file_exists) {
                $zip = $std->loteDistDFeInt->docZip;
                $xml = gzdecode(base64_decode($zip));

                file_put_contents(public_path('xml_dfe/') . $chave . '.xml', $xml);
            } else {
                $xml = file_get_contents(public_path('xml_dfe/') . $chave . '.xml');
            }

            if ($std != null && $std->cStat != 138) {
                echo "Documento não retornado. [$std->cStat] $std->xMotivo" . ", aguarde alguns instantes e atualize a pagina!";
                die;
            }
            $dfe = ManifestoDfe::where('chave', $chave)->first();
            $nfe = simplexml_load_string($xml);
            $nNF = $nfe->NFe->infNFe->ide->nNF;
            $dfe->nNF = $nNF;
            $dfe->save();

            file_put_contents(public_path('xml_dfe/') . $chave . '.xml', $xml);

            $danfe = new Danfe($xml);
            // $id = $danfe->monta();
            $pdf = $danfe->render();
            header('Content-Type: application/pdf');
            // echo $pdf;
            return response($pdf)
            ->header('Content-Type', 'application/pdf');
        } catch (InvalidArgumentException $e) {
            echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
        }
    }

    private function cadastraFornecedor($dataFornecedor)
    {
        $fornecedor = Fornecedor::where('cpf_cnpj', $dataFornecedor['cpf_cnpj'])
        ->where('empresa_id', request()->empresa_id)->first();

        if ($fornecedor == null) {
            $fornecedor = Fornecedor::create($dataFornecedor);
        }

        return $fornecedor;
    }
}
