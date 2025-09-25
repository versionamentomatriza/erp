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
use App\Services\ExtratoService;
use App\Services\OfxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExtratoController extends Controller
{
    public function conciliar(Request $request)
    {
        $user = auth()->user();
        $empresaId = optional($user->empresa)->empresa_id ?? $user->empresa_id ?? null;

        // Dados fixos do usuário
        $fornecedores = Fornecedor::where('empresa_id', $empresaId)->get();
        $clientes = Cliente::where('empresa_id', $empresaId)->get();
        $extratos = Extrato::where('empresa_id', $empresaId)->get();
        $centrosCustos = CentroCusto::where('empresa_id', $empresaId)->get();
        $contasFinanceiras = ContaFinanceira::where('empresa_id', $empresaId)->get();
        $categoriasContas = CategoriaConta::where('empresa_id', $empresaId)
            ->orWhereNull('empresa_id')
            ->get();

        try {
            // Processamento de arquivos OFX
            if ($request->hasFile('arquivo_ofx')) {
                $destino = storage_path('app/ofx_salvos');

                if (!file_exists($destino)) {
                    mkdir($destino, 0777, true);
                }

                $arquivo = $request->file('arquivo_ofx');

                $todasTransacoes = [];
                $dadosExtrato = [];

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
                    ])->with('error', 'Nenhum extrato válido foi processado. Tente enviar novamente.');
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
                    $extrato->update([
                        'saldo_final' => $dadosExtrato['saldoFinal'] ?? $extrato->saldo_final,
                    ]);
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

                $contasFinanceirasEnvolvidas = $extrato->conciliacoes->map(function ($conciliacao) {
                    return $conciliacao->contaFinanceira;
                })->unique('id');

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
            dd($e->getMessage());
        }
    }


    public function movimentacao_bancaria(Request $request)
    {
        $user = auth()->user();
        $empresa = Empresa::find($user->empresa->empresa_id);
        $extrato = Extrato::find($request->query('extrato'));
        $movimentacao = ExtratoService::gerarDRE($extrato);
        $saldoConciliado = $extrato->calcularSaldoConciliado();
        $contasFinanceiras = $extrato->conciliacoes->map(function ($conciliacao) {
            return $conciliacao->contaFinanceira;
        })->unique('id');

        return view('extrato.movimentacao-bancaria', compact('empresa', 'extrato', 'movimentacao', 'saldoConciliado', 'contasFinanceiras'));
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
                    'extrato_id'        => $request->input('id_extrato'),
                    'conta_financeira_id'  => $request->input('id_conta_financeira'),
                    'transacao_id'      => $id_transacao,
                    'conciliavel_id'    => $request->input('id_conta'),
                    'conciliavel_tipo'  => $request->input('tipo_conta'),
                    'data_conciliacao'  => Extrato::findOrFail($request->input('id_extrato'))->inicio ?? now(),
                    'valor_conciliado'  => $conta->valor_pago ?? $conta->valor_recebido,
                ]);
            }

            return redirect()->to(url()->previous())
                ->with('success', 'Transação vinculada com sucesso.');
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    public function desvincular(Request $request)
    {
        $request->validate([
            'id_extrato'        => ['required'],
            'id_conta'          => ['required'],
            'tipo_conta'        => ['required'],
            'ids_transacoes'    => ['required'],
        ]);

        foreach ($request->input('ids_transacoes') as $id_transacao) {
            Conciliacao::where('conciliavel_id', $request->input('id_conta'))
                ->where('conciliavel_tipo', $request->input('tipo_conta'))
                ->where('transacao_id', $id_transacao)
                ->where('extrato_id', $request->input('id_extrato'))
                ->delete();
        }

        return redirect()->to(url()->previous())
            ->with('success', 'Transação desvinculada com sucesso.');
    }

    public function criar_conta(Request $request)
    {
        // Validação dos dados
        $validated = $request->validate([
            'descricao'             => 'required|string|max:255',
            'valor'                 => 'required|numeric|min:0',
            'data_vencimento'       => 'required|date',
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
            'valor_integral'     => (float) $validated['valor'],
            'data_vencimento'    => $validated['data_vencimento'],
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
            'data_conciliacao'      => Extrato::findOrFail($request->input('extrato_id'))->inicio ?? now(),
        ]);

        return redirect()->to(url()->previous())
            ->with('success', 'Conta criada com sucesso.');
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

        return redirect()->back()->with('success', 'Conta excluída com sucesso.');
    }

    public function ignorar_transacao(Request $request)
    {
        $request->validate([
            'extrato_id' => 'required|integer|exists:extratos,id',
            'transacao_id' => 'required|integer|exists:transacoes,id',
        ]);

        $et = ExtratoTransacao::where('extrato_id', $request->input('extrato_id'))
            ->where('transacao_id', $request->input('transacao_id'))
            ->firstOrFail();

        $et->delete();

        return redirect()->to(url()->previous())
            ->with('success', 'Transação ignorada com sucesso.');
    }

    public function finalizar(Request $request)
    {
        $user = auth()->user();
        $empresaId = optional($user->empresa)->empresa_id ?? $user->empresa_id ?? null;
        $centrosCustos = CentroCusto::where('empresa_id', $empresaId)->get();
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

        $extrato = Extrato::find($request->get('extrato'));
        $transacoes = $extrato->transacoes->filter(function ($transacao) {
            return $transacao->valor < $transacao->valorConciliado() || $transacao->valor > $transacao->valorConciliado();
        });

        if ($transacoes->count() > 0) {
            return view('extrato.finalizar', compact('extrato', 'transacoes', 'centrosCustos', 'categoriasPagar', 'categoriasReceber'));
        } else {
            $extrato->finalizar();

            return redirect()->to(url()->previous())
                ->with('success', 'Conciliação realizada com sucesso.');
        }
    }

    public function excedente(Request $request)
    {
        $request->validate([
            'extrato_id' => 'required|integer|exists:extratos,id',
            'empresa_id' => 'required|integer|exists:empresas,id',
            'transacoes' => 'required|array',
        ]);

        $extrato = Extrato::findOrFail($request->input('extrato_id'));

        foreach ($request->transacoes as $form) {
            // Se não foi marcada a opção de incluir, pula essa transação
            if (empty($form['incluir'])) {
                continue;
            }

            // Valida campos obrigatórios SOMENTE se "incluir" for true
            $validator = Validator::make($form, [
                'id' => 'required|integer|exists:transacoes,id',
                'categoria_id' => 'required|integer|exists:categoria_contas,id',
                'descricao' => 'nullable|string|max:255',
                'centro_custo_id' => 'nullable|integer|exists:centro_custos,id',
                'data_receber' => $form['tipo'] === 'receber' ? 'required|date' : 'nullable',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $transacao = Transacao::findOrFail($form['id']);
            $diferenca = $transacao->valorConciliado() - $transacao->valor;

            if ($diferenca > 0) {
                // EXCEDENTE → CONTA A PAGAR
                $dados = [
                    'descricao'          => $form['descricao'] ?? 'Excedente de conciliação',
                    'valor_integral'     => $diferenca,
                    'valor_pago'         => $diferenca,
                    'data_vencimento'    => $transacao->data,
                    'data_pagamento'     => $transacao->data,
                    'categoria_conta_id' => $form['categoria_id'],
                    'centro_custo_id'    => $form['centro_custo_id'] ?? null,
                    'empresa_id'         => $request->empresa_id,
                    'transacao_id'       => $transacao->id,
                ];

                $conta = ContaPagar::create($dados);

                Conciliacao::create([
                    'transacao_id'      => $transacao->id,
                    'extrato_id'        => $extrato->id,
                    'conciliavel_id'    => $conta->id,
                    'conciliavel_tipo'  => get_class($conta),
                    'valor_conciliado'  => abs($diferenca),
                    'data_conciliacao'  => Extrato::findOrFail($request->input('extrato_id'))->inicio ?? now(),
                ]);
            } elseif ($diferenca < 0) {
                // FALTANTE → CONTA A RECEBER
                $valorFaltante = abs($diferenca);

                $dados = [
                    'descricao'          => $form['descricao'] ?? 'Diferença de conciliação',
                    'valor_integral'     => $valorFaltante,
                    'valor_recebido'     => 0,
                    'data_vencimento'    => $form['data_receber'] ?? now()->toDateString(),
                    'categoria_conta_id' => $form['categoria_id'],
                    'centro_custo_id'    => $form['centro_custo_id'] ?? null,
                    'empresa_id'         => $request->empresa_id,
                    'transacao_id'       => $transacao->id,
                ];

                $conta = ContaReceber::create($dados);
            }
        }

        // Finaliza o extrato
        $extrato->finalizar();

        return redirect()->route('extrato.conciliar')
            ->with('success', 'Conciliação realizada com sucesso.');
    }
}
