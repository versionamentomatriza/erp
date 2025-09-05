<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Nfe;
use App\Models\Caixa;
use App\Models\Inutilizacao;
use App\Services\NFeService;
use NFePHP\DA\NFe\Danfe;

class NFePainelController extends Controller
{
    public function __construct(){
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
    }

    public function emitir(Request $request){

        $nfe = Nfe::findOrFail($request->id);

        $empresa = Empresa::findOrFail($nfe->empresa_id);
        $empresa = __objetoParaEmissao($empresa, $nfe->local_id);

        if($empresa->arquivo == null){
            return response()->json("Certificado não encontrado para este emitente", 401);
        }

        $nfe_service = new NFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$nfe->ambiente,
            "razaosocial" => $empresa->nome,
            "siglaUF" => $empresa->cidade->uf,
            "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
        ], $empresa);

        $doc = $nfe_service->gerarXml($nfe);

        if(!isset($doc['erros_xml'])){
            $xml = $doc['xml'];
            $chave = $doc['chave'];

            try{
                $signed = $nfe_service->sign($xml);
                $resultado = $nfe_service->transmitir($signed, $doc['chave']);

                if ($resultado['erro'] == 0) {
                    $nfe->chave = $doc['chave'];
                    $nfe->estado = 'aprovado';

                    if($empresa->ambiente == 2){
                        $empresa->numero_ultima_nfe_homologacao = $doc['numero'];
                    }else{
                        $empresa->numero_ultima_nfe_producao = $doc['numero'];
                    }
                    $nfe->numero = $doc['numero'];
                    $nfe->recibo = $resultado['success'];
                    $nfe->data_emissao = date('Y-m-d H:i:s');
                    
                    // $nfe->ambiente = $empresa->ambiente
                    $nfe->save();
                    $empresa->save();
                    $data = [
                        'recibo' => $resultado['success'],
                        'chave' => $nfe->chave
                    ];
                    return response()->json($data, 200);
                }else{
                    $error = $resultado['error'];
                    $recibo = isset($resultado['recibo']) ? $resultado['recibo'] : null;

                    // return response()->json($resultado, 401);

                    if(isset($error['protNFe'])){
                        $motivo = $error['protNFe']['infProt']['xMotivo'];
                        $cStat = $error['protNFe']['infProt']['cStat'];
                        $nfe->motivo_rejeicao = substr("[$cStat] $motivo", 0, 200);
                    }

                    // $nfe->numero = isset($documento['numero_nfe']) ? $documento['numero_nfe'] : 
                    // Nfe::lastNumero($empresa);
                    if($nfe->chave == ''){
                        $nfe->chave = $doc['chave'];
                    }

                    if($nfe->signed_xml == null){
                        $nfe->signed_xml = $signed;
                    }
                    if($nfe->recibo == null){
                        $nfe->recibo = $recibo;
                    }
                    $nfe->estado = 'rejeitado';
                    $nfe->save();
                    
                    if(isset($error['protNFe'])){
                        return response()->json("[$cStat] $motivo", 403);
                    }else{
                        return response()->json($error, 403);
                    }
                }
            }catch(\Exception $e){
                return response()->json($e->getMessage(), 404);
            }

        }else{
            return response()->json($doc['erros_xml'], 401);
        }

    }

    public function cancelar(Request $request)
    {
        $nfe = Nfe::findOrFail($request->id);
        $empresa = Empresa::findOrFail($nfe->empresa_id);
        $empresa = __objetoParaEmissao($empresa, $nfe->local_id);

        if ($nfe != null) {
            $cnpj = preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj);
            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$nfe->ambiente,
                "razaosocial" => $empresa->nome,
                "siglaUF" => $empresa->cidade->uf,
                "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
            ], $empresa);
            $doc = $nfe_service->cancelar($nfe, $request->motivo);

            if (!isset($doc['erro'])) {
                $nfe->estado = 'cancelado';
                $nfe->save();
                // return response()->json($doc, 200);
                $motivo = $doc['retEvento']['infEvento']['xMotivo'];
                $cStat = $doc['retEvento']['infEvento']['cStat'];
                if($cStat == 135){
                    return response()->json("[$cStat] $motivo", 200);
                }else{
                    return response()->json("[$cStat] $motivo", 401);
                }
            } else {
                $arr = $doc['data'];
                $cStat = $arr['retEvento']['infEvento']['cStat'];
                $motivo = $arr['retEvento']['infEvento']['xMotivo'];
                
                return response()->json("[$cStat] $motivo", $doc['status']);
            }
        } else {
            return response()->json('Consulta não encontrada', 404);
        }
    }

    public function corrigir(Request $request)
    {
        $nfe = Nfe::findOrFail($request->id);
        $empresa = Empresa::findOrFail($nfe->empresa_id);
        $empresa = __objetoParaEmissao($empresa, $nfe->local_id);

        if ($nfe != null) {
            $cnpj = preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj);
            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$nfe->ambiente,
                "razaosocial" => $empresa->nome,
                "siglaUF" => $empresa->cidade->uf,
                "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
            ], $empresa);
            $doc = $nfe_service->correcao($nfe, $request->motivo);

            if (!isset($doc['erro'])) {

                // return response()->json($doc, 200);
                $motivo = $doc['retEvento']['infEvento']['xMotivo'];
                $cStat = $doc['retEvento']['infEvento']['cStat'];
                if($cStat == 135){
                    return response()->json("[$cStat] $motivo", 200);
                }else{
                    return response()->json("[$cStat] $motivo", 401);
                }
            } else {
                return response()->json($nfe['data'], $nfe['status']);
            }
        } else {
            return response()->json('Consulta não encontrada', 404);
        }
    }

    public function consultar(Request $request)
    {
        $nfe = Nfe::findOrFail($request->id);
        $empresa = Empresa::findOrFail($nfe->empresa_id);
        $empresa = __objetoParaEmissao($empresa, $nfe->local_id);

        if ($nfe != null) {
            $cnpj = preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj);
            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$nfe->ambiente,
                "razaosocial" => $empresa->nome,
                "siglaUF" => $empresa->cidade->uf,
                "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
            ], $empresa);
            $consulta = $nfe_service->consultar($nfe);

            if (!isset($consulta['erro'])) {
                try{
                    $motivo = $consulta['protNFe']['infProt']['xMotivo'];
                    $cStat = $consulta['protNFe']['infProt']['cStat'];
                    if($cStat == 100){
                        return response()->json("[$cStat] $motivo", 200);
                    }else{
                        return response()->json("[$cStat] $motivo", 401);
                    }
                }catch(\Exception $e){
                    return response()->json($consulta['cStat'] . " " . $consulta['xMotivo'], 404);
                }
            }else{
                return response()->json($consulta['data'], $consulta['status']);
            }
        } else {
            return response()->json('Consulta não encontrada', 404);
        }
    }

    public function inutilizar(Request $request)
    {
        $item = Inutilizacao::findOrFail($request->id);
        $empresa = Empresa::findOrFail($item->empresa_id);
        $empresa = __objetoParaEmissao($empresa, $item->local_id);
        
        if ($item != null) {
            $cnpj = preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj);
            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$empresa->ambiente,
                "razaosocial" => $empresa->nome,
                "siglaUF" => $empresa->cidade->uf,
                "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
            ], $empresa);
            $consulta = $nfe_service->inutilizar($item);
            if (!isset($consulta['erro'])) {
                $cStat = $consulta['infInut']['cStat'];
                if($cStat == 102){
                    $item->estado = 'aprovado';
                    $item->save();
                    return response()->json($consulta['infInut']['xMotivo'], 200);

                }else{
                    $item->estado = 'rejeitado';
                    $item->save();
                    return response()->json($consulta['infInut']['xMotivo'], 403);
                }

            }else{
                $item->estado = 'rejeitado';
                $item->save();
                return response()->json($consulta['data'], 403);
            }
        } else {
            return response()->json('Consulta não encontrada', 404);
        }
    }

    public function consultaStatusSefaz(Request $request){
        $caixa = Caixa::where('usuario_id', $request->usuario_id)->where('status', 1)->first();
        $empresa = Empresa::findOrFail($request->empresa_id);
        $empresa = __objetoParaEmissao($empresa, $caixa ? $caixa->local_id : null);
        
        $nfe_service = new NFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$empresa->ambiente,
            "razaosocial" => $empresa->nome,
            "siglaUF" => $empresa->cidade->uf,
            "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
        ], $empresa);
        $consulta = $nfe_service->consultaStatus((int)$empresa->ambiente, $empresa->cidade->uf);
        return response()->json($consulta, 200);
    }

}
