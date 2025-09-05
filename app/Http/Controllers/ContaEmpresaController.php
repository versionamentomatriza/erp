<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContaEmpresa;
use App\Models\PlanoConta;
use App\Models\ItemContaEmpresa;

class ContaEmpresaController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:contas_empresa_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:contas_empresa_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:contas_empresa_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:contas_empresa_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request){
        $data = ContaEmpresa::
        where('empresa_id', $request->empresa_id)
        ->get();

        return view('conta_empresa.index', compact('data'));
    }

    public function create(Request $request){

        $countPlanos = PlanoConta::where('empresa_id', $request->empresa_id)->count();
        if($countPlanos == 0){
            session()->flash('flash_warning', 'Defina o plano de contas');
            return redirect()->route('plano-contas.index');
        }
        return view('conta_empresa.create');
    }

    public function edit(Request $request, $id){
        $item = ContaEmpresa::findOrFail($id);
        $countPlanos = PlanoConta::where('empresa_id', $request->empresa_id)->count();
        if($countPlanos == 0){
            session()->flash('flash_warning', 'Defina o plano de contas');
            return redirect()->route('plano-contas.index');
        }
        return view('conta_empresa.edit', compact('item'));
    }

    public function store(Request $request)
    {
        try {

            $request->merge([
                'saldo' => __convert_value_bd($request->saldo_inicial),
                'saldo_inicial' => __convert_value_bd($request->saldo_inicial),
            ]);
            ContaEmpresa::create($request->all());
            session()->flash("flash_success", "Conta criada com sucesso!");
            return redirect()->route('contas-empresa.index');
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
            return redirect()->back();

        }
    }

    public function destroy($id){
        $item = ContaEmpresa::findOrFail($id);
        $item->itens()->delete();
        $item->delete();
        session()->flash("flash_success", "Conta removida");
        return redirect()->back();
    }

    public function update(Request $request, $id){

        try{
            $item = ContaEmpresa::findOrFail($id);

            $request->merge([
                'saldo' => __convert_value_bd($request->saldo)
            ]);
            $item->fill($request->all())->save();
            session()->flash("flash_success", "Conta atualizada!");
            return redirect()->route('contas-empresa.index');

        }catch(\Exception $e){
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
            return redirect()->back();
        }
    }

    public function show(Request $request, $id){

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $tipo = $request->tipo;

        $item = ContaEmpresa::findOrFail($id);
        $data = ItemContaEmpresa::where('conta_id', $id)
        ->orderBy('id', 'desc')
        ->when($start_date, function ($q) use ($start_date) {
            return $q->whereDate('created_at', '>=', $start_date);
        })
        ->when($end_date, function ($q) use ($end_date) {
            return $q->whereDate('created_at', '<=', $end_date);
        })
        ->when($tipo, function ($q) use ($tipo) {
            return $q->where('tipo', $tipo);
        })
        ->paginate(50);

        return view('conta_empresa.show', compact('data', 'item'));
    }

}
