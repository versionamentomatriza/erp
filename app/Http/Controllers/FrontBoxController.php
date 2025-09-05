<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\CategoriaProduto;
use App\Models\Empresa;
use App\Models\Nfce;
use App\Models\ConfigGeral;
use App\Models\Produto;
use App\Models\User;
use App\Models\UsuarioEmpresa;
use App\Models\Funcionario;
use Illuminate\Http\Request;
use NFePHP\DA\NFe\CupomNaoFiscal;
use App\Utils\EstoqueUtil;

class FrontBoxController extends Controller
{
    /** 
     * Display a listing of the resource.
	 
     */
    public function __construct(EstoqueUtil $util)
    {
        $this->util = $util;
        $this->middleware('permission:pdv_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:pdv_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:pdv_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:pdv_delete', ['only' => ['destroy']]);
    }

    public function gerarFatura(Request $request)
    {
        try {
            $entrada_fatura = $request->entrada_fatura;
            $tipo_pagamento = $request->tipo_pagamento_fatura;
            $parcelas_fatura = (int) $request->parcelas_fatura;
            $intervalo_fatura = (int) $request->intervalo_fatura;
            $primeiro_vencimento_fatura = $request->primeiro_vencimento_fatura ?: date('Y-m-d');
            $total = __convert_value_bd($request->total);

            if ($total <= 0) {
                return response()->json(['error' => 'Total inválido'], 400);
            }

            $somaFatura = $total;
            $parcelas_fatura_original = $parcelas_fatura;

            if ($entrada_fatura) {
                $entrada_convertida = __convert_value_bd($entrada_fatura);
                $somaFatura -= $entrada_convertida;
                $parcelas_fatura--;
            } else {
                $entrada_convertida = 0;
            }

            if ($parcelas_fatura <= 0) {
                return response()->json(['error' => 'Número de parcelas inválido'], 400);
            }

            $valorParcela = round($somaFatura / $parcelas_fatura, 2);
            $somaLoop = 0;
            $data = [];

            for ($i = 0; $i < $parcelas_fatura_original; $i++) {
                if ($i == 0) {
                    $vencimento = $primeiro_vencimento_fatura;
                } else {
                    $vencimento = date('Y-m-d', strtotime($vencimento . " + $intervalo_fatura days"));
                }

                $p['vencimento'] = $vencimento;

                if ($i == 0 && $entrada_fatura) {
                    $p['valor'] = $entrada_convertida;
                    $somaLoop += $entrada_convertida;
                } else {
                    if ($i == $parcelas_fatura_original - 1) {
                        $p['valor'] = $total - $somaLoop;
                    } else {
                        $p['valor'] = $valorParcela;
                        $somaLoop += $valorParcela;
                    }
                }

                $data[] = $p;
            }

            return view('front_box.partials.row_fatura', compact('data', 'tipo_pagamento'))->render();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function index()
    {
        $data = Nfce::where('empresa_id', request()->empresa_id)
            ->orderBy('id', 'desc')->get();
        return view('front_box.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }
        $caixa = __isCaixaAberto();
        $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)->get();

        $abertura = Caixa::where('usuario_id', get_id_user())
            ->where('status', 1)
            ->first();

        $config = Empresa::findOrFail(request()->empresa_id);
        if ($config == null) {
            session()->flash("flash_warning", "Configure antes de continuar!");
            return redirect()->route('config.index');
        }

        if ($config->natureza_id_pdv == null) {
            session()->flash("flash_warning", "Configure a natureza de operação padrão para continuar!");
            return redirect()->route('config.index');
        }

        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();

        $config = ConfigGeral::where('empresa_id', request()->empresa_id)->first();
        $tiposPagamento = Nfce::tiposPagamento();
        // dd($tiposPagamento);
        if ($config != null) {
            $config->tipos_pagamento_pdv = $config != null && $config->tipos_pagamento_pdv ? json_decode($config->tipos_pagamento_pdv) : [];
            $temp = [];
            if (sizeof($config->tipos_pagamento_pdv) > 0) {
                foreach ($tiposPagamento as $key => $t) {
                    if (in_array($t, $config->tipos_pagamento_pdv)) {
                        $temp[$key] = $t;
                    }
                }
                $tiposPagamento = $temp;
            }
        }
        return view('front_box.create', compact('categorias', 'abertura', 'funcionarios', 'caixa', 'config', 'tiposPagamento'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Nfce::findOrFail($id);

        return view('front_box.show', compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $item = Nfce::with(['itens', 'cliente'])
            ->findOrFail($id);
        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }
        $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)->get();

        $abertura = Caixa::where('usuario_id', get_id_user())
            ->where('status', 1)
            ->first();

        $config = Empresa::findOrFail(request()->empresa_id);
        if ($config == null) {
            session()->flash("flash_warning", "Configure antes de continuar!");
            return redirect()->route('config.index');
        }

        if ($config->natureza_id_pdv == null) {
            session()->flash("flash_warning", "Configure a natureza de operação padrão para continuar!");
            return redirect()->route('config.index');
        }

        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
        $cliente = $item->cliente;
        $funcionario = $item->funcionario;
        $caixa = __isCaixaAberto();
        $tiposPagamento = Nfce::tiposPagamento();
        // dd($tiposPagamento);
        if ($config != null) {
            $config->tipos_pagamento_pdv = $config != null && $config->tipos_pagamento_pdv ? json_decode($config->tipos_pagamento_pdv) : [];
            $temp = [];
            if (sizeof($config->tipos_pagamento_pdv) > 0) {
                foreach ($tiposPagamento as $key => $t) {
                    if (in_array($t, $config->tipos_pagamento_pdv)) {
                        $temp[$key] = $t;
                    }
                }
                $tiposPagamento = $temp;
            }
        }

        return view('front_box.edit', compact('categorias', 'abertura', 'funcionarios', 'item', 'cliente', 'funcionario', 'caixa', 'tiposPagamento'));
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = Nfce::findOrFail($id);
        try {
            foreach ($item->itens as $i) {
                if ($i->produto->gerenciar_estoque) {
                    $this->util->incrementaEstoque($i->produto_id, $i->quantidade, $i->variacao_id, $item->local_id);
                }
            }
            $item->itens()->delete();
            $item->fatura()->delete();
            $item->contaReceber()->delete();
            $item->delete();
            session()->flash("flash_success", "Venda removida!");
        } catch (\Exception $e) {
            echo $e->getMessage() . '<br>' . $e->getLine();
            die;
            session()->flash("flash_error", 'Algo deu errado.', $e->getMessage());
        }
        return redirect()->route('frontbox.index');
    }

    public function imprimirNaoFiscal($id)
    {
        $item = Nfce::findOrFail($id);
        $config = Empresa::where('id', $item->empresa_id)
            ->first();

        $config = __objetoParaEmissao($config, $item->local_id);

        $usuario = UsuarioEmpresa::find(get_id_user());
        $cupom = new CupomNaoFiscal($item, $config);

        $pdf = $cupom->render();
        return response($pdf)
            ->header('Content-Type', 'application/pdf');
    }
}
