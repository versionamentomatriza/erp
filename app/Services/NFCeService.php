<?php

namespace App\Services;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

use NFePHP\Common\Certificate;
use NFePHP\NFe\Tools;
use NFePHP\NFe\Make;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
use App\Models\Nfce;
use App\Models\Empresa;
use App\Models\ConfiguracaoSuper;
use App\Models\Ibpt;

class NFCeService
{

	private $config;
	private $tools;
	protected $empresa_id = null;

	protected $timeout = 15;

	public function __construct($config, $empresa)
	{

		$this->empresa_id = $empresa->id;

		$this->config = $config;
		$this->tools = new Tools(json_encode($config), Certificate::readPfx($empresa->arquivo, $empresa->senha));
		$this->tools->model(65);

		$config = ConfiguracaoSuper::first();
		if($config){
			if($config->timeout_nfce){
				$this->timeout = $config->timeout_nfce;
			}
		}
	}

	public function gerarXml($item)
	{

		$nfe = new Make();
		$stdInNFe = new \stdClass();
		$stdInNFe->versao = '4.00';
		$stdInNFe->Id = null;
		$stdInNFe->pk_nItem = '';

		$infNFe = $nfe->taginfNFe($stdInNFe);
		$emitente = $item->empresa;
		$emitente = __objetoParaEmissao($emitente, $item->local_id);

		$cliente = $item->cliente;

		$stdIde = new \stdClass();
		$stdIde->cUF = Empresa::getCodUF($emitente->cidade->uf); // codigo uf emitente
		$stdIde->cNF = rand(11111, 99999);
		// $stdIde->natOp = $venda->natureza->natureza;
		$stdIde->natOp = $item->natureza->descricao;

		$stdIde->mod = 65;
		$stdIde->serie = $item->numero_serie;

		// $stdIde->nNF = $item->lastNumero(); // numero sequencial da nfce
		$stdIde->nNF = $item->numero; // numero sequencial da nfe
		$stdIde->dhEmi = date("Y-m-d\TH:i:sP");
		$stdIde->dhSaiEnt = date("Y-m-d\TH:i:sP");
		$stdIde->tpNF = 1;
		$stdIde->idDest = 1;
		$stdIde->cMunFG = $emitente->cidade->codigo;
		$stdIde->tpImp = 4;
		$stdIde->tpEmis = 1;
		$stdIde->cDV = 0;
		$stdIde->tpAmb = (int)$emitente->ambiente;
		$stdIde->finNFe = 1;
		$stdIde->indFinal = 1;
		$stdIde->indPres = 1;
		$stdIde->procEmi = '0';
		$stdIde->verProc = '2.0';
		$tagide = $nfe->tagide($stdIde); //fim da tagide

		// inicia tag do emitente
		$stdEmit = new \stdClass();
		$stdEmit->xNome = $emitente->nome;
		$stdEmit->xFant = $emitente->nome_fantasia;
		$stdEmit->CRT = $emitente->tributacao == 'Regime Normal' ? 3 : 1;
		$stdEmit->IE = preg_replace('/[^0-9]/', '', $emitente->ie);

		$cpf_cnpj = preg_replace('/[^0-9]/', '', $emitente->cpf_cnpj);
		if (strlen($cpf_cnpj) == 14) {
			$stdEmit->CNPJ = $cpf_cnpj;
		} else {
			$stdEmit->CPF = $cpf_cnpj;
		}
		$emit = $nfe->tagemit($stdEmit);


		$stdEnderEmit = new \stdClass();
		$stdEnderEmit->xLgr = $emitente->rua;
		$stdEnderEmit->nro = $emitente->numero;
		$stdEnderEmit->xCpl = $emitente->complemento;
		$stdEnderEmit->xBairro = $emitente->bairro;
		$stdEnderEmit->cMun = $emitente->cidade->codigo;
		$stdEnderEmit->xMun = $emitente->cidade->nome;
		$stdEnderEmit->UF = $emitente->cidade->uf;
		$stdEnderEmit->CEP = preg_replace('/[^0-9]/', '', $emitente->cep);
		$stdEnderEmit->cPais = '1058';
		$stdEnderEmit->xPais = 'BRASIL';
		$enderEmit = $nfe->tagenderEmit($stdEnderEmit); // fim tag do emitente

		// inicia tag do destinatario
		if ($cliente != null) {

			$stdDest = new \stdClass();
			$stdDest->xNome = $cliente->razao_social;
			if ($cliente->contribuinte == 1) {
				if ($cliente->ie == '') {
					$stdDest->indIEDest = "2";
				} else {
					$stdDest->indIEDest = "1";
				}
			} else {
				$stdDest->indIEDest = "9";
			}

			$cpf_cnpj = preg_replace('/[^0-9]/', '', $cliente->cpf_cnpj);

			if (strlen($cpf_cnpj) == 14) {
				$stdDest->CNPJ = $cpf_cnpj;
				$ie = preg_replace('/[^0-9]/', '', $cliente->ie);
				$stdDest->IE = $ie;
			} else {
				$stdDest->CPF = $cliente->cpf_cnpj;
			}
			$dest = $nfe->tagdest($stdDest);

			$stdEnderDest = new \stdClass();
			$stdEnderDest->xLgr = $cliente->rua;
			$stdEnderDest->nro = $cliente->numero;
			$stdEnderDest->xCpl = $cliente->complemento;
			$stdEnderDest->xBairro = $cliente->bairro;
			$stdEnderDest->cMun = $cliente->cidade->codigo;
			$stdEnderDest->xMun = $cliente->cidade->nome;
			$stdEnderDest->UF = $cliente->cidade->uf;
			$stdEnderDest->fone = preg_replace('/[^0-9]/', '', $cliente->telefone);
			$stdEnderDest->CEP = preg_replace('/[^0-9]/', '', $cliente->cep);
			$stdEnderDest->cPais = '1058';
			$stdEnderDest->xPais = 'BRASIL';

			$enderDest = $nfe->tagenderDest($stdEnderDest);
		}

		if ($item->cliente_cpf_cnpj != "") {
			$stdDest = new \stdClass();
			if ($item->cliente_nome) {
				$stdDest->xNome = $item->cliente_nome;
			}
			$stdDest->indIEDest = "9";

			$doc = preg_replace('/[^0-9]/', '', $item->cliente_cpf_cnpj);
			if (strlen($doc) == 14) $stdDest->CNPJ = $doc;
			else $stdDest->CPF = $doc;
			$dest = $nfe->tagdest($stdDest);
		}
		//fim tag destinatario endereço

		$somaProdutos = 0;
		$somaICMS = 0;
		$somaFrete = 0;
		$somaIpi = 0;
		$totalItens = sizeof($item->itens);
		$somaDesconto = 0;

		$obsIbpt = "";
		$somaFederal = 0;
		$somaEstadual = 0;
		$somaMunicipal = 0;
		$somaVICMSST = 0;

		foreach ($item->itens as $itemCont => $i) {
			$itemCont++;

			$stdProd = new \stdClass(); // tag produto inicio
			$stdProd->item = $itemCont;

			$validaEan = $this->validate_EAN13Barcode($i->produto->codigo_barras);
			$stdProd->cEAN = $validaEan ? $i->produto->codigo_barras : 'SEM GTIN';
			$stdProd->cEANTrib = $validaEan ? $i->produto->codigo_barras : 'SEM GTIN';

			$stdProd->cProd = $i->produto->id;
			$stdProd->xProd = $i->descricao();
			$stdProd->NCM = preg_replace('/[^0-9]/', '', $i->ncm);
			$ibpt = Ibpt::getItemIbpt($emitente->cidade->uf, preg_replace('/[^0-9]/', '', $i->ncm));

			$stdProd->CFOP = $i->cfop;
			$stdProd->uCom = $i->produto->unidade;
			$stdProd->qCom = $i->quantidade;
			$stdProd->vUnCom = $this->format($i->valor_unitario);
			$stdProd->vProd = $this->format(($i->quantidade * $i->valor_unitario));
			$stdProd->uTrib = $i->produto->unidade;
			$stdProd->qTrib = $i->quantidade;
			$stdProd->vUnTrib = $this->format($i->valor_unitario);
			$stdProd->indTot = 1;

			// if ($item->desconto > 0) {
			// 	if ($itemCont < sizeof($item->itens)) {
			// 		$totalVenda = $item->total + $item->desconto;
			// 		$media = (((($stdProd->vProd - $totalVenda) / $totalVenda)) * 100);
			// 		$media = 100 - ($media * -1);
			// 		if ($item->desconto > 0.1) {
			// 			$tempDesc = ($item->desconto * $media) / 100;
			// 		} else {
			// 			$tempDesc = $item->desconto;
			// 		}
			// 		if ($somaDesconto >= $item->desconto) {
			// 			$tempDesc = 0;
			// 		}
			// 		$somaDesconto += $tempDesc;
			// 		if ($tempDesc > 0.01)
			// 			$stdProd->vDesc = $this->format($tempDesc);
			// 	} else {
			// 		if ($item->desconto - $somaDesconto >= 0.01)
			// 			$stdProd->vDesc = $this->format($item->desconto - $somaDesconto);
			// 	}
			// }

			if($item->desconto > 0.01 && $somaDesconto < $item->desconto){
				if($itemCont < sizeof($item->itens)){
					$totalVenda = $item->total + $item->desconto;

					$media = (((($stdProd->vProd - $totalVenda)/$totalVenda))*100);
					$media = 100 - ($media * -1);

					$tempDesc = ($item->desconto*$media)/100;
					$tempDesc -= 0.01;
					if($tempDesc > 0.01){
						$somaDesconto += $this->format($tempDesc);
						$stdProd->vDesc = $this->format($tempDesc);
					}else{
						if(sizeof($item->itens) > 1){
							$somaDesconto += 0.01;
							$stdProd->vDesc = $this->format(0.01);
						}else{
							$somaDesconto = $item->desconto;
							$stdProd->vDesc = $this->format($somaDesconto);
						}
					}
				}else{
					if(($item->desconto - $somaDesconto) > 0.01){
						$stdProd->vDesc = $this->format($item->desconto - $somaDesconto);
					}
				}
			}

			$prod = $nfe->tagprod($stdProd); // fim tag de produtos

			$stdImposto = new \stdClass();
			$stdImposto->item = $itemCont;

			if($ibpt != null){

				$vProd = $stdProd->vProd;

				if($i->produto->origem == 1 || $i->produto->origem == 2){
					$federal = $this->format(($vProd*($ibpt->importado_federal/100)), 2);

				}else{
					$federal = $this->format(($vProd*($ibpt->nacional_federal/100)), 2);
				}
				$somaFederal += $federal;

				$estadual = $this->format(($vProd*($ibpt->estadual/100)), 2);
				$somaEstadual += $estadual;

				$municipal = $this->format(($vProd*($ibpt->municipal/100)), 2);
				$somaMunicipal += $municipal;

				$soma = $federal + $estadual + $municipal;
				$stdImposto->vTotTrib = $soma;

				$obsIbpt = " FONTE: " . $ibpt->versao ?? '';
				$obsIbpt .= " | ";
			}
			$imposto = $nfe->tagimposto($stdImposto); // tag imposto

			if ($stdEmit->CRT == 1) {
				$stdICMS = new \stdClass();

				$stdICMS->item = $itemCont;
				$stdICMS->orig = 0;
				$stdICMS->CSOSN = $i->cst_csosn;

				if (isset($i['vBCSTRet'])) $stdICMS->vBCSTRet = $i['vBCSTRet'];
				if (isset($i['pST'])) $stdICMS->pST = $i['pST'];
				if (isset($i['vICMSSTRet'])) $stdICMS->vICMSSTRet = $i['vICMSSTRet'];
				if (isset($i['vICMSSubstituto'])) $stdICMS->vICMSSubstituto = $i['vICMSSubstituto'];


				$stdICMS->pCredSN = $this->format($i->perc_icms);
				$stdICMS->vCredICMSSN = $this->format($i->perc_icms);
				$ICMS = $nfe->tagICMSSN($stdICMS);

				$somaICMS = 0;
			} else if ($stdEmit->CRT == 3) {
				$somaProdutos += $stdProd->vProd;

				$stdICMS = new \stdClass();
				$stdICMS->item = $itemCont;
				$stdICMS->orig = 0;
				$stdICMS->CST = $i->cst_csosn;
				$stdICMS->modBC = 0;
				$stdICMS->vBC = $this->format($i->valor_unitario * $i->quantidade);
				$stdICMS->pICMS = $this->format($i->perc_icms);
				$stdICMS->vICMS = $stdICMS->vBC * ($stdICMS->pICMS / 100);

				if (isset($i['vBCSTRet'])) $stdICMS->vBCSTRet = $i['vBCSTRet'];
				if (isset($i['pST'])) $stdICMS->vBCSTRet = $i['pST'];
				if (isset($i['vICMSSTRet'])) $stdICMS->vBCSTRet = $i['vICMSSTRet'];
				if (isset($i['vICMSSubstituto'])) $stdICMS->vBCSTRet = $i['vICMSSubstituto'];

				if($i->cst_csosn == 10){

					$stdICMS->modBCST = $i->produto->modBCST;
					$stdICMS->vBCST = $stdProd->vProd;
					$stdICMS->pICMSST = $this->format($i->produto->pICMSST);
					$somaVICMSST += $stdICMS->vICMSST = $stdICMS->vBCST * ($stdICMS->pICMSST/100);
				}

				$somaICMS += (($i->valor_unitario * $i->quantidade)
					* ($stdICMS->pICMS / 100));
				$ICMS = $nfe->tagICMS($stdICMS);
			} // fim tag icms
			//PIS
			$stdPIS = new \stdClass();
			$stdPIS->item = $itemCont;
			$stdPIS->CST = $i->cst_pis;
			$stdPIS->vBC = $this->format($i->perc_pis) > 0 ? $stdProd->vProd : 0.00;
			$stdPIS->pPIS = $this->format($i->perc_pis);
			$stdPIS->vPIS = $this->format($stdProd->vProd * ($i->perc_pis / 100));
			$PIS = $nfe->tagPIS($stdPIS);

			//COFINS
			$stdCOFINS = new \stdClass();
			$stdCOFINS->item = $itemCont;
			$stdCOFINS->CST = $i->cst_cofins;
			$stdCOFINS->vBC = $this->format($i->cst_cofins) > 0 ? $stdProd->vProd : 0.00;
			$stdCOFINS->pCOFINS = $this->format($i->perc_cofins);
			$stdCOFINS->vCOFINS = $this->format($stdProd->vProd * ($i->perc_cofins / 100));
			$COFINS = $nfe->tagCOFINS($stdCOFINS);

			if(strlen($i->produto->codigo_anp) > 2){
				$stdComb = new \stdClass();
				$stdComb->item = $itemCont; 
				$stdComb->cProdANP = $i->produto->codigo_anp;
				$stdComb->descANP = $i->produto->getDescricaoAnp();
				if($i->produto->perc_glp > 0){
					$stdComb->pGLP = $this->format($i->produto->perc_glp);
				}

				if($i->produto->perc_gnn > 0){
					$stdComb->pGNn = $this->format($i->produto->perc_gnn);
				}

				if($i->produto->perc_gni > 0){
					$stdComb->pGNi = $this->format($i->produto->perc_gni);
				}

				$stdComb->vPart = $this->format($i->produto->valor_partida);
				$stdComb->UFCons = $item->cliente ? $item->cliente->cidade->uf : $emitente->cidade->uf;

				if($i->produto->pBio > 0){
					$stdComb->pBio = $i->produto->pBio;
				}
				$nfe->tagcomb($stdComb);
			}

			if($stdIde->indFinal == 0 && strlen($i->produto->codigo_anp) > 2){
				$stdOrigComb = new \stdClass();

				$stdOrigComb->item = $itemCont; 
				$stdOrigComb->indImport = $i->produto->indImport;
				$stdOrigComb->cUFOrig = $i->produto->cUFOrig;
				$stdOrigComb->pOrig = $i->produto->pOrig;
				$nfe->tagorigComb($stdOrigComb);
			}

			$cest = $i->produto->cest;
			$cest = str_replace(".", "", $cest);
			$stdProd->CEST = $cest;
			if(strlen($cest) > 0){
				$std = new \stdClass();
				$std->item = $itemCont; 
				$std->CEST = $cest;
				$nfe->tagCEST($std);
			}
		}

		$stdICMSTot = new \stdClass();
		$stdICMSTot->vBC = $this->format($somaProdutos);
		$stdICMSTot->vICMS = $this->format($somaICMS);
		$stdICMSTot->vICMSDeson = 0.00;
		$stdICMSTot->vBCST = 0.00;
		$stdICMSTot->vST = 0.00;
		$stdICMSTot->vProd = 0;
		$stdICMSTot->vFrete = 0.00;
		$stdICMSTot->vSeg = 0.00;
		$stdICMSTot->vDesc = $this->format($item->desconto);
		$stdICMSTot->vII = 0.00;
		$stdICMSTot->vIPI = 0.00;
		$stdICMSTot->vPIS = 0.00;
		$stdICMSTot->vCOFINS = 0.00;
		$stdICMSTot->vOutro = 0.00;
		$stdICMSTot->vNF = $this->format($item->total  + $somaVICMSST);

		$stdICMSTot->vTotTrib = 0.00;
		$ICMSTot = $nfe->tagICMSTot($stdICMSTot);

		$stdTransp = new \stdClass();
		$stdTransp->modFrete = 9;

		$transp = $nfe->tagtransp($stdTransp);

		$respTec = ConfiguracaoSuper::first();
		if ($respTec != null) {
			$stdResp = new \stdClass();
			$stdResp->CNPJ = preg_replace('/[^0-9]/', '', $respTec->cpf_cnpj);
			$stdResp->xContato = $respTec->name;
			$stdResp->email = $respTec->email;
			$stdResp->fone = preg_replace('/[^0-9]/', '', $respTec->telefone);
			$nfe->taginfRespTec($stdResp);
		}

		// dd($item->desconto);
		//Fatura
		$stdFat = new \stdClass();
		$stdFat->nFat = $stdIde->nNF;
		$stdFat->vOrig = $this->format($item->total);
		$stdFat->vDesc = $this->format($item->desconto);
		$stdFat->vLiq = $this->format($item->total);

		// $fatura = $nfe->tagfat($stdFat);

		$contFatura = 1;

		$stdPag = new \stdClass();
		if ($item->dinheiro_recebido > 0) {
			$vPag = $item->dinheiro_recebido;
			$stdPag->vTroco = $vPag - $item->total;
		}
		// dd($this->format($item->total));
		$pag = $nfe->tagpag($stdPag);


		if ($item->dinheiro_recebido > 0) {
			$stdDetPag = new \stdClass();
			$stdDetPag->tPag = $item->tipo_pagamento;
			if($item->tipo_pagamento == '06'){
				$stdDetPag->tPag = '05'; 
			}
			$stdDetPag->vPag = $this->format($item->dinheiro_recebido);
			$stdDetPag->indPag = 1;
			$detPag = $nfe->tagdetPag($stdDetPag);
		} else {

			if (sizeof($item->fatura) > 0) {
				foreach ($item->fatura as $ft) {

					$stdDetPag = new \stdClass();
					$stdDetPag->tPag = $ft->tipo_pagamento;
					if($ft->tipo_pagamento == '06'){
						$stdDetPag->tPag = '05'; 
					}
					$stdDetPag->vPag = $this->format($ft->valor);
					$stdDetPag->indPag = 1;

					if($ft->tipo_pagamento == '03' || $ft->tipo_pagamento == '04' || $ft->tipo_pagamento == '17'){
						
						$stdDetPag->tBand = $item->bandeira_cartao;
						if(!$item->bandeira_cartao){
							$stdDetPag->tBand = '01';
						}

						if($item->cnpj_cartao){
							$stdDetPag->CNPJ = preg_replace('/[^0-9]/', '', $item->cnpj_cartao);
						}

						if($item->cAut_cartao != ""){
							$stdDetPag->cAut = $item->cAut_cartao;
						}


						$stdDetPag->tpIntegra = 2;
					}
					$detPag = $nfe->tagdetPag($stdDetPag);
				}
			}
		}


		$stdInfoAdic = new \stdClass();
		$obs = $item->observacao;

		if($somaEstadual > 0 || $somaFederal > 0 || $somaMunicipal > 0){
			$obs .= " Trib. aprox. ";
			if($somaFederal > 0){
				$obs .= "R$ " . number_format($somaFederal, 2, ',', '.') ." Federal"; 
			}
			if($somaEstadual > 0){
				$obs .= ", R$ ".number_format($somaEstadual, 2, ',', '.')." Estadual"; 
			}
			if($somaMunicipal > 0){
				$obs .= ", R$ ".number_format($somaMunicipal, 2, ',', '.')." Municipal"; 
			}
			// $ibpt = IBPT::where('uf', $config->UF)->first();

			$obs .= $obsIbpt;
		}
		$stdInfoAdic->infCpl = $obs;

		$infoAdic = $nfe->taginfAdic($stdInfoAdic);

		try {
			$nfe->montaNFe();
			$arr = [
				'chave' => $nfe->getChave(),
				'xml' => $nfe->getXML(),
				'numero' => $stdIde->nNF
			];
			return $arr;
		} catch (\Exception $e) {
			return [
				'erros_xml' => $nfe->getErrors()
			];
		}
	}

