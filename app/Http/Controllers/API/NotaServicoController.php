<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CloudDfe\SdkPHP\Nfse;
use App\Models\Empresa;
use App\Models\NotaServico;



class NotaServicoController extends Controller
{
    public function transmitir(Request $request){
        $item = NotaServico::findOrFail($request->id);
        $empresa = $item->empresa;

        $params = [
            'token' => $empresa->token_nfse,
            'ambiente' => Nfse::AMBIENTE_PRODUCAO,
            // 'ambiente' => $empresa->ambiente == 2 ? Nfse::AMBIENTE_HOMOLOGACAO : Nfse::AMBIENTE_PRODUCAO,
            'options' => [
                'debug' => false,
                'timeout' => 60,
                'port' => 443,
                'http_version' => CURL_HTTP_VERSION_NONE
            ]
        ];

        $nfse = new Nfse($params);
        $servico = $item->servico;
		
		//dd($servico->aliquota_iss);

        $novoNumeroNFse = $empresa->numero_ultima_nfse+1;

        try {

            $doc = preg_replace('/[^0-9]/', '', $item->documento);
            $im = preg_replace('/[^0-9]/', '', $item->im);
            $ie = preg_replace('/[^0-9]/', '', $item->ie);
			
			$cod_tributacao_ajustado = "";
			if($empresa->cidade->codigo == "3106200"){
				$cod_tributacao_ajustado_primeira_parte = $servico->codigo_servico;
				$cod_servico_ajustado_primeira_parte = $servico->codigo_tributacao_municipio;
				$cod_tributacao_ajustado_segunda_parte = $servico->codigo_servico;
				$cod_servico_ajustado_segunda_parte = $servico->codigo_tributacao_municipio;
			}
			else{
				$cod_tributacao_ajustado_primeira_parte = $servico->codigo_servico;
				$cod_servico_ajustado_primeira_parte = $servico->codigo_tributacao_municipio;
				$cod_tributacao_ajustado_segunda_parte = $servico->codigo_tributacao_municipio;
				$cod_servico_ajustado_segunda_parte = $servico->codigo_tributacao_municipio;
			}
			
			

            $payload = [
                "numero" => $novoNumeroNFse,
                "serie" => $empresa->numero_serie_nfse,
                "tipo" => "1",
                "status" => "1",
                "data_emissao" => date("Y-m-d\TH:i:sP"),
                "data_competencia" => date("Y-m-d\TH:i:sP"),
				"regime_tributacao" => $item->regime_tributacao,

                "tomador" => [
                    "cnpj" => strlen($doc) == 14 ? $doc : null,
                    "cpf" => strlen($doc) == 11 ? $doc : null,
                    "im" => $im ? $im : null,
                    "ie" => $ie ? $ie : null,
                    "razao_social" => $item->razao_social,
					"telefone" => isset($item->telefone) ? preg_replace('/[()\s-]/', '', $item->telefone) : '',
					"contato" => 'matriza',
					"email" => 'suporte@matriza.com.br',
					
                    "endereco" => [
                        "logradouro" => $this->retiraAcentos($item->rua),
                        "numero" => $this->retiraAcentos($item->numero),
                        "complemento" => $this->retiraAcentos($item->complemento),
                        "bairro" => $this->retiraAcentos($item->bairro),
                        "codigo_municipio" => $item->cidade->codigo,
						"nome_municipio" => $item->cidade->nome,
                        "uf" => $item->cidade->uf,
                        "cep" => preg_replace('/[^0-9]/', '', $item->cep)
                    ]
                ],
                "servico" => [
                    "codigo" => $cod_servico_ajustado_primeira_parte,
                    "codigo_tributacao_municipio" => $cod_tributacao_ajustado_primeira_parte,
					"regime_tributacao" => $item->regime_tributacao,
                    "discriminacao" => $this->retiraAcentos($servico->discriminacao),
					"codigo_cnae" =>  $servico->codigo_cnae,
                    "codigo_municipio" => $empresa->cidade->codigo,
                    "valor_servicos" => $servico->valor_servico,
                    "valor_pis" => $servico->aliquota_pis,
                    "valor_aliquota" => $servico->aliquota_iss,
                    "itens" => [
                        [
							"codigo" => $cod_servico_ajustado_segunda_parte,
							"codigo_tributacao_municipio" => $cod_tributacao_ajustado_segunda_parte,
							//"codigo_tributacao_municipio" => $servico->codigo_servico,
							"regime_tributacao" => $item->regime_tributacao,
							"discriminacao" => $this->retiraAcentos($servico->discriminacao),
							"codigo_cnae" =>  $servico->codigo_cnae,
							"codigo_municipio" => $empresa->cidade->codigo,
							"unidade_nome" => 'UND',
							"valor_servicos" => $servico->valor_servico,
							"valor_base_calculo" => $servico->valor_servico,
							"valor_pis" => $servico->aliquota_pis,
							"valor_aliquota" => $servico->aliquota_iss,
							"valor_iss" => $servico->aliquota_iss,
                        ]
                    ]
                ]
            ];

            $resp = $nfse->cria($payload);
            if($resp->sucesso == true){
                if(isset($resp->chave)){
                    $item->chave = $resp->chave;
                    $item->save();
                }
                // return response()->json($resp, 200);


                $chave = $resp->chave;
                sleep(15);
                $tentativa = 1;
                while ($tentativa <= 5) {
                    $payload = [
                        'chave' => $item->chave
                    ];
                    $resp = $nfse->consulta($payload);
                    if ($resp->codigo != 5023) {
                        if ($resp->sucesso) {
                    // autorizado

                            $item->estado = 'aprovado';
                            $item->url_pdf_nfse = $resp->link_pdf;
                            //$item->numero_nfse = $resp->numero;
							$item->numero_nfse = $novoNumeroNFse;
                            $item->codigo_verificacao = $resp->codigo_verificacao;

                            $item->save();

                            $empresa->numero_ultima_nfse = $novoNumeroNFse;
                            $empresa->save();
                            $xml = $resp->xml;
                            file_put_contents(public_path('xml_nota_servico/')."$item->chave.xml", $xml);
							if (isset($resp->pdf)) {
								$pdf_b64 = base64_decode($resp->pdf);
								file_put_contents(public_path('nfse_temp/') . "$item->chave.pdf", $pdf_b64);
							}
                            return response()->json($resp, 200);
                        } else {
                            return response()->json($resp, 200);
                        }
                    }
                    sleep(3);
                    $tentativa++;
                }

            }else{
                if($resp->codigo == 5008){
                    $item->chave = $resp->chave;
                    $item->save();
                }
                return response()->json($resp, 404);
            }

        }catch (\Exception $e) {
            return response()->json($e->getMessage(), 403);
        }
    }

