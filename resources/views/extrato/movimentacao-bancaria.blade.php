@extends('layouts.app', ['title' => 'Conciliação Bancária'])

@section('title', 'Conciliação Bancária')

@section('content')
    <div class="container py-4">
        <div class="card shadow-lg border-0 p-4">
            <!-- Cabeçalho -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 mb-1">Movimentação Bancária</h1>
                    <div class="text-muted small">
                        <i class="bi bi-building"></i> {{ $empresa->nome_fantasia ?? $empresa->nome }} <br>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-primary"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
                </div>
            </div>

            <!-- Cards resumo -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-none border-0">
                        <div class="card-body">
                            <div class="text-muted small">Receita Líquida</div>
                            <div class="fs-5 fw-semibold">R$
                                {{ number_format($movimentacao['receita_liquida'], 2, ',', '.') }}
                            </div>
                            <div class="small text-muted">Bruta - Deduções</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-none border-0">
                        <div class="card-body">
                            <div class="text-muted small">Lucro Bruto</div>
                            <div class="fs-5 fw-semibold">R$ {{ number_format($movimentacao['lucro_bruto'], 2, ',', '.') }}
                            </div>
                            <div class="small text-muted">Receita Líquida - Custos</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-none border-0">
                        <div class="card-body">
                            <div class="text-muted small">Resultado Operacional</div>
                            <div class="fs-5 fw-semibold">R$
                                {{ number_format($movimentacao['resultado_operacional'], 2, ',', '.') }}
                            </div>
                            <div class="small text-muted">Lucro Bruto - Despesas Op.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-none border-0">
                        <div class="card-body">
                            <div class="text-muted small">Lucro Líquido</div>
                            <div
                                class="fs-5 fw-semibold {{ $movimentacao['lucro_liquido'] < 0 ? 'text-danger' : 'text-success' }}">
                                R$ {{ number_format($movimentacao['lucro_liquido'], 2, ',', '.') }}
                            </div>
                            <div class="small text-muted">Resultado final do período</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nav Tabs -->
            <ul class="nav nav-tabs" id="movTabs" role="tablist">
                <!-- Aba principal fixa -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-geral-tab"
                    data-bs-toggle="tab" data-bs-target="#tab-geral" type="button" role="tab">
                    Resumo Geral
                    </button>
                </li>

                <!-- Abas dinâmicas por conta -->
                @foreach ($contasFinanceiras as $index => $conta)
                    <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-{{ $index }}-tab"
                        data-bs-toggle="tab" data-bs-target="#tab-{{ $index }}" type="button" role="tab">
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
                        @include('extrato.partials.tabela-dre', ['movimentacao' => $movimentacao])
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
                                        ->orderByDesc('data_conciliacao')
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