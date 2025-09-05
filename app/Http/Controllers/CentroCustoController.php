<?php

namespace App\Http\Controllers;

use App\Models\Nfe;
use App\Models\CentroCusto;
use Illuminate\Http\Request;

class CentroCustoController extends Controller
{

    public function index(Request $request)
    {
        // Filtra registros por empresa e descrição
        $data = CentroCusto::where('empresa_id', $request->empresa_id)
            ->when(!empty($request->descricao), function ($query) use ($request) {
                return $query->where('descricao', 'LIKE', "%{$request->descricao}%");
            })
            ->paginate(env("PAGINACAO", 10)); // Paginação padrão ou 10 itens por página

        return view('centro_custo.index', compact('data'));
    }

    public function create()
    {
        return view('centro_custo.create'); // Renderiza a view de criação
    }

    public function store(Request $request)
    {
        try {
            // Criação de um novo Centro de Custo
            CentroCusto::create($request->all());
            session()->flash("flash_success", "Centro de custo criado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Erro ao criar centro de custo: " . $e->getMessage());
        }
        return redirect()->route('centro-custo.index');
    }

    public function edit($id)
    {
        // Busca o registro para edição
        $item = CentroCusto::findOrFail($id);
        return view('centro_custo.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = CentroCusto::findOrFail($id);

        try {
            // Atualiza os dados do registro
            $item->update($request->all());
            session()->flash("flash_success", "Centro de custo atualizado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Erro ao atualizar centro de custo: " . $e->getMessage());
        }
        return redirect()->route('centro-custo.index');
    }

    public function destroy($id)
    {
        $item = CentroCusto::findOrFail($id);

        try {
            // Remove o registro
            $item->delete();
            session()->flash("flash_success", "Centro de custo excluído com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Erro ao excluir centro de custo: " . $e->getMessage());
        }
        return redirect()->route('centro-custo.index');
    }
public function show($id)
{
    $centroCusto = CentroCusto::findOrFail($id);

    // Vendas (NFE Saída)
    $nfeRegistros = Nfe::where('centro_custo_id', $id)
        ->where('tpNF', 1)
        ->with(['cliente.cidade'])
        ->get()
        ->map(function ($nfe) {
            return [
                'id' => $nfe->id,
                'cliente' => $nfe->cliente->razao_social ?? 'Cliente não encontrado',
                'total' => $nfe->total,
                'data' => $nfe->created_at->format('d/m/Y'),
                'cidade' => $nfe->cliente->cidade->nome ?? 'Cidade não encontrada',
                'estado' => $nfe->estado ?? 'Estado não encontrado',
            ];
        });

    // Compras (NFE Entrada)
    $comprasRegistros = Nfe::where('centro_custo_id', $id)
        ->where('tpNF', 0)
        ->with(['fornecedor.cidade'])
        ->get()
        ->map(function ($compra) {
            return [
                'id' => $compra->id,
                'fornecedor' => $compra->fornecedor->razao_social ?? 'Fornecedor não encontrado',
                'total' => $compra->total,
                'data' => $compra->created_at->format('d/m/Y'),
                'cidade' => $compra->fornecedor->cidade->nome ?? 'Cidade não encontrada',
                'estado' => $compra->estado ?? 'Estado não encontrado',
            ];
        });

    // Contas a Pagar
    $contasPagar = \App\Models\ContaPagar::where('centro_custo_id', $id)
        ->with(['fornecedor'])
        ->get()
        ->map(function ($conta) {
            return [
                'id' => $conta->id,
                'fornecedor' => $conta->fornecedor->razao_social ?? 'Fornecedor não informado',
                'valor' => $conta->valor_integral,
                'data' => \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y'),
                'status' => $conta->status ? 'Pago' : 'Pendente',
            ];
        });

    // Contas a Receber
    $contasReceber = \App\Models\ContaReceber::where('centro_custo_id', $id)
        ->with(['cliente'])
        ->get()
        ->map(function ($conta) {
            return [
                'id' => $conta->id,
                'cliente' => $conta->cliente->razao_social ?? 'Cliente não informado',
                'valor' => $conta->valor_integral,
                'data' => \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y'),
                'status' => $conta->status ? 'Recebido' : 'Pendente',
            ];
        });

    // Totais
    $nfeTotal = $nfeRegistros->sum('total');
    $comprasTotal = $comprasRegistros->sum('total');
    $contasPagarTotal = $contasPagar->sum('valor');
    $contasReceberTotal = $contasReceber->sum('valor');

    return view('centro_custo.show', compact(
        'centroCusto',
        'nfeRegistros', 'nfeTotal',
        'comprasRegistros', 'comprasTotal',
        'contasPagar', 'contasPagarTotal',
        'contasReceber', 'contasReceberTotal'
    ));
}


}