    public function consultar(Request $request){
        $item = NotaServico::findOrFail($request->id);
        $empresa = $item->empresa;
        $params = [
            'token' => $empresa->token_nfse,
            'ambiente' => Nfse::AMBIENTE_PRODUCAO,
            // 'ambiente' => $empresa->ambiente == 2 ? Nfse::AMBIENTE_HOMOLOGACAO : Nfse::AMBIENTE_PRODUCAO,
            'options' => [
                'debug' => false,
                'timeout' => 60,
                'port' => 443,
                'http_version' => CURL_HTTP_VERSION_NONE
            ]
        ];
        try{

            $nfse = new Nfse($params);
            $payload = [
                'chave' => $item->chave
            ];
            $resp = $nfse->consulta($payload);
            if($resp->sucesso == true){
                if($resp->codigo == 100){
                    $item->estado = 'aprovado';
                    $item->url_pdf_nfse = $resp->link_pdf;
                    $item->numero_nfse = $resp->numero;
                    $item->codigo_verificacao = $resp->codigo_verificacao;

                    $item->save();

                    $empresa->numero_ultima_nfse = (int)$resp->numero;
                    $empresa->save();
                    $xml = $resp->xml;
                    file_put_contents(public_path('xml_nota_servico/')."$item->chave.xml", $xml);
					if (isset($resp->pdf)) {
						$pdf_b64 = base64_decode($resp->pdf);
						file_put_contents(public_path('nfse_temp/') . "$item->chave.pdf", $pdf_b64);
					}
                }
                return response()->json($resp, 200);
            }
            return response()->json($resp, 404);
        }catch (\Exception $e) {
            return response()->json($e->getMessage(), 403);
        }
    }

    public function cancelar(Request $request){
        $item = NotaServico::findOrFail($request->id);
        $empresa = $item->empresa;
        $params = [
            'token' => $empresa->token_nfse,
            'ambiente' => Nfse::AMBIENTE_PRODUCAO,
            // 'ambiente' => $empresa->ambiente == 2 ? Nfse::AMBIENTE_HOMOLOGACAO : Nfse::AMBIENTE_PRODUCAO,
            'options' => [
                'debug' => false,
                'timeout' => 60,
                'port' => 443,
                'http_version' => CURL_HTTP_VERSION_NONE
            ]
        ];
        try{
            $nfse = new Nfse($params);
            $payload = [
                'chave' => $item->chave,
                'motivo_cancelamento' => $request->motivo,
				'codigo_cancelamento' => $request->codigo
            ];
			
            $resp = $nfse->cancela($payload);
            if($resp->sucesso == true){
				
                if($resp->codigo == 101){
                    $item->estado = 'cancelado';
                    $item->save();

                }
                return response()->json($resp, 200);
            }

            return response()->json($resp, 404);
        }catch (\Exception $e) {
            return response()->json($e->getMessage(), 403);
        }
    }

    private function retiraAcentos($texto){
        return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/", "/(ç)/", "/(&)/"),explode(" ","a A e E i I o O u U n N c e"),$texto);
    }
}
