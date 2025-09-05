<?php

namespace App\Http\Controllers;

use App\Models\Apontamento;
use App\Models\Empresa;
use App\Models\Estoque;
use App\Models\Produto;
use App\Utils\EstoqueUtil;
use Dompdf\Dompdf;
use Illuminate\Http\Request;

class ApontamentoController extends Controller
{
    protected $util;

    public function __construct(EstoqueUtil $util)
    {
        $this->util = $util;
    }


    public function create()
    {
        $data = Apontamento::orderBy('created_at', 'desc')->paginate(40);
        return view('estoque.apontamento', compact('data'));
    }

    public function store(Request $request)
    {
        try {
            $verificaMessage = $this->util->verificaEstoqueComposicao($request->produto_id, __convert_value_bd($request->quantidade));

            if ($verificaMessage != "") {
                session()->flash('flash_error', $verificaMessage);
                return redirect()->back();
            }
            $apontamento = Apontamento::create([
                'produto_id' => $request->produto_id,
                'quantidade' => __convert_value_bd($request->quantidade)
            ]);

            $this->util->reduzComposicao($request->produto_id, __convert_value_bd($request->quantidade));

            $tipo = 'incremento';
            $codigo_transacao = $apontamento->id;
            $tipo_transacao = 'alteracao_estoque';

            $this->util->movimentacaoProduto(
                $request->produto_id,
                $request->quantidade,
                $tipo,
                $codigo_transacao,
                $tipo_transacao,
                \Auth::user()->id
            );

            session()->flash('flash_success', 'Apontamento realizado com sucesso');
        } catch (\Exception $e) {
            echo $e->getMessage();
            die;
            session()->flash('flash_error', 'Algo deu errado:' . $e->getMessage());
        }
        return redirect()->route('estoque.index');
    }

    public function imprimir($id)
    {
        $item = Apontamento::findOrFail($id);

        $config = Empresa::where('id', request()->empresa_id)->first();

        $p = view('estoque.impressao_apontamento', compact('config', 'item'));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper("A4");
        $domPdf->render();
        $domPdf->stream("Apontamento de Produção $id.pdf", array("Attachment" => false));
    }
}
