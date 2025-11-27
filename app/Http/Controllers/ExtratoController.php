<?php

namespace App\Http\Controllers;

use App\Models\CategoriaConta;
use App\Models\CentroCusto;
use App\Models\Cliente;
use App\Models\Conciliacao;
use App\Models\ContaFinanceira;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Empresa;
use App\Models\Extrato;
use App\Models\ExtratoTransacao;
use App\Models\Fornecedor;
use App\Models\Transacao;
use App\Models\TransferenciaConta;
use App\Services\ExtratoService;
use App\Services\OfxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ExtratoController extends Controller
{
    public function conciliar(Request $request)
    {
        $user               = auth()->user();
        $empresaId          = optional($user->empresa)->empresa_id ?? $user->empresa_id ?? null;
        $fornecedores       = Fornecedor::where('empresa_id', $empresaId)->get();
        $clientes           = Cliente::where('empresa_id', $empresaId)->get();
        $centrosCustos      = CentroCusto::where('empresa_id', $empresaId)->get();
        $contasFinanceiras  = ContaFinanceira::where('empresa_id', $empresaId)->get();
        $categoriasContas   = CategoriaConta::where('empresa_id', $empresaId)
            ->orWhereNull('empresa_id')
            ->get();
        $extratos           = Extrato::where('empresa_id', $empresaId)->when(
            $request->has('inicio'),
            function ($q) use ($request) {
                $dataInicio = \Carbon\Carbon::parse($request->input('inicio'))->startOfMonth()->toDateString();
                $dataFim = \Carbon\Carbon::parse($request->input('inicio'))->endOfMonth()->toDateString();
                return $q->whereBetween('inicio', [$dataInicio, $dataFim]);
            }
        )->orderByDesc('id')->take(6)->get();

        try {
            // Processamento de arquivos OFX
            if ($request->hasFile('arquivo_ofx')) {
                $destino = storage_path('app/ofx_salvos');

                if (!file_exists($destino)) mkdir($destino, 0777, true);

                $arquivo = $request->file('arquivo_ofx');
                $todasTransacoes = $dadosExtrato = [];

                if ($arquivo && $arquivo->isValid()) {
                    $nome = uniqid() . '_' . $arquivo->getClientOriginalName();
                    $caminho = $arquivo->move($destino, $nome)->getPathname();

                    if (file_exists($caminho)) {
                        $conteudo = @file_get_contents($caminho);

                        if ($conteudo) {
                            $resultado = OfxService::parse($conteudo);

                            if (is_array($resultado) && !empty($resultado)) {
                                $dadosExtrato = $resultado;
                                $todasTransacoes = $resultado['transacoes'] ?? [];
                            }
                        }
                    }
                }

                if (empty($dadosExtrato) || empty($todasTransacoes)) {
                    session()->flash("flash_error", "Nenhum extrato válido foi processado. Tente enviar novamente.");
                    return view('extrato.index', [
                        'fornecedores'      => $fornecedores,
                        'clientes'          => $clientes,
                        'contasFinanceiras' => $contasFinanceiras,
                        'contasPagar'       => collect(),
                        'contasReceber'     => collect(),
                        'centrosCustos'     => $centrosCustos,
                        'categoriasContas'  => $categoriasContas,
                        'extratos'          => $extratos,
                        'extrato'           => null,
                        'transacoes'        => collect(),
                    ]);
                }

                // 🔹 Data de referência (dataInicio do OFX ou da 1ª transação)
                $dataReferencia = !empty($dadosExtrato['dataInicio'])
                    ? \Carbon\Carbon::parse($dadosExtrato['dataInicio'])
                    : (isset($todasTransacoes[0]['data']) ? \Carbon\Carbon::parse($todasTransacoes[0]['data']) : now());

                $mes = $dataReferencia->month;
                $ano = $dataReferencia->year;

                // 🔹 Verifica se já existe extrato no mesmo mês/ano
                $extrato = Extrato::where('empresa_id', $empresaId)
                    ->whereMonth('inicio', $mes)
                    ->whereYear('inicio', $ano)
                    ->first();

                if ($extrato) {
                    // 🔹 Atualiza saldo final com o último saldo do OFX importado
                    $extrato->update(['status' => 'pendente', 'saldo_final' => $dadosExtrato['saldoFinal'] ?? $extrato->saldo_final]);
                } else {
                    $extrato = Extrato::create([
                        'banco'         => $dadosExtrato['transacoes'][0]['banco'] ?? null,
                        'inicio'        => $dataReferencia->copy()->startOfMonth()->toDateString(),
                        'fim'           => $dataReferencia->copy()->endOfMonth()->toDateString(),
                        'saldo_inicial' => $dadosExtrato['saldoInicial'] ?? 0,
                        'saldo_final'   => $dadosExtrato['saldoFinal'] ?? 0,
                        'empresa_id'    => $empresaId,
                    ]);

                    $extratos->push($extrato);
                }

                // 🔹 Cria ou vincula transações
                ExtratoService::criarTransacoes($todasTransacoes, $extrato->id);

                $contasPagar = buscarContasPorExtrato(ContaPagar::class, $empresaId, $extrato);
                $contasReceber = buscarContasPorExtrato(ContaReceber::class, $empresaId, $extrato);

                return redirect()->route('extrato.conciliar', ['extrato' => $extrato->id]);
            }

            // Selecionando extrato existente via GET
            if ($request->get('extrato')) {
                $extrato = Extrato::find($request->get('extrato'));

                $contasPagar = buscarContasPorExtrato(ContaPagar::class, $empresaId, $extrato);
                $contasReceber = buscarContasPorExtrato(ContaReceber::class, $empresaId, $extrato);

                if (
                    $request->filled('conta_pagar_fornecedor_id') ||
                    $request->filled('conta_pagar_descricao') ||
                    $request->filled('conta_pagar_data_inicio') ||
                    $request->filled('conta_pagar_data_fim')
                ) {
                    $query = ContaPagar::query();
                    $query->where('empresa_id', $empresaId);

                    if ($request->filled('conta_pagar_fornecedor_id')) $query->where('fornecedor_id', $request->conta_pagar_fornecedor_id);
                    if ($request->filled('conta_pagar_descricao')) $query->where('descricao', 'like', '%' . $request->conta_pagar_descricao . '%');
                    if ($request->filled('conta_pagar_data_inicio')) $query->whereDate('data_vencimento', '>=', $request->conta_pagar_data_inicio);
                    if ($request->filled('conta_pagar_data_fim')) $query->whereDate('data_vencimento', '<=', $request->conta_pagar_data_fim);

                    // Mescla resultados da pesquisa com contas já carregadas
                    $contasPagarPesquisa = $query->get();
                    $contasPagar = $contasPagarPesquisa->merge($contasPagar)->unique('id');
                }

                if (
                    $request->filled('conta_receber_cliente_id') ||
                    $request->filled('conta_receber_descricao') ||
                    $request->filled('conta_receber_data_inicio') ||
                    $request->filled('conta_receber_data_fim')
                ) {
                    $query = ContaReceber::query();
                    $query->where('empresa_id', $empresaId);

                    if ($request->filled('conta_receber_cliente_id')) $query->where('cliente_id', $request->conta_receber_cliente_id);
                    if ($request->filled('conta_receber_descricao')) $query->where('descricao', 'like', '%' . $request->conta_receber_descricao . '%');
                    if ($request->filled('conta_receber_data_inicio')) $query->whereDate('data_vencimento', '>=', $request->conta_receber_data_inicio);
                    if ($request->filled('conta_receber_data_fim')) $query->whereDate('data_vencimento', '<=', $request->conta_receber_data_fim);

                    // Mescla resultados da pesquisa com contas já carregadas
                    $contasReceberPesquisa = $query->get();
                    $contasReceber = $contasReceberPesquisa->merge($contasReceber)->unique('id');
                }

                $contasFinanceirasEnvolvidas = $extrato->contasFinanceirasEnvolvidas();

                return view('extrato.index', [
                    'fornecedores'                  => $fornecedores,
                    'clientes'                      => $clientes,
                    'contasFinanceiras'             => $contasFinanceiras,
                    'contasPagar'                   => $contasPagar,
                    'contasReceber'                 => $contasReceber,
                    'centrosCustos'                 => $centrosCustos,
                    'categoriasContas'              => $categoriasContas,
                    'extratos'                      => $extratos,
                    'extrato'                       => $extrato,
                    'transacoes'                    => $extrato->transacoes,
                    'contasFinanceirasEnvolvidas'   => $contasFinanceirasEnvolvidas,
                ]);
            }

            // Caso nenhum arquivo ou extrato seja fornecido
            return view('extrato.index', [
                'fornecedores'      => $fornecedores,
                'clientes'          => $clientes,
                'contasFinanceiras' => $contasFinanceiras,
                'contasPagar'       => collect(),
                'contasReceber'     => collect(),
                'centrosCustos'     => $centrosCustos,
                'categoriasContas'  => $categoriasContas,
                'extratos'          => $extratos,
                'extrato'           => null,
                'transacoes'        => collect(),
            ]);
        } catch (\Throwable $e) {
            session()->flash("flash_error", "Ocorreu um erro ao processar sua solicitação: " . $e->getMessage());
            return back();
        }
    }


    public function movimentacao_bancaria(Request $request)
    {
        $user               = auth()->user();
        $empresa            = Empresa::find($user->empresa->empresa_id);
        $extratoIds         = (array) $request->query('extrato_id');
        $extratos           = Extrato::whereIn('id', $extratoIds)->get();
        $movimentacao       = ExtratoService::gerarFluxoCaixa($extratos);
        $contasFinanceiras  = $extratos
            ->flatMap(fn($e) => $e->contasFinanceirasEnvolvidas())
            ->unique('id')
            ->values();

        return view('extrato.movimentacao-bancaria', compact('empresa', 'movimentacao', 'contasFinanceiras'));
    }

    public function dre(Request $request)
    {
        $user       = auth()->user();
        $empresa    = Empresa::find($user->empresa->empresa_id);
        $inicio     = $request->input('inicio');
        $fim        = $request->input('fim');
        $extratos   = null;
        $dre        = null;

        if ($inicio && $fim) {
            $inicioCarbon = Carbon::createFromFormat('Y-m', $inicio)->startOfMonth();
            $fimCarbon    = Carbon::createFromFormat('Y-m', $fim)->endOfMonth();

            $dre = ExtratoService::gerarDRE($empresa, $inicioCarbon->toDateString(), $fimCarbon->toDateString());
        }

        return view('extrato.dre', compact('empresa', 'dre'));
    }

    public function vincular(Request $request)
    {
        try {
            $request->validate([
                'id_extrato'            => ['required', 'integer'],
                'id_conta_financeira'   => ['required', 'integer'],
                'id_conta'              => ['required', 'integer'],
                'tipo_conta'            => ['required', 'string', 'in:App\Models\ContaPagar,App\Models\ContaReceber'],
                'ids_transacoes'        => ['required'],
                'valor_pago'            => ['nullable', 'numeric'],
                'data_pagamento'        => ['nullable', 'date'],
                'valor_recebido'        => ['nullable', 'numeric'],
                'data_recebimento'      => ['nullable', 'date'],
            ]);

            $model = $request->input('tipo_conta');
            $conta = $model::findOrFail($request->input('id_conta'));

            if (!$conta->status) {
                $dadosAtualizados = ['status' => 1];

                if ($conta instanceof ContaPagar) {
                    $dadosAtualizados['valor_pago']     = $request->input('valor_pago') ?? $conta->valor_pago;
                    $dadosAtualizados['data_pagamento'] = $request->input('data_pagamento') ?? $conta->data_pagamento;
                } elseif ($conta instanceof ContaReceber) {
                    $dadosAtualizados['valor_recebido']    = $request->input('valor_recebido') ?? $conta->valor_recebido;
                    $dadosAtualizados['data_recebimento']  = $request->input('data_recebimento') ?? $conta->data_recebimento;
                }

                $conta->update($dadosAtualizados);
            }

            foreach ($request->input('ids_transacoes') as $id_transacao) {
                Conciliacao::create([
                    'extrato_id'            => $request->input('id_extrato'),
                    'conta_financeira_id'   => $request->input('id_conta_financeira'),
                    'transacao_id'          => $id_transacao,
                    'conciliavel_id'        => $request->input('id_conta'),
                    'conciliavel_tipo'      => $request->input('tipo_conta'),
                    'data_conciliacao'      => now(),
                    'valor_conciliado'      => $conta->valor_pago ?? $conta->valor_recebido,
                ]);
            }

            session()->flash("flash_success", "Transação vinculada com sucesso.");
            return redirect()->to(url()->previous());
        } catch (\Throwable $e) {
            session()->flash("flash_error", "Erro ao vincular transação.");
            return redirect()->to(url()->previous());
        }
    }

    public function desvincular(Request $request)
    {
        $request->validate([
            'id_extrato'        => ['required', 'integer', 'exists:extratos,id'],
            'id_conta'          => ['required', 'integer',],
            'tipo_conta'        => ['required', 'string'],
            'ids_transacoes'    => ['required', 'array', 'min:1'],
            'manter_em_aberto'  => ['nullable', 'boolean'],
        ]);

        $manterEmAberto = $request->boolean('manter_em_aberto');
        if ($manterEmAberto) {
            $model = $request->input('tipo_conta');
            $conta = $model::findOrFail($request->input('id_conta'));
            $conta->status = 0;

            if ($conta instanceof ContaPagar) {
                $conta->valor_pago = null;
                $conta->data_pagamento = null;
            } elseif ($conta instanceof ContaReceber) {
                $conta->valor_recebido = null;
                $conta->data_recebimento = null;
            }

            $conta->save();
        }

        foreach ($request->input('ids_transacoes') as $id_transacao) {
            Conciliacao::where('conciliavel_id', $request->input('id_conta'))
                ->where('conciliavel_tipo', $request->input('tipo_conta'))
                ->where('transacao_id', $id_transacao)
                ->where('extrato_id', $request->input('id_extrato'))
                ->delete();
        }

        session()->flash("flash_success", "Transação desvinculada com sucesso.");
        return redirect()->to(url()->previous());
    }

    public function criar_conta(Request $request)
    {
        // Validação dos dados
        $validated = $request->validate([
            'descricao'             => 'required|string|max:255',
            'observacao'            => 'nullable|string',
            'valor'                 => 'required|numeric|min:0',
            'data_vencimento'       => 'required|date',
            'data_competencia'      => 'required|date',
            'centro_custo_id'       => 'nullable|integer',
            'fornecedor_id'         => 'nullable|integer',
            'cliente_id'            => 'nullable|integer',
            'categoria_conta_id'    => 'required|integer',
            'tipo'                  => 'required|in:DEBIT,CREDIT',
            'transacao_id'          => 'required|integer|exists:transacoes,id',
            'extrato_id'            => 'required|integer',
            'conta_financeira_id'   => 'required|integer',
        ]);

        $user = auth()->user();

        $dados = [
            'descricao'          => $validated['descricao'],
            'observacao'         => $validated['observacao'] ?? null,
            'valor_integral'     => (float) $validated['valor'],
            'data_vencimento'    => $validated['data_vencimento'],
            'data_competencia'   => $validated['data_competencia'],
            'empresa_id'         => optional($user->empresa)->empresa_id ?? $user->empresa_id ?? null,
            'centro_custo_id'    => $validated['centro_custo_id'] ?? null,
            'categoria_conta_id' => $validated['categoria_conta_id'],
            'status'             => 1,
        ];

        // Ajusta conforme o tipo de conta
        if ($validated['tipo'] === 'DEBIT') {
            $dados['fornecedor_id']  = $validated['fornecedor_id'] ?? null;
            $dados['data_pagamento'] = $dados['data_vencimento'];
            $dados['valor_pago']     = $dados['valor_integral'];

            $conta = ContaPagar::create($dados);
        } else {
            $dados['cliente_id']       = $validated['cliente_id'] ?? null;
            $dados['data_recebimento'] = $dados['data_vencimento'];
            $dados['valor_recebido']   = $dados['valor_integral'];
            $conta = ContaReceber::create($dados);
        }

        // Vincula à transação
        $transacao = Transacao::find($validated['transacao_id']);
        $valorConciliado = $conta->valor_pago ?? $conta->valor_recebido;

        Conciliacao::create([
            'transacao_id'          => $transacao->id,
            'extrato_id'            => (int) $request->input('extrato_id'),
            'conta_financeira_id'   => (int) $request->input('conta_financeira_id'),
            'conciliavel_id'        => $conta->id,
            'conciliavel_tipo'      => get_class($conta),
            'valor_conciliado'      => $valorConciliado,
            'data_conciliacao'      => now(),
        ]);

        session()->flash("flash_success", "Conta criada com sucesso.");
        return redirect()->to(url()->previous());
    }

    public function excluir_conta(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'tipo' => 'required|string|in:App\Models\ContaPagar,App\Models\ContaReceber',
        ]);

        $modelClass = $request->input('tipo');
        $conta = $modelClass::findOrFail($request->input('id'));

        // Verifica se a conta está vinculada a alguma transação
        $vinculos = Conciliacao::where('conciliavel_id', $conta->id)
            ->where('conciliavel_tipo', $request->input('tipo'))
            ->count();

        if ($vinculos > 0) {
            return redirect()->back()->with('error', 'Não é possível excluir esta conta, pois ela está vinculada a uma ou mais transações.');
        }

        // Se não houver vínculos, exclui a conta
        $conta->delete();

        session()->flash("flash_success", "Conta excluída com sucesso.");
        return redirect()->back();
    }

    public function transferir_conta(Request $request)
    {
        $request->validate([
            'id_extrato'            => ['required', 'integer'],
            'id_conta'              => ['required', 'integer'],
            'tipo_conta'            => ['required', 'string', 'in:App\Models\ContaPagar,App\Models\ContaReceber'],
            'id_conta_financeira'   => ['required', 'integer'],
        ]);

        $concliliacao = Conciliacao::where('conciliavel_id', $request->input('id_conta'))
            ->where('conciliavel_tipo', $request->input('tipo_conta'))
            ->where('extrato_id', $request->input('id_extrato'))
            ->firstOrFail();

        $concliliacao->update([
            'conta_financeira_id' => $request->input('id_conta_financeira'),
        ]);

        session()->flash("flash_success", "Movimentação realizada com sucesso!");
        return redirect()->to(url()->previous());
    }

    public function transferir_transacao(Request $request)
    {
        $validated = $request->validate([
            'empresa_id'        => 'required|integer|exists:empresas,id',
            'transacao_id'      => 'required|integer|exists:transacoes,id',
            'conta_origem_id'   => 'required|integer|exists:contas_financeiras,id|different:conta_destino_id',
            'conta_destino_id'  => 'required|integer|exists:contas_financeiras,id|different:conta_origem_id',
        ], [
            'empresa_id.required'           => 'A empresa é obrigatória.',
            'transacao_id.required'         => 'A transação é obrigatória.',
            'conta_origem_id.required'      => 'Selecione a conta de origem.',
            'conta_destino_id.required'     => 'Selecione a conta de destino.',
            'conta_origem_id.different'     => 'As contas de origem e destino devem ser diferentes.',
            'conta_destino_id.different'    => 'As contas de origem e destino devem ser diferentes.',
        ]);

        TransferenciaConta::create($validated);

        session()->flash("flash_success", "Movimentação realizada com sucesso!");
        return redirect()->to(url()->previous());
    }

    public function desfazer_transferencia_transacao(Request $request)
    {
        $request->validate(['transacao_id' => 'required|integer|exists:transacoes,id'], ['transacao_id.required' => 'A transação é obrigatória.']);

        $transacao = Transacao::findOrFail($request->input('transacao_id'));
        $ultimaTransferencia = $transacao->transferencias()->latest('created_at')->first();

        if (!$ultimaTransferencia) return redirect()->back()->with('flash_warning', 'Nenhuma movimentação encontrada para esta transação.');

        try {
            $ultimaTransferencia->delete();
            session()->flash('flash_success', 'Movimentação desfeita com sucesso.');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Erro ao desfazer movimentação: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    public function ignorar_transacao(Request $request)
    {
        $request->validate([
            'extrato_id'    => 'required|integer|exists:extratos,id',
            'transacao_id'  => 'required|integer|exists:transacoes,id',
        ]);

        $et = ExtratoTransacao::where('extrato_id', $request->input('extrato_id'))
            ->where('transacao_id', $request->input('transacao_id'))
            ->firstOrFail();

        $et->delete();

        session()->flash("flash_success", "Transação ignorada com sucesso.");
        return redirect()->to(url()->previous());
    }

    public function finalizar(Request $request)
    {
        $user                           = auth()->user();
        $empresaId                      = optional($user->empresa)->empresa_id ?? $user->empresa_id ?? null;
        $centrosCustos                  = CentroCusto::where('empresa_id', $empresaId)->get();
        $contasFinanceiras              = ContaFinanceira::where('empresa_id', $empresaId)->get();
        $extrato                        = Extrato::find($request->get('extrato'));
        $contasFinanceirasEnvolvidas    = $extrato->contasFinanceirasEnvolvidas();

        $categoriasPagar = CategoriaConta::where(function ($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId)->orWhereNull('empresa_id');
        })
            ->whereIn('tipo', ['despesa', 'custo'])
            ->get();

        $categoriasReceber = CategoriaConta::where(function ($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId)->orWhereNull('empresa_id');
        })
            ->where('tipo', 'receita')
            ->get();

        $transacoes = $extrato->transacoes->filter(function ($transacao) {
            if ($transacao->movimentada()) return false;
            return $transacao->valor < $transacao->valorConciliado() || $transacao->valor > $transacao->valorConciliado();
        });

        foreach ($contasFinanceirasEnvolvidas as $conta) {
            $conta->saldo_atual = $conta->calcularSaldoAtual();
            $conta->save();
        }

        if ($transacoes->count() > 0) {
            return view('extrato.finalizar', compact('extrato', 'transacoes', 'centrosCustos', 'categoriasPagar', 'categoriasReceber', 'contasFinanceiras', 'contasFinanceirasEnvolvidas'));
        } else {
            $extrato->finalizar();
            session()->flash("flash_success", "Conciliação realizada com sucesso.");
            return redirect()->to(url()->previous());
        }
    }

    public function excedente(Request $request)
    {
        $request->validate([
            'extrato_id'            => 'required|integer|exists:extratos,id',
            'empresa_id'            => 'required|integer|exists:empresas,id',
            'transacoes'            => 'nullable|array',
            'contas_divergentes'    => 'nullable|array',
        ]);

        if (!empty($request->input('contas_divergentes'))) {
            foreach ($request->input('contas_divergentes') as $cd) {
                $conta = ContaFinanceira::find($cd['id']);
                if ($conta) $conta->update(['saldo_atual' => $cd['novo_saldo'] ?? $conta->saldo_atual]);
            }
        }

        $extrato = Extrato::findOrFail($request->input('extrato_id'));

        if (!empty($request->input('transacoes'))) {
            foreach ($request->input('transacoes') as $form) {
                // Se não foi marcada a opção de incluir, pula essa transação
                if (empty($form['incluir'])) continue;

                // Valida campos obrigatórios SOMENTE se "incluir" for true
                $validator = Validator::make($form, [
                    'id'                    => 'required|integer|exists:transacoes,id',
                    'categoria_id'          => 'required|integer|exists:categoria_contas,id',
                    'conta_financeira_id'   => 'nullable|integer|exists:contas_financeiras,id',
                    'centro_custo_id'       => 'nullable|integer|exists:centro_custos,id',
                    'descricao'             => 'nullable|string|max:255',
                    'data_competencia'      => 'required|date',
                    'data_receber'          => $form['tipo'] === 'receber' ? 'required|date' : 'nullable',
                ]);

                if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();

                $transacao = Transacao::findOrFail($form['id']);
                $diferenca = $transacao->valorConciliado() - $transacao->valor;

                if ($diferenca > 0) {
                    // EXCEDENTE → CONTA A PAGAR
                    $dados = [
                        'descricao'          => $form['descricao'] ?? 'Excedente de conciliação',
                        'valor_integral'     => $diferenca,
                        'valor_pago'         => $diferenca,
                        'data_vencimento'    => $transacao->data,
                        'data_competencia'   => $form['data_competencia'] ?? now()->toDateString(),
                        'data_pagamento'     => $transacao->data,
                        'categoria_conta_id' => $form['categoria_id'],
                        'centro_custo_id'    => $form['centro_custo_id'] ?? null,
                        'empresa_id'         => $request->empresa_id,
                        'transacao_id'       => $transacao->id,
                    ];

                    $conta = ContaPagar::create($dados);

                    Conciliacao::create([
                        'transacao_id'          => $transacao->id,
                        'extrato_id'            => $extrato->id,
                        'conta_financeira_id'   => $form['conta_financeira_id'],
                        'conciliavel_id'        => $conta->id,
                        'conciliavel_tipo'      => get_class($conta),
                        'valor_conciliado'      => abs($diferenca),
                        'data_conciliacao'      => now(),
                    ]);
                } elseif ($diferenca < 0) {
                    // FALTANTE → CONTA A RECEBER
                    $valorFaltante = abs($diferenca);

                    $dados = [
                        'descricao'          => $form['descricao'] ?? 'Diferença de conciliação',
                        'valor_integral'     => $valorFaltante,
                        'valor_recebido'     => null,
                        'data_vencimento'    => $form['data_receber'] ?? now()->toDateString(),
                        'data_competencia'   => $form['data_competencia'] ?? now()->toDateString(),
                        'categoria_conta_id' => $form['categoria_id'],
                        'centro_custo_id'    => $form['centro_custo_id'] ?? null,
                        'empresa_id'         => $request->empresa_id,
                        'status'             => 0,
                    ];

                    $conta = ContaReceber::create($dados);
                }
            }
        }

        // Finaliza o extrato
        $extrato->finalizar();

        session()->flash("flash_success", "Conciliação realizada com sucesso.");
        return redirect()->route('extrato.conciliar');
    }
}
