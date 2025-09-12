<?php

namespace App\Http\Controllers;

use App\Models\CategoriaConta;
use App\Models\CentroCusto;
use App\Models\Conciliacao;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Empresa;
use App\Models\Extrato;
use App\Models\Transacao;
use App\Services\ExtratoService;
use App\Services\OfxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ExtratoController extends Controller
{
    public function conciliar(Request $request)
    {
        $user = auth()->user();
        $empresaId = optional($user->empresa)->empresa_id ?? $user->empresa_id ?? null;

        // Dados fixos do usuário
        $extratos = Extrato::where('empresa_id', $empresaId)->get();
        $centrosCustos = CentroCusto::where('empresa_id', $empresaId)->get();
        $categoriasContas = CategoriaConta::where('empresa_id', $empresaId)
            ->orWhereNull('empresa_id')
            ->get();

        try {
            // Processamento de arquivos OFX
            if ($request->hasFile('arquivos_ofx')) {
                $paths = [];
                $destino = storage_path('app/ofx_salvos');

                if (!file_exists($destino)) {
                    mkdir($destino, 0777, true);
                }

                foreach ($request->file('arquivos_ofx') as $arquivo) {
                    $nome = uniqid() . '_' . $arquivo->getClientOriginalName();
                    $paths[] = $arquivo->move($destino, $nome)->getPathname();
                }

                $todasTransacoes = [];
                $dadosExtrato = [];

                foreach ($paths as $path) {
                    if (!file_exists($path)) continue;

                    $conteudo = @file_get_contents($path);
                    if (!$conteudo) continue;

                    $resultado = OfxService::parse($conteudo);
                    if (!is_array($resultado) || empty($resultado)) continue;

                    $dadosExtrato[] = $resultado;
                    $todasTransacoes = array_merge($todasTransacoes, $resultado['transacoes'] ?? []);
                }

                if (empty($dadosExtrato)) {
                    return view('extrato.index', [
                        'contasPagar'      => collect(),
                        'contasReceber'    => collect(),
                        'centrosCustos'    => $centrosCustos,
                        'categoriasContas' => $categoriasContas,
                        'extratos'         => $extratos,
                        'extrato'          => null,
                        'transacoes'       => collect(),
                    ])->with('error', 'Nenhum extrato válido foi processado. Tente enviar novamente.');
                }

                $extrato = Extrato::firstOrCreate([
                    'banco'       => $dadosExtrato[0]['transacoes'][0]['banco'] ?? null,
                    'inicio'      => collect($dadosExtrato)->min('dataInicio'),
                    'fim'         => collect($dadosExtrato)->max('dataFim'),
                    'saldo_final' => collect($dadosExtrato)->sortByDesc('dataFim')->first()['saldoFinal'] ?? 0,
                    'empresa_id'  => $empresaId,
                ]);

                ExtratoService::criarTransacoes($todasTransacoes, $extrato->id);

                $contasPagar = ContaPagar::where('empresa_id', $empresaId)
                    ->whereBetween('data_vencimento', [$extrato->inicio, $extrato->fim])
                    ->orderBy('id', 'desc')
                    ->get();

                $contasReceber = ContaReceber::where('empresa_id', $empresaId)
                    ->whereBetween('data_vencimento', [$extrato->inicio, $extrato->fim])
                    ->orderBy('id', 'desc')
                    ->get();

                return view('extrato.index', [
                    'contasPagar'      => $contasPagar,
                    'contasReceber'    => $contasReceber,
                    'centrosCustos'    => $centrosCustos,
                    'categoriasContas' => $categoriasContas,
                    'extratos'         => $extratos,
                    'extrato'          => $extrato,
                    'transacoes'       => $extrato->transacoes,
                ]);
            }

            // Selecionando extrato existente via GET
            if ($request->get('extrato')) {
                $extrato = Extrato::find($request->get('extrato'));

                $contasPagar = ContaPagar::where('empresa_id', $empresaId)
                    ->whereBetween('data_vencimento', [$extrato->inicio, $extrato->fim])
                    ->orderBy('id', 'desc')
                    ->get();

                $contasReceber = ContaReceber::where('empresa_id', $empresaId)
                    ->whereBetween('data_vencimento', [$extrato->inicio, $extrato->fim])
                    ->orderBy('id', 'desc')
                    ->get();

                return view('extrato.index', [
                    'contasPagar'      => $contasPagar,
                    'contasReceber'    => $contasReceber,
                    'centrosCustos'    => $centrosCustos,
                    'categoriasContas' => $categoriasContas,
                    'extratos'         => $extratos,
                    'extrato'          => $extrato,
                    'transacoes'       => $extrato->transacoes,
                ]);
            }

            // Caso nenhum arquivo ou extrato seja fornecido
            return view('extrato.index', [
                'contasPagar'      => collect(),
                'contasReceber'    => collect(),
                'centrosCustos'    => $centrosCustos,
                'categoriasContas' => $categoriasContas,
                'extratos'         => $extratos,
                'extrato'          => null,
                'transacoes'       => collect(),
            ]);
        } catch (\Throwable $e) {
            Log::channel('ofx')->error('Erro ao processar OFX: ' . $e->getMessage());

            return view('extrato.index', [
                'contasPagar'      => collect(),
                'contasReceber'    => collect(),
                'centrosCustos'    => $centrosCustos,
                'categoriasContas' => $categoriasContas,
                'extratos'         => $extratos,
                'extrato'          => null,
                'transacoes'       => collect(),
            ])->with('error', 'Erro ao processar o arquivo OFX. Tente reenviar.');
        }
    }

    public function dre(Request $request)
    {
        $user = auth()->user();
        $empresa = Empresa::find($user->empresa->empresa_id);
        $extrato = Extrato::find($request->query('extrato'));
        $dre = ExtratoService::gerarDRE($extrato);

        return view('extrato.dre', compact('empresa', 'extrato', 'dre'));
    }

    public function vincular(Request $request)
    {
        $request->validate([
            'id_extrato'        => ['required', 'integer'],
            'id_conta'          => ['required', 'integer'],
            'tipo_conta'        => ['required', 'string', 'in:App\Models\ContaPagar,App\Models\ContaReceber'],
            'ids_transacoes'    => ['required'],
        ]);

        $modelClass = $request->input('tipo_conta');
        $conta = $modelClass::findOrFail($request->input('id_conta'));

        foreach ($request->input('ids_transacoes') as $id_transacao) {
            Conciliacao::create([
                'extrato_id'        => $request->input('id_extrato'),
                'transacao_id'      => $id_transacao,
                'conciliavel_id'    => $request->input('id_conta'),
                'conciliavel_tipo'  => $request->input('tipo_conta'),
                'data_conciliacao'  => now(),
                'valor_conciliado'  => $conta->valor_pago ?? $conta->valor_recebido,
            ]);
        }

        return redirect()->to(url()->previous())
            ->with('success', 'Transação vinculada com sucesso.');
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
            'descricao'           => 'required|string|max:255',
            'valor'               => 'required|numeric|min:0',
            'data_vencimento'     => 'required|date',
            'centro_custo_id'     => 'nullable|integer',
            'categoria_conta_id'  => 'required|integer',
            'tipo'                => 'required|in:DEBIT,CREDIT',
            'transacao_id'        => 'required|integer|exists:transacoes,id',
            'extrato_id'          => 'required|integer',
        ]);

        $user = auth()->user();

        $dados = [
            'descricao'          => $validated['descricao'],
            'valor_integral'     => (float) $validated['valor'],
            'data_vencimento'    => $validated['data_vencimento'],
            'empresa_id'         => optional($user->empresa)->empresa_id ?? $user->empresa_id ?? null,
            'centro_custo_id'    => $validated['centro_custo_id'] ?? null,
            'categoria_conta_id' => $validated['categoria_conta_id'],
        ];

        // Ajusta conforme o tipo de conta
        if ($validated['tipo'] === 'DEBIT') {
            $dados['data_pagamento'] = $dados['data_vencimento'];
            $dados['valor_pago']     = $dados['valor_integral'];

            $conta = ContaPagar::create($dados);
        } else {
            $dados['data_recebimento'] = $dados['data_vencimento'];
            $dados['valor_recebido']   = $dados['valor_integral'];

            $conta = ContaReceber::create($dados);
        }

        // Vincula à transação
        $transacao = Transacao::find($validated['transacao_id']);
        $valorConciliado = $conta->valor_pago ?? $conta->valor_recebido;

        Conciliacao::create([
            'transacao_id'     => $transacao->id,
            'extrato_id'       => (int) $request->input('extrato_id'),
            'conciliavel_id'   => $conta->id,
            'conciliavel_tipo' => get_class($conta),
            'valor_conciliado' => $valorConciliado,
            'data_conciliacao' => now(),
        ]);

        return redirect()->to(url()->previous())
            ->with('success', 'Conta criada com sucesso.');
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
                    'data_conciliacao'  => now(),
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
