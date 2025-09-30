<?php

namespace App\Http\Controllers;

use App\Models\Funcionamento;
use App\Models\Servico;
use App\Models\User;
use App\Models\Nfce;
use App\Models\UsuarioEmpresa;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Agendamento;
use App\Models\ItemAgendamento;
use App\Models\CategoriaProduto;
use App\Models\Caixa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ConfigGeral;
use Dompdf\Dompdf;
use Dompdf\Options;

class AgendamentoController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:agendamento_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:agendamento_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:agendamento_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:agendamento_delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        // $funcionario = Funcionario::where('empresa_id', request()->empresa_id)->get();
        $servicos = Servico::where('empresa_id', request()->empresa_id)
        ->where('status', 1)
        ->get();

        $data = Agendamento::where('empresa_id', request()->empresa_id)
        ->orderBy('data', 'desc')->get();
        $agendamentos = [];

        foreach($data as $item){
            $a = [
                'title' => $item->cliente->razao_social,
                'start' => $item->data . " " . $item->inicio,
                'end' => $item->data . " " . $item->termino,
                'className' => $item->getPrioridade(),
                'id' => $item->id
            ];
            array_push($agendamentos, $a);
        }

        return view('agendamento.index', compact('agendamentos', 'servicos'));
    }

    public function store(Request $request){
        try {

            $nfe = DB::transaction(function () use ($request) {
                $dataAgendamento = [
                    'funcionario_id' => $request->funcionario,
                    'cliente_id' => $request->cliente_id,
                    'data' => $request->data,
                    'inicio' => $request->inicio,
                    'termino' => $request->termino,
                    'prioridade' => $request->prioridade,
                    'observacao' => $request->observacao ?? "",
                    'total' => __convert_value_bd($request->total),
                    'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0, 
                    'acrescimo' => 0, 
                    'empresa_id' => $request->empresa_id
                ];
            // dd($request->servicos);
                $agendamento = Agendamento::create($dataAgendamento);
            // dd($dataAgendamento);
                for($i=0; $i<sizeof($request->servicos); $i++){
                    $servico = Servico::findOrFail($request->servicos[$i]);
                    $dataItem = [
                        'agendamento_id' => $agendamento->id,
                        'servico_id' => $request->servicos[$i],
                        'quantidade' => 1,
                        'valor' => $servico->valor
                    ];
                    ItemAgendamento::create($dataItem);
                }
            });
            session()->flash("flash_success", "Agendamento cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado.', $e->getMessage());
        }
        return redirect()->back();
    }

    public function show($id){
        $item = Agendamento::findOrFail($id);

        return view('agendamento.show', compact('item'));
    }

    public function update(Request $request, $id){
        $item = Agendamento::findOrFail($id);
        $item->inicio = $request->inicio;
        $item->termino = $request->termino;
        $item->data = $request->data;
        $item->save();
        session()->flash("flash_success", "Agendamento alterado!");
        return redirect()->back();

    }

    public function updateStatus(Request $request, $id){
        $item = Agendamento::findOrFail($id);
        $item->status = 1;
        $item->save();
        session()->flash("flash_success", "Agendamento alterado!");
        return redirect()->route('agendamentos.index');

    }

    public function destroy($id)
    {
        $item = Agendamento::findOrFail($id);
        try {
            $item->itens()->delete();
            $item->delete();
            session()->flash("flash_success", "Agendamento removido!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu Errado: " . $e->getMessage());
        }
        return redirect()->route('agendamentos.index');
    }

    public function pdv($id){
        $agendamento = Agendamento::findOrFail($id);
        __validaObjetoEmpresa($agendamento);

        // if($item->status == 1){
        //     session()->flash("flash_warning", 'Pedido já esta finalizado');
        //     return redirect()->back();
        // }

        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }

        $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)->get();

        $abertura = Caixa::where('empresa_id', request()->empresa_id)->where('usuario_id', get_id_user())
        ->where('status', 1)
        ->first();

        $config = Empresa::findOrFail(request()->empresa_id);
        if($config == null){
            session()->flash("flash_warning", "Configure antes de continuar!");
            return redirect()->route('config.index');
        }

        if($config->natureza_id_pdv == null){
            session()->flash("flash_warning", "Configure a natureza de operação padrão para continuar!");
            return redirect()->route('config.index');
        }

        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
        $cliente = $agendamento->cliente;
        $funcionario = $agendamento->funcionario;
        $servicos = $agendamento->itens;
        $title = 'Finalizando agendamento #' . $agendamento->id;
        $caixa = __isCaixaAberto();

        $config = ConfigGeral::where('empresa_id', request()->empresa_id)->first();
        $tiposPagamento = Nfce::tiposPagamento();
        if($config != null){
            $config->tipos_pagamento_pdv = $config != null && $config->tipos_pagamento_pdv ? json_decode($config->tipos_pagamento_pdv) : [];
            $temp = [];
            if(sizeof($config->tipos_pagamento_pdv) > 0){
                foreach($tiposPagamento as $key => $t){
                    if(in_array($t, $config->tipos_pagamento_pdv)){
                        $temp[$key] = $t;
                    }
                }
                $tiposPagamento = $temp;
            }
        }

        return view('front_box.create', 
            compact('categorias', 'abertura', 'funcionarios', 'agendamento', 'servicos', 'title', 'cliente', 'funcionario', 
                'caixa', 'config', 'tiposPagamento'));

    }
	
	public function imprimir($id)
	{
		$item = Agendamento::with(['cliente', 'funcionario', 'itens.servico'])
			->findOrFail($id);

		$title = 'Relatório de Agendamento';

		return view('agendamento.imprimir', compact('item', 'title'));
	}
	
public function pdf($id)
{
    $item = Agendamento::with(['cliente', 'funcionario', 'itens.servico'])
        ->findOrFail($id);

    $options = new \Dompdf\Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isRemoteEnabled', true);

    $dompdf = new \Dompdf\Dompdf($options);

    $html = view('agendamento.pdf', compact('item'))->render();

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return $dompdf->stream("agendamento_{$id}.pdf", ["Attachment" => false]);
}

}
