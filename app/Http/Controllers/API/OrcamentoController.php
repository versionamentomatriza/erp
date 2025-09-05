<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\ConfigGeral;

class OrcamentoController extends Controller
{
    public function validaDesconto(Request $request){
        $item = Produto::findOrFail($request->produto_id);
        $valor = $request->valor;

        $config = ConfigGeral::where('empresa_id', $request->empresa_id)->first();
        if($config != null && $config->percentual_desconto_orcamento > 0){
            $v = $item->valor_unitario - $item->valor_compra;
            $valorMinimo = $item->valor_unitario - ($v*($config->percentual_desconto_orcamento/100));

            if($valor < $valorMinimo){
                return response()->json($valorMinimo, 401);
            }
            return response()->json("ok", 200);
        }
        return response()->json("ok", 200);
    }

}
