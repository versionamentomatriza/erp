<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\EmailVindiNFSe;
use Illuminate\Http\Request;
use CloudDfe\SdkPHP\Nfse;
use App\Models\Empresa;
use App\Models\ItemNotaServico;
use App\Models\NotaServico;
use App\Models\Plano;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use MercadoPago\Plan;

class NotaServicoController extends Controller
{
    public function emitirNotaVindi(Request $request)
    {
        Log::channel('requests')->info("NFSe requisitada com os parâmetros:  " . json_encode($request->all()));
        try {
             // --- IGNORE ---
            $nfse = new Nfse([
                "token" => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbXAiOjEwMjk3LCJ1c3IiOjMzMSwidHAiOjIsImlhdCI6MTc0MDYwMjI1OX0.ynAN6Bgn1OHbLCvYHs7zTHHkUgBfinKeheDTtvXqrOs',
                "ambiente" => 1, // IMPORTANTE: 1 - Produção / 2 - Homologação
                'options' => [
                    'debug' => false,
                    'timeout' => 60,
                    'port' => 443,
                    'http_version' => CURL_HTTP_VERSION_NONE
                ]
            ]);

            $emitente  = Empresa::find(14);
            $empresa   = Empresa::find($request->empresa_id);
            $plano     = Plano::find($request->plano_id);
            $doc       = preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj);
            $numero    = $emitente->numero_ultima_nfse + 1;

            Log::channel('requests')->info("NFSe a ser emitida para a empresa:  " . $empresa->nome);

            $payload = [
                "modelo" => "municipal",
                "numero" => $numero,
                "serie" => $emitente->numero_serie_nfse,
                "tipo" => "1",
                "status" => "1",
                "data_emissao" => date("Y-m-d\TH:i:sP"),
                "data_competencia" => date("Y-m-d\TH:i:sP"),
                "tomador" => [
                    "cnpj" => strlen($doc) == 14 ? $doc : null,
                    "cpf" => strlen($doc) == 11 ? $doc : null,
                    "im" => $empresa->inscricao_municipal,
                    "razao_social" => $empresa->nome,
                    "endereco" => [
                        "logradouro" => $empresa->rua,
                        "numero" => $empresa->numero,
                        "complemento" => $empresa->complemento,
                        "bairro" => $empresa->bairro,
                        "codigo_municipio" => $empresa->cidade->codigo,
                        "uf" => $empresa->cidade->uf,
                        "cep" => preg_replace('/\D/', '', $empresa->cep)
                    ]
                ],
                "servico" => [
                    "codigo_municipio" => $emitente->cidade->codigo,
                    "itens" => [
                        [
                            "codigo"                      => "107",
                            "codigo_tributacao_municipio" => "1.07",
                            "discriminacao"               => "Plano empresarial",
                            "valor_servicos"              => $plano->valor,
                            "valor_base_calculo"          => $plano->valor,
                            "valor_aliquota"              => "2.00",
                            "regime_tributacao"           => 0
                        ]
                    ]
                ]
            ];

            // Envia a NFSe para a API
            $resp = $nfse->cria($payload);

            Log::channel('requests')->info("Resposta NFSe: " . json_encode($resp));

            if ($resp->sucesso) {
                $emitente->numero_ultima_nfse = $numero;
                $emitente->save();
                // Ao entrar nesse bloco significa que a NFSe foi para o provedor e aguarda processamento.
                // Salva a chave no banco de dados para receber depois o resultado se a nota foi autorizada ou rejeitada
                // OBS: A chave é o identificador para consultas futuras da NFSe
                $chave = $resp->chave;
                sleep(15); // Aguarda 15 segundos para consultar a NFse, pois o processamento pode levar alguns segundos
                $payload = ["chave" => $chave];
                $resp = $nfse->consulta($payload);

                if ($resp->codigo != 5023) {
                    if ($resp->sucesso) {
                        // Aqui a NFSe foi autorizada
                        // Atualiza os dados da NFSe no banco de dados
                        $nfse = NotaServico::create([
                            'empresa_id' => $emitente->id,
                            'regime_tributacao' => $emitente->regime_tributacao ?? null,
                            'valor_total' => $plano->valor,
                            'ambiente' => 1,
                            'estado' => 'aprovado',
                            'serie' => $emitente->numero_serie_nfse,
                            'codigo_verificacao' => $resp->codigo_verificacao,
                            'numero_nfse' => $numero,
                            'chave' => $chave,
                            'url_xml' => '',
                            'url_pdf_nfse' =>  $resp->link_pdf ?? '',
                            'url_pdf_rps' => '',
                            'cliente_id' => $empresa->id,
                            'natureza_operacao' => '',
                            'documento' => $empresa->cpf_cnpj,
                            'razao_social' => $empresa->nome,
                            'im' => $empresa->im ?? '',
                            'ie' => $empresa->ie ?? '',
                            'cep' => $empresa->cep ?? '',
                            'rua' => $empresa->rua,
                            'numero' => $empresa->numero,
                            'bairro' => $empresa->bairro,
                            'complemento' => $empresa->complemento ?? '',
                            'cidade_id' => $empresa->cidade_id,
                            'email' => $empresa->email ?? '',
                            'gerar_conta_receber' => 0,
                            'telefone' => $empresa->telefone ?? ''
                        ]);

                        ItemNotaServico::create([
                            'nota_servico_id' => $nfse->id,
                            'discriminacao' => "Plano empresarial",
                            'valor_servico' => $plano->valor,
                            'servico_id' => 500,
                            'codigo_cnae' => '006203100',
                            'codigo_servico' => '107',
                            'codigo_tributacao_municipio' => '107',
                            'exigibilidade_iss' => 1,
                            'iss_retido' => 2,
                            'data_competencia' => now() ?? null,
                            'estado_local_prestacao_servico' => 'SC',
                            'cidade_local_prestacao_servico' => 'São José',
                            'valor_deducoes' => 0,
                            'desconto_incondicional' => 0,
                            'desconto_condicional' => 0,
                            'outras_retencoes' => 0,
                            'aliquota_iss' => 2.00,
                            'aliquota_pis' => 0.65,
                            'aliquota_cofins' => 3.00,
                            'aliquota_inss' => 1.00,
                            'aliquota_ir' => 0,
                            'aliquota_csll' => 0,
                            'intermediador' => 'n',
                            'documento_intermediador' => '',
                            'nome_intermediador' => '',
                            'im_intermediador' => '',
                            'responsavel_retencao_iss' => 1,

                        ]);

                        if (isset($resp->pdf)) {
                            $pdf_b64 = base64_decode($resp->pdf);
                            file_put_contents(public_path('nfse_temp/') . "$chave.pdf", $pdf_b64);
                        }

                        // Dados do email
                        $dadosEmail = [
                            'number' => $chave,
                            'name'   => $empresa->nome,
                            'link'   => $resp->link_nfse ?? null,
                        ];

                        // Envia
                        if (!empty($empresa->email)) {
                            Mail::to($empresa->email)->queue(new EmailVindiNFSe($dadosEmail));
                            Log::channel('requests')->info("NFSe enviada para a empresa:  " . $empresa->nome);
                        }

                        return response()->json($resp, 200);
                    } else return response()->json($resp, 400);
                } else return response()->json($resp, 400);
            } else if (in_array($resp->codigo, [5001, 5002])) {
                // Aqui o retorno indica que houve um erro na validação dos dados enviados
                // O código 5001 indica que falto campos obrigatórios ou opcionais obrigatórios referente ao emitente.
                // O código 5002 indica que houve um erro na validação dos dados como CNPJ, CPF, Inscrição Estadual, etc.
                return response()->json($resp, 400);
            } else {
                // Aqui é retornado qualquer erro que não seja relacionado a validação dos dados como não foi informado certificado digital, entre outros.
                return response()->json($resp, 400);
            }
        } catch (\Exception $e) {
            Log::channel('requests')->info("NFSe não emitida: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

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
				"regime_apuracao" => "1",
				"regime_tributacao" => $servico->regime_tributacao,

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
					"regime_tributacao" => $servico->regime_tributacao,
                    "discriminacao" => $this->retiraAcentos($servico->discriminacao),
					"codigo_cnae" =>  $servico->codigo_cnae,
                    "codigo_municipio" => $empresa->cidade->codigo,
                    "valor_servicos" => $servico->valor_servico,
                    "valor_pis" => $servico->aliquota_pis,
                    "valor_aliquota" => $servico->aliquota_iss,
					"tributos_totais" => [
						"percentual_tributos_federais" => 0,
						"valor_tributos_federais" => 0.00,
						"percentual_tributos_estaduais" => 0,
						"valor_tributos_estaduais" => 0.00,
						"percentual_tributos_municipais" => 0,
						"valor_tributos_municipais" => 0.00,
						"percentual_tributos_simples_nacional" => 0
					],
                    "itens" => [
                        [
							"codigo" => $cod_servico_ajustado_segunda_parte,
							"codigo_tributacao_municipio" => $cod_tributacao_ajustado_segunda_parte,
							//"codigo_tributacao_municipio" => $servico->codigo_servico,
							"regime_apuracao" => "1",
							"regime_tributacao" => $servico->regime_tributacao,
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