	private function validate_EAN13Barcode($ean)
	{

		$sumEvenIndexes = 0;
		$sumOddIndexes  = 0;

		$eanAsArray = array_map('intval', str_split($ean));

		if(strlen($ean) == 14){
			return true;
		}

		if (!$this->has13Numbers($eanAsArray) ) {
			return false;
		};

		for ($i = 0; $i < count($eanAsArray)-1; $i++) {
			if ($i % 2 === 0) {
				$sumOddIndexes  += $eanAsArray[$i];
			} else {
				$sumEvenIndexes += $eanAsArray[$i];
			}
		}

		$rest = ($sumOddIndexes + (3 * $sumEvenIndexes)) % 10;

		if ($rest !== 0) {
			$rest = 10 - $rest;
		}

		return $rest === $eanAsArray[12];
	}

	private function has13Numbers(array $ean)
	{
		return count($ean) === 13 || count($ean) === 14;
	}

	public function format($number, $dec = 2)
	{
		return number_format((float) $number, $dec, ".", "");
	}

	public function sign($xml)
	{
		return $this->tools->signNFe($xml);
	}

	public function transmitir($signXml, $chave)
	{
		try {
			$idLote = str_pad(100, 15, '0', STR_PAD_LEFT);
			$resp = $this->tools->sefazEnviaLote([$signXml], $idLote, 1);

			$st = new Standardize();
			$std = $st->toStd($resp);
			sleep($this->timeout);
			
			if ($std->cStat != 103 && $std->cStat != 104) {

				return [
					'erro' => 1,
					'error' => "[$std->cStat] - $std->xMotivo",
					'cStat' => $std->cStat
				];
			}

			sleep(2);
			try {
				$xml = Complements::toAuthorize($signXml, $resp);
				file_put_contents(public_path('xml_nfce/') . $chave . '.xml', $xml);
				return [
					'erro' => 0,
					'success' => $std->protNFe->infProt->nProt
				];
				// $this->printDanfe($xml);
			} catch (\Exception $e) {
				return [
					'erro' => 1,
					'error' => $st->toArray($resp),
					'recibo' => $resp
				];
			}
		} catch (\Exception $e) {
			return [
				'erro' => 1,
				'error' => $e->getMessage()
			];
		}
	}

