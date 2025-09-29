<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\CategoriaProduto;
use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\ComissaoVenda;
use App\Models\CentroCusto;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Fornecedor;
use App\Models\ItemNfe;
use App\Models\ItemNfce;
use App\Models\Nfe;
use App\Models\Nfce;
use App\Models\Cte;
use App\Models\Mdfe;
use App\Models\Funcionario;
use App\Models\Marca;
use App\Models\Agendamento;
use App\Models\TaxaPagamento;
use App\Models\Localizacao;
use Dompdf\Dompdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BaseExport;
use App\Models\Caixa;
use Illuminate\Support\Carbon;

class RelatorioController extends Controller
{
    public function index()
    {
        $marcas = Marca::where('empresa_id', request()->empresa_id)->get();
        $estados = ['novo', 'rejeitado', 'cancelado', 'aprovado'];
        $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)->get();
        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
        $cidades = Localizacao::where('empresa_id', request()->empresa_id)->get();
        return view('relatorios.index', compact('funcionarios', 'estados', 'marcas', 'categorias', 'cidades'));
    }


    
    public function produtos(Request $request)
    {
        $locais = __getLocaisAtivoUsuario()->pluck(['id']);
        $estoque = $request->estoque;
        $tipo = $request->tipo;
        $marca_id = $request->marca_id;
        $categoria_id = $request->categoria_id;
        $local_id = $request->local_id;
    
        $data = Produto::select('produtos.*')
            ->where('empresa_id', $request->empresa_id)
            ->when($estoque != '', function ($query) use ($estoque) {
                if ($estoque == 1) {
                    return $query->join('estoques', 'estoques.produto_id', '=', 'produtos.id')
                        ->where('estoques.quantidade', '>', 0);
                } else {
                    return $query->leftJoin('estoques', 'estoques.produto_id', '=', 'produtos.id')
                        ->whereNull('estoques.produto_id')
                        ->orWhere(function ($q) {
                            return $q->join('estoques', 'estoques.produto_id', '=', 'produtos.id')
                                ->where('estoques.quantidade', '=', 0);
                        });
                }
            })
            ->when($categoria_id, fn($query) => $query->where('categoria_id', $categoria_id))
            ->when($marca_id, fn($query) => $query->where('marca_id', $marca_id))
            ->when($local_id, function ($query) use ($local_id) {
                return $query->join('produto_localizacaos', 'produto_localizacaos.produto_id', '=', 'produtos.id')
                    ->where('produto_localizacaos.localizacao_id', $local_id);
            })
            ->when(!$local_id, function ($query) use ($locais) {
                return $query->join('produto_localizacaos', 'produto_localizacaos.produto_id', '=', 'produtos.id')
                    ->whereIn('produto_localizacaos.localizacao_id', $locais);
            })
            ->get();
    
        if ($tipo != '') {
            foreach ($data as $item) {
                $sumNfe = ItemNfe::where('produto_id', $item->id)->sum('quantidade');
                $sumNfce = ItemNfce::where('produto_id', $item->id)->sum('quantidade');
                $item->quantidade_vendida = $sumNfe + $sumNfce;
            }
    
            $data = $tipo == 1 ? $data->sortByDesc('quantidade_vendida') : $data->sortBy('quantidade_vendida');
        }
    
        $marca = $marca_id ? Marca::findOrFail($marca_id) : null;
        $categoria = $categoria_id ? CategoriaProduto::findOrFail($categoria_id) : null;
    
        $exportar_excel = $request->get('export');

    // PDF
    if ($exportar_excel !== 'excel') {
        set_time_limit(60);
        ini_set('memory_limit', '512M');
        $view = view('relatorios.produtos', [
            'data' => $data,
            'tipo' => $tipo,
            'marca' => $marca,
            'categoria' => $categoria,
            'title' => 'Relatório de Produto',
        ]);
        

        $domPdf = new Dompdf(["enable_remote" => true]); 
        $domPdf->loadHtml($view);
        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        return $domPdf->stream("Relatório_de_Produto.pdf", ["Attachment" => false]);
    }

    // EXCEL
    return Excel::download(
        new BaseExport(['data' => $data, 'tipo' => $tipo], 'exports.produtos'),
        'produto.xlsx'
    );
    

}
    

