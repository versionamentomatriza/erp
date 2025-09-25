<?php

namespace App\Http\Controllers;

use App\Models\ContaFinanceira;
use Illuminate\Http\Request;

class ContaFinanceiraController extends Controller
{
    public function index()
    {
        $contas = ContaFinanceira::where('empresa_id', auth()->user()->empresa->empresa->id)->get();
        return view('contas-financeiras.index', compact('contas'));
    }

    public function create(Request $request)
    {
        return view('contas-financeiras.create');
    }

    public function edit(Request $request, $id){
        $item = ContaFinanceira::findOrFail($id);
        return view('contas-financeiras.edit', compact('item'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nome' => 'required|string|max:255',
                'banco' => 'nullable|string|max:255',
                'agencia' => 'nullable|string|max:50',
                'conta' => 'nullable|string|max:50',
                'saldo_inicial' => 'required',
            ]);

            $request->merge([
                'saldo_final' => __convert_value_bd($request->saldo_inicial),
            ]);

            ContaFinanceira::create($request->all());
            session()->flash("flash_success", "Conta criada com sucesso!");
            return redirect()->route('contas-financeiras.index');
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function update(Request $request, $id){

        try{
            $item = ContaFinanceira::findOrFail($id);

            $request->merge([
                'saldo_inicial' => __convert_value_bd($request->saldo_inicial),
                'saldo_atual'   => __convert_value_bd($request->saldo_atual)
            ]);

            $item->fill($request->all())->save();
            session()->flash("flash_success", "Conta atualizada!");
            return redirect()->route('contas-financeiras.index');

        }catch(\Exception $e){
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
            return redirect()->back();
        }
    }

    public function destroy($id){
        $item = ContaFinanceira::findOrFail($id);
        $item->delete();
        session()->flash("flash_success", "Conta removida");
        return redirect()->back();
    }
}