	public function consultar($nfe)
	{
		try {

			$this->tools->model('65');

			$chave = $nfe->chave;
			$response = $this->tools->sefazConsultaChave($chave);

			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			if($arr['xMotivo'] == 'Autorizado o uso da NF-e'){
				if($nfe->estado != 'aprovado'){

					$empresa = Empresa::findOrFail($nfe->empresa_id);
					$empresa = __objetoParaEmissao($empresa, $nfe->local_id);

					$chave = $arr['protNFe']['infProt']['chNFe'];
					$nRec = $nfe->recibo;
					$nfe->estado = 'aprovado';
					$nfe->save();

					if($empresa->ambiente == 1){
						$empresa->numero_ultima_nfce_producao = $nfe->numero;
					}else{
						$empresa->numero_ultima_nfce_homologacao = $nfe->numero;
					}
					try{
						$xml = Complements::toAuthorize($nfe->signed_xml, $nRec);
						file_put_contents(public_path('xml_nfce/').$chave.'.xml', $xml);
					}catch(\Exception $e){
						
					}

				}
			}

			return $arr;
		} catch (\Exception $e) {
			return ['erro' => true, 'data' => $e->getMessage(), 'status' => 402];
		}
	}

	public function cancelar($nfe, $motivo)
	{
		try {

			$chave = $nfe->chave;
			$response = $this->tools->sefazConsultaChave($chave);
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			sleep(1);

			$nProt = $arr['protNFe']['infProt']['nProt'];

			$response = $this->tools->sefazCancela($chave, $motivo, $nProt);
			sleep(2);
			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();

			if ($std->cStat != 128) {
				//TRATAR
			} else {
				$cStat = $std->retEvento->infEvento->cStat;
				if ($cStat == '101' || $cStat == '135' || $cStat == '155') {
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
					file_put_contents(public_path('xml_nfce_cancelada/') . $chave . '.xml', $xml);

					return $arr;
				} else {

					return ['erro' => true, 'data' => $arr, 'status' => 402];
				}
			}
		} catch (\Exception $e) {
			// echo $e->getMessage();
			return ['erro' => true, 'data' => $e->getMessage(), 'status' => 402];
			//TRATAR
		}
	}

	public function consultaStatus($tpAmb, $uf)
	{
		try {
			$response = $this->tools->sefazStatus($uf, $tpAmb);
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			return $arr;
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}
}