public function clientes(Request $request)
{
    $start      = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
    $end        = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;
    $tipo       = $request->input('tipo');
    $empresaId  = $request->input('empresa_id');

    $data = Cliente::where('empresa_id', $empresaId)
            ->when($start && $end, fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->when($start && !$end, fn($q) => $q->where('created_at', '>=', $start))
            ->when(!$start && $end, fn($q) => $q->where('created_at', '<=', $end))
            ->get();

    if ($tipo !== '') {
        $ids = $data->pluck('id');

        $nfes = Nfe::selectRaw('cliente_id, SUM(total) as soma')
            ->whereIn('cliente_id', $ids)
            ->groupBy('cliente_id')
            ->pluck('soma', 'cliente_id');

        $nfces = Nfce::selectRaw('cliente_id, SUM(total) as soma')
            ->whereIn('cliente_id', $ids)
            ->groupBy('cliente_id')
            ->pluck('soma', 'cliente_id');

        foreach ($data as $item) {
            $sumNfe  = $nfes[$item->id] ?? 0;
            $sumNfce = $nfces[$item->id] ?? 0;
            $item->setAttribute('total', $sumNfe + $sumNfce);
        }

        $data = $tipo == 1
            ? $data->sortByDesc('total')->values()
            : $data->sortBy('total')->values();
    }

    $exportar_excel = $request->get('export');
    if ($exportar_excel === 'excel') {
                set_time_limit(60);
                ini_set('memory_limit', '512M');
        return Excel::download(
            new BaseExport(['data' => $data, 'tipo' => $tipo], 'exports.clientes'),
            'clientes.xlsx'
        );
    }

    $p = view('relatorios/clientes', compact('data', 'tipo'))
        ->with('title', 'Relatório de Clientes');

    $domPdf = new Dompdf(["enable_remote" => true]);
    $domPdf->loadHtml($p);
    $domPdf->setPaper("A4", "landscape");
    $domPdf->render();
    $domPdf->stream("Relatório de Clientes.pdf", ["Attachment" => false]);
}

public function fornecedores(Request $request)
{
    $start      = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
    $end        = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;
    $tipo       = $request->input('tipo');
    $empresaId  = $request->input('empresa_id');

    $data = Fornecedor::where('empresa_id', $empresaId)
            ->when($start && $end, fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->when($start && !$end, fn($q) => $q->where('created_at', '>=', $start))
            ->when(!$start && $end, fn($q) => $q->where('created_at', '<=', $end))
            ->get();

    if ($tipo != '') {
        foreach ($data as $item) {
            $sumNfe = Nfe::where('fornecedor_id', $item->id)
                ->where('tpNF', 0)
                ->sum('total');

            $item->total = $sumNfe;
        }
        $data = $tipo == 1 ? $data->sortByDesc('total') : $data->sortBy('total');
    }

    $exportar_excel = $request->get('export');
    if ($exportar_excel === 'excel') {
                set_time_limit(60);
                ini_set('memory_limit', '512M');
        return Excel::download(
            new BaseExport(['data' => $data, 'tipo' => $tipo], 'exports.fornecedores'),
            'fornecedores.xlsx'
        );
    }

    $p = view('relatorios/fornecedores', compact('data', 'tipo'))
        ->with('title', 'Relatório de Fornecedores');

    $domPdf = new Dompdf(["enable_remote" => true]);
    $domPdf->loadHtml($p);
    $domPdf->setPaper("A4", "landscape");
    $domPdf->render();
    $domPdf->stream("Relatório de Fornecedores.pdf", ["Attachment" => false]);
}


public function nfe(Request $request)
{
    $start      = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
    $end        = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;
    $empresaId  = $request->input('empresa_id');
    $locais     = __getLocaisAtivoUsuario()->pluck(['id']);

    $data = Nfe::where('empresa_id', $empresaId)
        ->when($start && $end, fn($q) => $q->whereBetween('created_at', [$start, $end]))
        ->when($start && !$end, fn($q) => $q->whereDate('data_emissao', '>=', $start))
        ->when(!$start && $end, fn($q) => $q->whereDate('data_emissao', '<=', $end))
        ->when(!empty($request->cliente), fn($q) => $q->where('cliente_id', $request->cliente))
        ->when(!empty($request->estado), fn($q) => $q->where('estado', $request->estado))
        ->when(!empty($request->tipo), fn($q) => $q->where('tpNF', $request->tipo))
        ->when($request->local_id, fn($q) => $q->where('local_id', $request->local_id))
        ->when(!$request->local_id, fn($q) => $q->whereIn('local_id', $locais))
        ->when(!empty($request->finNFe), fn($q) => $q->where('finNFe', $request->finNFe))
        ->when(!empty($request->natureza_operacao), fn($q) => $q->where('natureza_id', $request->natureza_operacao))
        ->get();

    $exportar_excel = $request->get('export');

    // PDF
    if ($exportar_excel !== 'excel') {
        set_time_limit(60);
        ini_set('memory_limit', '512M');
        $view = view('relatorios.nfe', compact('data'))
            ->with('title', 'Relatório de NFe');

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($view);
        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        return $domPdf->stream("Relatório_de_NFe.pdf", ["Attachment" => false]);
    }

    // EXCEL
    return Excel::download(
        new BaseExport(['data' => $data], 'exports.nfe'),
        'nfe.xlsx'
    );
}
    

    public function nfce(Request $request)
    {
        $locais = __getLocaisAtivoUsuario()->pluck('id');

        $start      = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $end        = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;
        $cliente_id = $request->cliente_id;
        $estado     = $request->estado;
        $local_id   = $request->local_id;
        $empresa_id = $request->empresa_id ?? auth()->user()->empresa_id; // Certifique-se que empresa_id está definido.

        $query = Nfce::where('empresa_id', $empresa_id);

        // Filtro por data de cadastro (created_at)
        if ($start && $end) {
            if ($start <= $end) {
                $query->whereBetween('created_at', [$start, $end]);
            }
        } elseif ($start) {
            $query->whereDate('created_at', '>=', $start);
        } elseif ($end) {
            $query->whereDate('created_at', '<=', $end);
        }

        // Outros filtros opcionais
        if (!empty($estado)) {
            $query->where('estado', $estado);
        }

        if (!empty($cliente_id)) {
            $query->where('cliente_id', $cliente_id);
        }

        if (!empty($local_id)) {
            $query->where('local_id', $local_id);
        } else {
            $query->whereIn('local_id', $locais);
        }

        $data = $query->get();
        $exportar_excel = $request->get('export');

        // PDF
        if ($exportar_excel !== 'excel') {
        set_time_limit(60);
        ini_set('memory_limit', '512M');
            $view = view('relatorios.nfce', compact('data'))
                ->with('title', 'Relatório de NFCe');
    
            $domPdf = new Dompdf(["enable_remote" => true]);
            $domPdf->loadHtml($view);
            $domPdf->setPaper("A4", "landscape");
            $domPdf->render();
            return $domPdf->stream("Relatório_de_NFCe.pdf", ["Attachment" => false]);
        } 
    
        // EXCEL
        return Excel::download(
            new BaseExport(['data' => $data], 'exports.nfce'),
            'nfce.xlsx'
        );
    }


    public function cte(Request $request)
    {

        $locais = __getLocaisAtivoUsuario();
        $locais = $locais->pluck(['id']);

        $start      = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $end        = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;
        $estado     = $request->estado;
        $local_id   = $request->local_id;
        $empresaId  = $request->input('empresa_id');

        $data = Cte::where('empresa_id', $request->empresa_id)
            ->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereBetween('created_at', [$start, $end]);
            })
            ->when($start && !$end, function ($query) use ($start) {
                return $query->whereDate('created_at', '>=', $start);
            })
            ->when(!$start && $end, function ($query) use ($end,) {
                return $query->whereDate('created_at', '<=', $end);
            })
            ->when(!empty($estado), function ($query) use ($estado) {
                return $query->where('estado', $estado);
            })
            ->when($local_id, function ($query) use ($local_id) {
                return $query->where('local_id', $local_id);
            })
            ->when(!$local_id, function ($query) use ($locais) {
                return $query->whereIn('local_id', $locais);
            })
            ->get();
            
            $exportar_excel = $request->get('export');

            // PDF
            if ($exportar_excel !== 'excel') {
                set_time_limit(60);
                ini_set('memory_limit', '512M');
                $view = view('relatorios.cte', compact('data'))
                    ->with('title', 'Relatório de CTe');
        
                $domPdf = new Dompdf(["enable_remote" => true]);
                $domPdf->loadHtml($view);
                $domPdf->setPaper("A4", "landscape");
                $domPdf->render();
                return $domPdf->stream("Relatório_de_CTe.pdf", ["Attachment" => false]);
            } 
        
            // EXCEL
            return Excel::download(
                new BaseExport(['data' => $data], 'exports.cte'),
                'cte.xlsx'
            );
        }


    public function mdfe(Request $request)
    {
        $locais     = __getLocaisAtivoUsuario();
        $locais     = $locais->pluck(['id']);
        $empresaId  = $request->input('empresa_id');
        $start      = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $end        = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;
        $estado     = $request->estado;
        $local_id   = $request->local_id;

        $data = Mdfe::where('empresa_id', $request->empresa_id)
            ->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereBetween('created_at', [$start, $end]);
            })
            ->when($start && !$end, function ($query) use ($start) {
                return $query->whereDate('created_at', '>=', $start);
            })
            ->when(!$start && $end, function ($query) use ($end,) {
                return $query->whereDate('created_at', '<=', $end);
            })
            ->when(!empty($estado), function ($query) use ($estado) {
                return $query->where('estado_emissao', $estado);
            })
            ->when($local_id, function ($query) use ($local_id) {
                return $query->where('local_id', $local_id);
            })
            ->when(!$local_id, function ($query) use ($locais) {
                return $query->whereIn('local_id', $locais);
            })
            ->get();


        $p = view('relatorios/mdfe', compact('data'))
            ->with('title', 'Relatório de MDFe');

            $exportar_excel = $request->get('export');

            // PDF
            if ($exportar_excel !== 'excel') {
                set_time_limit(60);
                ini_set('memory_limit', '512M');
                $view = view('relatorios.mdfe', compact('data'))
                    ->with('title', 'Relatório de MDFe');
        
                $domPdf = new Dompdf(["enable_remote" => true]);
                $domPdf->loadHtml($view);
                $domPdf->setPaper("A4", "landscape");
                $domPdf->render();
                return $domPdf->stream("Relatório_de_MDFe.pdf", ["Attachment" => false]);
            } 
        
            // EXCEL
            return Excel::download(
                new BaseExport(['data' => $data], 'exports.mdfe'),
                'mdfe.xlsx'
            );
        }

    public function conta_pagar(Request $request)
    {
        $locais         = __getLocaisAtivoUsuario();
        $locais         = $locais->pluck(['id']);
        $start          = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $end            = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;
        $empresaId      = $request->input('empresa_id');
        $status         = $request->status;
        $local_id       = $request->local_id;
        $centroCustoId  = $request->centro_custo_id;


        $data = ContaPagar::where('empresa_id', $empresaId)
            ->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereBetween('created_at', [$start, $end]);
            })
            ->when($start && !$end, function ($query) use ($start) {
                return $query->whereDate('created_at', '>=', $start);
            })
            ->when(!$start && $end, function ($query) use ($end,) {
                return $query->whereDate('created_at', '<=', $end);
            })
            ->when(!empty($status), function ($query) use ($status) {
                if ($status == -1) {
                    return $query->where('status', '!=', 1);
                } else {
                    return $query->where('status', $status);
                }
            })
            ->when($local_id, function ($query) use ($local_id) {
                return $query->where('local_id', $local_id);
            })
            ->when(!$local_id, function ($query) use ($locais) {
                return $query->whereIn('local_id', $locais);
            })
            ->when($centroCustoId, function ($query) use ($centroCustoId) {
                return $query->where('centro_custo_id', $centroCustoId);
            })
            ->get();

            $exportar_excel = $request->get('export');

            // PDF
            if ($exportar_excel !== 'excel') {
                set_time_limit(60);
                ini_set('memory_limit', '512M');
                $view = view('relatorios.conta_pagar', compact('data', 'request'))
                    ->with('title', 'Relatório de Conta a Pagar');
        
                $domPdf = new Dompdf(["enable_remote" => true]);
                $domPdf->loadHtml($view);
                $domPdf->setPaper("A4", "landscape");
                $domPdf->render();
                return $domPdf->stream("Relatório_de_Conta_a_Pagar.pdf", ["Attachment" => false]);
            } 
        
            // EXCEL
            return Excel::download(
                new BaseExport(['data' => $data], 'exports.conta_pagar '),
                'conta_pagar.xlsx'
            );
        }

    public function conta_receber(Request $request)
    {
        $locais         = __getLocaisAtivoUsuario();
        $locais         = $locais->pluck(['id']);
        $start          = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $end            = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;
        $empresaId      = $request->input('empresa_id');
        $status         = $request->status;
        $local_id       = $request->local_id;
        $centroCustoId  = $request->centro_custo_id;


        $data = ContaReceber::where('empresa_id', $empresaId)
            ->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereBetween('created_at', [$start, $end]);
            })
            ->when($start && !$end, function ($query) use ($start) {
                return $query->whereDate('created_at', '>=', $start);
            })
            ->when(!$start && $end, function ($query) use ($end,) {
                return $query->whereDate('created_at', '<=', $end);
            })
            ->when(!empty($status), function ($query) use ($status) {
                if ($status == -1) {
                    return $query->where('status', '!=', 1);
                } else {
                    return $query->where('status', $status);
                }
            })
            ->when($local_id, function ($query) use ($local_id) {
                return $query->where('local_id', $local_id);
            })
            ->when(!$local_id, function ($query) use ($locais) {
                return $query->whereIn('local_id', $locais);
            })
            ->when($centroCustoId, function ($query) use ($centroCustoId) {
                return $query->where('centro_custo_id', $centroCustoId);
            })
            ->get();

        $exportar_excel = $request->get('export');

        // PDF
        if ($exportar_excel !== 'excel') {
            set_time_limit(60);
            ini_set('memory_limit', '512M');
            $view = view('relatorios.conta_receber', compact('data', 'request'))
                ->with('title', 'Relatório de Conta a Receber');
    
            $domPdf = new Dompdf(["enable_remote" => true]);
            $domPdf->loadHtml($view);
            $domPdf->setPaper("A4", "landscape");
            $domPdf->render();
            return $domPdf->stream("Relatório_de_Conta_a_Receber.pdf", ["Attachment" => false]);
        } 
    
        // EXCEL
        return Excel::download(
            new BaseExport(['data' => $data], 'exports.conta_receber '),
            'conta_receber.xlsx'
        );
    }

    public function comissao(Request $request)
    {
        $start          = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $end            = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;
        $empresaId      = $request->input('empresa_id');
        $funcionario_id = $request->funcionario_id;

        $data = ComissaoVenda::where('empresa_id', $empresaId)
            ->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereBetween('created_at', [$start, $end]);
            })
            ->when($start && !$end, function ($query) use ($start) {
                return $query->whereDate('created_at', '>=', $start);
            })
            ->when(!$start && $end, function ($query) use ($end,) {
                return $query->whereDate('created_at', '<=', $end);
            })
            ->when(!empty($funcionario_id), function ($query) use ($funcionario_id) {
                return $query->where('funcionario_id', $funcionario_id);
            })
            ->get();

        $p = view('relatorios/comissao', compact('data'))
            ->with('title', 'Relatório de Comissao');

            $exportar_excel = $request->get('export');

            // PDF
            if ($exportar_excel !== 'excel') {
                set_time_limit(60);
                ini_set('memory_limit', '512M');
                $view = view('relatorios.comissao', compact('data'))
                    ->with('title', 'Relatório de Comissão');
        
                $domPdf = new Dompdf(["enable_remote" => true]);
                $domPdf->loadHtml($view);
                $domPdf->setPaper("A4", "landscape");
                $domPdf->render();
                return $domPdf->stream("Relatório_de_Comissão.pdf", ["Attachment" => false]);
            } 
        
            // EXCEL
            return Excel::download(
                new BaseExport(['data' => $data], 'exports.comissao '),
                'comissao.xlsx'
            );
        }

    public function vendas(Request $request)
    {
        $empresaId      = $request->empresa_id;
        $centroCustoId  = $request->centro_custo_id;
        $estado         = $request->estado;
        $cidade         = $request->cidade_id;
        $start          = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $end            = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;

        // Aplicando filtros corretamente
        $vendas = Nfe::where('empresa_id', $empresaId)
            ->where('tpNF', 1)
            ->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereBetween('created_at', [$start, $end]);
            })
            ->when($start && !$end, function ($query) use ($start) {
                return $query->whereDate('created_at', '>=', $start);
            })
            ->when(!$start && $end, function ($query) use ($end,) {
                return $query->whereDate('created_at', '<=', $end);
            })
            ->when($centroCustoId, function ($query) use ($centroCustoId) {
                return $query->where('centro_custo_id', $centroCustoId);
            }) // Filtro de Centro de Custo
            ->when($estado, function ($query) use ($estado) {
                return $query->where('estado', $estado);
            }) // Filtro de Estado
            ->when($cidade, function ($query) use ($cidade) {
                return $query->whereHas('cliente', function ($subquery) use ($cidade) {
                    $subquery->where('cidade_id', $cidade); // Certifique-se que o campo é 'cidade_id'
                });
            }) // Filtro de Cidade
            ->with(['cliente', 'centroCusto']) // Certifique-se que o relacionamento está carregado corretamente
            ->get();

        // Processa os dados antes de enviar para a view
        $data = $this->uneArrayVendas($vendas, collect());

        // Renderiza a view corretamente
        $p = view('relatorios.vendas', compact('data'))
            ->with('title', 'Relatório de Vendas');
            $exportar_excel = $request->get('export');

            // PDF
           if ($exportar_excel !== 'excel') {
                set_time_limit(60);
                ini_set('memory_limit', '512M');

                $html = view('relatorios.vendas', compact('data'))
                    ->with('title', 'Relatório de Vendas')
                    ->render(); // renderiza como HTML

                $domPdf = new Dompdf(["enable_remote" => true]);
                $domPdf->loadHtml($html);
                $domPdf->setPaper("A4", "landscape");
                $domPdf->render();

                return $domPdf->stream("Relatório_de_Vendas.pdf", ["Attachment" => false]);
            }

        
            // EXCEL
            return Excel::download(
                new BaseExport(['data' => $data], 'exports.venda '),
                'vendas.xlsx'
            );
        }

    private function uneArrayVendas($vendas, $vendasCaixa)
    {
        $arr = [];

        foreach ($vendas as $v) {
            $cidade = $v->cliente && $v->cliente->cidade ? $v->cliente->cidade->nome : '--';
            $cidade_id = $v->cliente ? $v->cliente->cidade_id : null;
            $estado = $v->estado;
            $centro_custo = $v->centroCusto ? $v->centroCusto->descricao : 'N/A'; // Adicionando Centro de Custo
            $temp = [
                'id' => $v->id,
                'data' => $v->created_at,
                'total' => $v->total,
                'cliente' => $v->cliente ? $v->cliente->info : '--',
                'cidade_id' => $cidade_id,
                'cidade' => $cidade,
                'estado' => $estado,
                'centro_custo' => $centro_custo // Adicionando Centro de Custo
            ];
            array_push($arr, $temp);
        }

        foreach ($vendasCaixa as $v) {
            $cidade = $v->cliente && $v->cliente->cidade ? $v->cliente->cidade->nome : '--';
            $cidade_id = $v->cliente ? $v->cliente->cidade_id : null;
            $estado = $v->estado;
            $centro_custo = $v->centroCusto ? $v->centroCusto->descricao : 'N/A'; // Adicionando Centro de Custo
            $temp = [
                'id' => $v->id,
                'data' => $v->created_at,
                'total' => $v->total,
                'cliente' => $v->cliente ? $v->cliente->info : '--',
                'cidade_id' => $cidade_id,
                'cidade' => $cidade,
                'estado' => $estado,
                'centro_custo' => $centro_custo // Adicionando Centro de Custo
            ];
            array_push($arr, $temp);
        }

        return $arr;
    }


    public function compras(Request $request)
    {
        $locais             = __getLocaisAtivoUsuario();
        $locais             = $locais->pluck('id');
        $empresaId          = $request->input('empresa_id');
        $start              = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $end                = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;
        $local_id           = $request->local_id;
        $centro_custo_id    = $request->centro_custo_id;

        $data = Nfe::where('empresa_id', $empresaId)
            ->where('tpNF', 0) // Apenas compras
            ->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereBetween('created_at', [$start, $end]);
            })
            ->when($start && !$end, function ($query) use ($start) {
                return $query->whereDate('created_at', '>=', $start);
            })
            ->when(!$start && $end, function ($query) use ($end,) {
                return $query->whereDate('created_at', '<=', $end);
            })
            ->when($local_id, function ($query) use ($local_id) {
                return $query->where('local_id', $local_id);
            })
            ->when(!$local_id, function ($query) use ($locais) {
                return $query->whereIn('local_id', $locais);
            })
            ->when($centro_custo_id, function ($query) use ($centro_custo_id) {
                return $query->where('centro_custo_id', $centro_custo_id);
            })
            ->with(['centroCusto', 'fornecedor', 'localizacao']) // Carregando o relacionamento
            ->get();

        $p = view('relatorios/compras', compact('data'))
            ->with('title', 'Relatório de Compras');
            $exportar_excel = $request->get('export');

            // PDF
            if ($exportar_excel !== 'excel') {
                   set_time_limit(60);
                   ini_set('memory_limit', '512M');
                $view = view('relatorios.compras', compact('data'))
                    ->with('title', 'Relatório de Compras');
        
                $domPdf = new Dompdf(["enable_remote" => true]);
                $domPdf->loadHtml($view);
                $domPdf->setPaper("A4", "landscape");
                $domPdf->render();
                return $domPdf->stream("Relatório_de_Compras.pdf", ["Attachment" => false]);
            } 
        
            // EXCEL
            return Excel::download(
                new BaseExport(['data' => $data], 'exports.compras '),
                'compras.xlsx'
            );
        }



    public function taxas(Request $request)
    {
        $start          = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $end            = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;
        $empresaId      = $request->input('empresa_id');
        $taxas          = TaxaPagamento::where('empresa_id', $empresaId)->get();
        $tipos          = $taxas->pluck('tipo_pagamento')->toArray();

        $vendas = Nfe::where('empresa_id', $empresaId)
            ->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereBetween('created_at', [$start, $end]);
            })
            ->when($start && !$end, function ($query) use ($start) {
                return $query->whereDate('created_at', '>=', $start);
            })
            ->when(!$start && $end, function ($query) use ($end,) {
                return $query->whereDate('created_at', '<=', $end);
            })
            ->get();

        $data = [];
        foreach ($vendas as $v) {
            $bandeira_cartao = $v->bandeira_cartao;
            if (sizeof($v->fatura) > 1) {
                foreach ($v->fatura as $ft) {
                    $fp = $ft->tipo_pagamento;
                    if (in_array($fp, $tipos)) {
                        $taxa = TaxaPagamento::where('empresa_id', $empresaId)
                            ->where('tipo_pagamento', $fp)
                            ->when($bandeira_cartao != '' && $bandeira_cartao != '99', function ($q) use ($bandeira_cartao) {
                                return $q->where('bandeira_cartao', $bandeira_cartao);
                            })
                            ->first();
                        if ($taxa != null) {
                            $item = [
                                'cliente' => $v->cliente ? ($v->cliente->razao_social . " " . $v->cliente->cpf_cnpj) :
                                    'Consumidor final',
                                'total' => $ft->valor,
                                'taxa_perc' => $taxa ? $taxa->taxa : 0,
                                'taxa' => $taxa ? ($ft->valor * ($taxa->taxa / 100)) : 0,
                                'data' => \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i'),
                                'tipo_pagamento' => Nfe::getTipo($fp),
                                'venda_id' => $v->id,
                                'tipo' => 'PEDIDO'
                            ];
                            array_push($data, $item);
                        }
                    }
                }
            } else {
                if (in_array($v->tipo_pagamento, $tipos)) {
                    $total = $v->valor_total - $v->desconto + $v->acrescimo;
                    $taxa = TaxaPagamento::where('empresa_id', $empresaId)
                        ->when($bandeira_cartao != '' && $bandeira_cartao != '99', function ($q) use ($bandeira_cartao) {
                            return $q->where('bandeira_cartao', $bandeira_cartao);
                        })
                        ->where('tipo_pagamento', $v->tipo_pagamento)->first();
                    if ($taxa != null) {
                        $item = [
                            'cliente' => $v->cliente->razao_social,
                            'total' => $v->total,
                            'taxa_perc' => $taxa->taxa,
                            'taxa' => $taxa ? ($total * ($taxa->taxa / 100)) : 0,
                            'data' => \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i'),
                            'tipo_pagamento' => Nfe::getTipo($v->tipo_pagamento),
                            'venda_id' => $v->id,
                            'tipo' => 'PEDIDO'
                        ];
                        array_push($data, $item);
                    } else {
                        echo $bandeira_cartao;
                        die;
                    }
                }
            }
        }

        $vendasCaixa = Nfce::where('empresa_id', $empresaId)
            ->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereBetween('created_at', [$start, $end]);
            })
            ->when($start && !$end, function ($query) use ($start) {
                return $query->whereDate('created_at', '>=', $start);
            })
            ->when(!$start && $end, function ($query) use ($end,) {
                return $query->whereDate('created_at', '<=', $end);
            })
            ->get();

        foreach ($vendasCaixa as $v) {
            $bandeira_cartao = $v->bandeira_cartao;
            if (sizeof($v->fatura) > 1) {
                foreach ($v->fatura as $ft) {
                    if (in_array($ft->tipo_pagamento, $tipos)) {
                        $taxa = TaxaPagamento::where('empresa_id', $empresaId)
                            ->when($bandeira_cartao != '' && $bandeira_cartao != '99', function ($q) use ($bandeira_cartao) {
                                return $q->where('bandeira_cartao', $bandeira_cartao);
                            })
                            ->where('tipo_pagamento', $ft->tipo_pagamento)->first();

                        if ($taxa != null) {
                            $item = [
                                'cliente' => $v->cliente ? ($v->cliente->razao_social . " " . $v->cliente->cpf_cnpj) :
                                    'Consumidor final',
                                'total' => $ft->valor,
                                'taxa_perc' => $taxa->taxa,
                                'taxa' => $taxa ? ($ft->valor * ($taxa->taxa / 100)) : 0,
                                'data' => \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i'),
                                'tipo_pagamento' => Nfe::getTipo($ft->tipo_pagamento),
                                'venda_id' => $v->id,
                                'tipo' => 'PDV'
                            ];
                            array_push($data, $item);
                        }
                    }
                }
            } else {
                if (in_array($v->tipo_pagamento, $tipos)) {
                    $taxa = TaxaPagamento::where('empresa_id', $empresaId)
                        ->when($bandeira_cartao != '' && $bandeira_cartao != '99', function ($q) use ($bandeira_cartao) {
                            return $q->where('bandeira_cartao', $bandeira_cartao);
                        })
                        ->where('tipo_pagamento', $v->tipo_pagamento)->first();

                    if ($taxa != null) {
                        $item = [
                            'cliente' => $v->cliente ? ($v->cliente->razao_social . " " . $v->cliente->cpf_cnpj) :
                                'Consumidor final',
                            'total' => $v->total,
                            'taxa_perc' => $taxa->taxa,
                            'taxa' => $taxa ? ($v->total * ($taxa->taxa / 100)) : 0,
                            'data' => \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i'),
                            'tipo_pagamento' => Nfe::getTipo($v->tipo_pagamento),
                            'venda_id' => $v->id,
                            'tipo' => 'PDV'
                        ];
                        array_push($data, $item);
                    }
                }
            }
        }

        $p = view('relatorios/taxas')
            ->with('data', $data)
            ->with('title', 'Taxas de Pagamento');

            $exportar_excel = $request->get('export');

            // PDF
            if ($exportar_excel !== 'excel') {
                set_time_limit(60);
                ini_set('memory_limit', '512M');
                $view = view('relatorios.taxas', compact('data'))
                    ->with('title', 'Relatório de Taxas');
        
                $domPdf = new Dompdf(["enable_remote" => true]);
                $domPdf->loadHtml($view);
                $domPdf->setPaper("A4", "landscape");
                $domPdf->render();
                return $domPdf->stream("Relatório_de_Taxas.pdf", ["Attachment" => false]);
            } 
        
            // EXCEL
            return Excel::download(
                new BaseExport(['data' => $data], 'exports.taxas '),
                'taxas.xlsx'
            );
        }

    public function lucro(Request $request)
    {

        $locais         = __getLocaisAtivoUsuario();
        $locais         = $locais->pluck(['id']);
        $start          = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $end            = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;
        $empresaId      = $request->input('empresa_id');
        $local_id       = $request->local_id;

        $nfe = Nfe::where('empresa_id', $empresaId)
             ->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereBetween('created_at', [$start, $end]);
            })
            ->when($start && !$end, function ($query) use ($start) {
                return $query->whereDate('created_at', '>=', $start);
            })
            ->when(!$start && $end, function ($query) use ($end,) {
                return $query->whereDate('created_at', '<=', $end);
            })
            ->when($local_id, function ($query) use ($local_id) {
                return $query->where('local_id', $local_id);
            })
            ->when(!$local_id, function ($query) use ($locais) {
                return $query->whereIn('local_id', $locais);
            })
            ->where('orcamento', 0)
            ->where('tpNF', 1)
            ->get();

        $nfce = Nfce::where('empresa_id', $empresaId)
             ->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereBetween('created_at', [$start, $end]);
            })
            ->when($start && !$end, function ($query) use ($start) {
                return $query->whereDate('created_at', '>=', $start);
            })
            ->when(!$start && $end, function ($query) use ($end,) {
                return $query->whereDate('created_at', '<=', $end);
            })
            ->when($local_id, function ($query) use ($local_id) {
                return $query->where('local_id', $local_id);
            })
            ->when(!$local_id, function ($query) use ($locais) {
                return $query->whereIn('local_id', $locais);
            })
            ->get();

        $data = [];

        foreach ($nfe as $n) {
            $item = [
                'cliente' => $n->cliente ? $n->cliente->info : 'CONSUMIDOR FINAL',
                'data' => __data_pt($n->created_at),
                'valor_venda' => $n->total,
                'valor_custo' => $this->calculaCusto($n->itens),
                'localizacao' => $n->localizacao
            ];
            array_push($data, $item);
        }

        foreach ($nfce as $n) {
            $item = [
                'cliente' => $n->cliente ? $n->cliente->info : 'CONSUMIDOR FINAL',
                'data' => __data_pt($n->created_at),
                'valor_venda' => $n->total,
                'valor_custo' => $this->calculaCusto($n->itens),
                'localizacao' => $n->localizacao
            ];
            array_push($data, $item);
        }

        usort($data, function ($a, $b) {
            return $a['data'] < $b['data'] ? 1 : -1;
        });

        $p = view('relatorios/lucro', compact('data'))
            ->with('title', 'Relatório de Lucros');
            
            $exportar_excel = $request->get('export');

            // PDF
            if ($exportar_excel !== 'excel') {
                set_time_limit(60);
                ini_set('memory_limit', '512M');
                $view = view('relatorios.lucro', compact('data'))
                    ->with('title', 'Relatório de Lucro');
        
                $domPdf = new Dompdf(["enable_remote" => true]);
                $domPdf->loadHtml($view);
                $domPdf->setPaper("A4", "landscape");
                $domPdf->render();
                return $domPdf->stream("Relatório_de_Lucro.pdf", ["Attachment" => false]);
            } 
        
            // EXCEL
            return Excel::download(
                new BaseExport(['data' => $data], 'exports.lucro '),
                'lucro.xlsx'
            );
        }

        private function calculaCusto($itens)
        {
            $total = 0;
            foreach ($itens as $item) {
                if ($item && $item->produto && $item->produto->valor_compra !== null) {
                    $total += $item->quantidade * $item->produto->valor_compra;
                }
            }
            return $total;
        }
        
    public function baixaProdutos(Request $request)
    {
        // Obter o ID do usuário logado
        $usuariosIds = Caixa::where('empresa_id', Auth::user()->empresa->empresa_id)
            ->distinct()
            ->pluck('usuario_id')
            ->toArray();

        // Dados de entrada: datas e tipo de ordenação
        $start  = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $end    = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;
        $tipo   = $request->input('tipo');

        // Filtrar produtos com movimentações de redução (baixa) no período selecionado e associadas aos usuarios que movimentaram um caixa xa da empresa
        $produtos = Produto::whereHas('movimentacoes', function ($query) use ($start, $end, $usuariosIds) {
            $query->whereBetween('created_at', [$start, $end])
                ->where('tipo', 'reducao') // Filtra apenas reduções (baixas)
                ->whereIn('user_id', $usuariosIds); // Filtra pela movimentação dos usuários da empresa
        })
            ->with(['movimentacoes' => function ($query) use ($start, $end, $usuariosIds) {
                $query->whereBetween('created_at', [$start, $end])
                    ->where('tipo', 'reducao') // Movimentações de redução (baixa)
                    ->whereIn('user_id', $usuariosIds); // Movimentações
            }])
            ->get();

        // Calcular total baixado e o percentual para cada produto
        $totalBaixado = $produtos->sum(function ($produto) {
            return $produto->movimentacoes->sum('quantidade');
        });

        // Ordenar produtos por quantidade baixada
        if ($tipo == '1') {
            $produtos = $produtos->sortByDesc(function ($produto) {
                return $produto->movimentacoes->sum('quantidade');
            });
        } elseif ($tipo == '-1') {
            $produtos = $produtos->sortBy(function ($produto) {
                return $produto->movimentacoes->sum('quantidade');
            });
        }
            
        // Exibir a view com os dados calculados
        $p = view('relatorios.baixa_produtos', compact('produtos', 'totalBaixado', 'start_date', 'end_date', 'tipo'))
            ->with('title', 'Relatório de Baixa de Produtos'); // Passando o título

            $exportar_excel = $request->get('export');

            // PDF
            
            if ($exportar_excel !== 'excel') {
                set_time_limit(60);
                ini_set('memory_limit', '512M');
                $view = view('relatorios.baixa_produtos', compact('produtos', 'totalBaixado', 'start_date', 'end_date', 'tipo'))
                    ->with('title', 'Relatório de Saída de Produtos');
        
                $domPdf = new Dompdf(["enable_remote" => true]);
                $domPdf->loadHtml($view);
                $domPdf->setPaper("A4", "landscape");
                $domPdf->render();
                return $domPdf->stream("Relatório_Saída_de_Produtos.pdf", ["Attachment" => false]);
            } 
        
            // EXCEL
            return Excel::download(
                new BaseExport([
                    'data' => $produtos,
                    'totalBaixado' => $totalBaixado,
                ], 'exports.saida_produtos'),
                'saida_produtos.xlsx'
            );
        }

        public function agendamentos(Request $request)
        {
            $start  = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
            $end    = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;
        
            $agendamentos = Agendamento::whereBetween('data', [$start, $end])
                ->with(['funcionario', 'cliente', 'itens.servico'])
                ->get();
        
            $exportar_excel = $request->get('export');
        
            if ($exportar_excel !== 'excel') {
                set_time_limit(60);
                ini_set('memory_limit', '512M');
                $view = view('relatorios.agendamentos', compact('agendamentos', 'start_date', 'end_date'))
                    ->with('title', 'Relatório de Agendamentos');
        
                $domPdf = new Dompdf(["enable_remote" => true]);
                $domPdf->loadHtml($view);
                $domPdf->setPaper("A4", "landscape");
                $domPdf->render();
                return $domPdf->stream("Relatório_de_Agendamentos.pdf", ["Attachment" => false]);
            }
        
            return Excel::download(
                new BaseExport(['data' => $agendamentos], 'exports.agendamentos'),
                'agendamentos.xlsx'
            );
        }
        
}
