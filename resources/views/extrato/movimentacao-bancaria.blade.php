@extends('layouts.app', ['title' => 'Conciliação Bancária'])

@section('title', 'Conciliação Bancária')

@section('content')
    <div class="container py-4">
        <div class="card shadow-lg border-0 p-4">
            <!-- Cabeçalho -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 mb-1">Fluxo de caixa</h1>
                    <div class="text-muted small">
                        <i class="bi bi-building"></i> {{ $empresa->nome_fantasia ?? $empresa->nome }} <br>
                    </div>
                </div>
            </div>

            <!-- Card de Resumo -->
            <div class="card shadow-none mb-4 p-3">
                <div class="row text-center">
                    <div class="col">
                        <h6 class="text-success mb-1">Entradas</h6>
                        <h4 class="fw-bold text-success">R$ {{ number_format($movimentacao['total_entradas'], 2, ',', '.') }}</h4>
                    </div>
                    <div class="col">
                        <h6 class="text-danger mb-1">Saídas</h6>
                        <h4 class="fw-bold text-danger">R$ {{ number_format($movimentacao['total_saidas'], 2, ',', '.') }}</h4>
                    </div>
                    <div class="col">
                        <h6 class="text-muted mb-1">Balanço do período</h6>
                        <h4 class="fw-bold {{ $movimentacao['saldo_final'] >= 0 ? 'text-success' : 'text-danger' }}">R$ {{ number_format($movimentacao['saldo_final'], 2, ',', '.') }}</h4>
                    </div>
                </div>
            </div>

            <!-- Nav Tabs -->
            <ul class="nav nav-tabs" id="movTabs" role="tablist">
                <!-- Aba principal fixa -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-geral-tab" data-bs-toggle="tab" data-bs-target="#tab-geral"
                        type="button" role="tab">
                        Resumo Geral
                    </button>
                </li>

                <!-- Abas dinâmicas por conta -->
                @foreach ($contasFinanceiras as $index => $conta)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-{{ $index }}-tab" data-bs-toggle="tab"
                            data-bs-target="#tab-{{ $index }}" type="button" role="tab">
                            {{ $conta->nome }}
                        </button>
                    </li>
                @endforeach
            </ul>
            <!-- Conteúdo das Tabs -->
            <div class="tab-content mt-3" id="movTabsContent">
                <!-- Conteúdo da aba fixa -->
                <div class="tab-pane fade show active" id="tab-geral" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <!-- Aqui entra a tua tabela principal -->
                            <div class="accordion" id="accordionFluxoCaixa">
                                @foreach ($movimentacao['categorias'] as $nome => $categoria)
                                    @if ($categoria['contas']->isNotEmpty())
                                        @php
                                            $id = Str::slug($nome);
                                            $isEntrada = $categoria['tipo'] === 'entrada';
                                            $colorClass = $isEntrada ? 'text-success' : 'text-danger';
                                            $bgClass = $isEntrada ? 'bg-success-subtle' : 'bg-danger-subtle';
                                            $first = $loop->first;
                                        @endphp

                                        <div class="accordion-item mb-2 shadow-sm rounded">
                                            <h2 class="accordion-header" id="heading-{{ $id }}">
                                                <button class="accordion-button {{ $first ? '' : 'collapsed' }} fw-semibold {{ $bgClass }}" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#collapse-{{ $id }}"
                                                    aria-expanded="false" aria-controls="collapse-{{ $id }}">
                                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                                        <span>
                                                            @if ($isEntrada)
                                                                <i class="fa-solid fa-circle-arrow-down text-success me-2"></i>
                                                            @else
                                                                <i class="fa-solid fa-circle-arrow-up text-danger me-2"></i>
                                                            @endif
                                                            {{ $nome }}
                                                        </span>
                                                        <span class="fw-bold {{ $colorClass }}">
                                                            R$ {{ number_format($categoria['total'], 2, ',', '.') }}
                                                        </span>
                                                    </div>
                                                </button>
                                            </h2>

                                            <div id="collapse-{{ $id }}" class="accordion-collapse collapse {{ $first ? 'show' : '' }}"
                                                aria-labelledby="heading-{{ $id }}" data-bs-parent="#accordionFluxoCaixa">
                                                <div class="accordion-body">
                                                    @if ($categoria['contas']->isEmpty())
                                                        <p class="text-muted fst-italic mb-0">Nenhuma movimentação nesta categoria.</p>
                                                    @else
                                                        <div class="table-responsive">
                                                            <table class="table table-sm align-middle mb-0">
                                                                <thead>
                                                                    <tr class="table-light">
                                                                        <th>Data</th>
                                                                        <th>Descrição</th>
                                                                        <th>Valor</th>
                                                                        <th>Transações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($categoria['contas'] as $conta)
                                                                        @php
                                                                            $valor = $conta->valor_pago ?? $conta->valor_recebido ?? $conta->valor_integral;
                                                                        @endphp
                                                                        <tr>
                                                                            <td>
                                                                                @php
                                                                                    $data = $conta->data_pagamento ?: $conta->data_recebimento;
                                                                                @endphp
                                                                                {{$data ? \Illuminate\Support\Carbon::parse($data)->format('d/m/Y') : '-'}}
                                                                            </td>
                                                                            <td>{{ $conta->descricao ?? $conta->observacao ?? 'Sem descrição' }}
                                                                            </td>
                                                                            <td class="fw-bold">R$
                                                                                {{ number_format($valor, 2, ',', '.') }}
                                                                            </td>
                                                                            <td>
                                                                                @if ($conta->conciliacoes->isNotEmpty())
                                                                                    <ul class="list-unstyled mb-0">
                                                                                        @foreach ($conta->conciliacoes as $conc)
                                                                                            <li class="small text-muted">
                                                                                                <i class="fa-regular fa-clock me-1"></i>
                                                                                                {{ optional($conc->data_conciliacao)->format('d/m/Y') }}
                                                                                                — Transação #{{ $conc->transacao_id }}
                                                                                                (R$
                                                                                                {{ number_format($conc->valor_conciliado, 2, ',', '.') }})
                                                                                            </li>
                                                                                        @endforeach
                                                                                    </ul>
                                                                                @else
                                                                                    <span class="text-muted small fst-italic">Sem
                                                                                        transações</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Conteúdo das abas de cada conta -->
                @foreach ($contasFinanceiras as $index => $conta)
                    <div class="tab-pane fade" id="tab-{{ $index }}" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-light fw-bold">
                                <div class="d-flex justify-content-between">
                                    {{ $conta->nome ?? 'Conta ' . ($index + 1) }}
                                    <span>Saldo atual: R$ {{ number_format($conta->saldo_atual, 2, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="card-body">

                                {{-- 🟢 CONCILIAÇÕES --}}
                                <h6 class="mb-3 text-success">Conciliações</h6>
                                @php
                                    $conciliacoes = $conta->conciliacoes()
                                        ->with(['transacao', 'conciliavel'])
                                        ->orderBy('data_conciliacao')
                                        ->get();
                                @endphp

                                @if($conciliacoes->isEmpty())
                                    <p class="text-muted">Nenhuma conciliação registrada para esta conta.</p>
                                @else
                                    <div class="table-responsive mb-4">
                                        <table class="table table-sm align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Data</th>
                                                    <th>Tipo</th>
                                                    <th>Descrição</th>
                                                    <th class="text-end">Valor Conciliado (R$)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($conciliacoes as $c)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($c->data_conciliacao)->format('d/m/Y') }}</td>
                                                        <td>
                                                            @if (str_contains($c->conciliavel_tipo, 'Receber'))
                                                                <span class="badge bg-success">Recebimento</span>
                                                            @elseif (str_contains($c->conciliavel_tipo, 'Pagar'))
                                                                <span class="badge bg-danger">Pagamento</span>
                                                            @else
                                                                <span class="badge bg-secondary">Outro</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $c->transacao->descricao ?? '-' }}</td>
                                                        <td class="text-end">
                                                            R$ {{ number_format($c->valor_conciliado, 2, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                {{-- 🔵 TRANSFERÊNCIAS --}}
                                <h6 class="mb-3 text-primary">Transferências</h6>
                                @php
                                    $transferencias = \App\Models\TransferenciaConta::with(['contaOrigem', 'contaDestino', 'transacao'])
                                        ->where(function ($q) use ($conta) {
                                            $q->where('conta_origem_id', $conta->id)
                                            ->orWhere('conta_destino_id', $conta->id);
                                        })
                                        ->orderByDesc('id')
                                        ->get();
                                @endphp

                                @if($transferencias->isEmpty())
                                    <p class="text-muted">Nenhuma transferência encontrada.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Data</th>
                                                    <th>Origem</th>
                                                    <th>Destino</th>
                                                    <th>Descrição</th>
                                                    <th class="text-end">Valor (R$)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($transferencias as $t)
                                                    <tr class="{{ $t->conta_origem_id == $conta->id ? 'table-danger' : 'table-success' }}">
                                                        <td>{{ optional($t->transacao)->data ? \Carbon\Carbon::parse($t->transacao->data)->format('d/m/Y') : '-' }}</td>
                                                        <td>{{ $t->contaOrigem->nome ?? '-' }}</td>
                                                        <td>{{ $t->contaDestino->nome ?? '-' }}</td>
                                                        <td>{{ $t->transacao->descricao ?? '-' }}</td>
                                                        <td class="text-end">
                                                            R$ {{ number_format($t->transacao->valor ?? 0, 2, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection