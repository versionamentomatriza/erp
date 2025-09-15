@php
    $perPage = 15;
    $pagePagamento = request()->get('page_pagamento', 1);
    $offsetPagamento = ($pagePagamento - 1) * $perPage;
    $contasPagarPaginadas = $contasPagar->slice($offsetPagamento, $perPage);
    $totalPaginasPagamento = ceil($contasPagar->count() / $perPage);
@endphp

<style>
    /* Efeito elegante nos cards */
    .card-conta {
        transition: all 0.2s ease-in-out;
        border-left: 5px solid transparent;
    }

    .card-conta:hover {
        box-shadow: 0 6px 16px rgba(0, 123, 255, 0.25);
        border-left: 5px solid #0d6efd;
        transform: translateY(-2px);
    }
</style>

<div class="row g-3">
    @forelse($contasPagarPaginadas as $conta)
        <div class="col-12">
            @php
                $isConciliada = $conta->conciliada();
                $statusLabel = $isConciliada ? 'Conciliada' : 'Pendente';
                $statusClass = $isConciliada ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning';
            @endphp

            <div class="card card-conta shadow-sm border-0 rounded-3 mb-4 position-relative">
                <!-- Badge de status mais discreto -->
                <span
                    class="position-absolute top-0 end-0 mt-2 me-2 px-2 py-1 small text-uppercase fw-semibold {{ $statusClass }}"
                    style="border-radius: 0.25rem; font-size: 0.65rem;">
                    {{ $statusLabel }}
                </span>

                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h6 class="text-primary fw-bold mb-0">
                            <i class="bi bi-hash"></i> {{ $conta->id }}
                        </h6>

                        <!-- Valor em destaque -->
                        <div>
                            <span class="fw-bold text-success fs-6">
                                R$ {{ number_format($conta->valor_pago ?? 0, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="row gy-3">
                        <!-- Descrição -->
                        <div class="col-md-4">
                            <p class="mb-1 text-secondary small fw-semibold">Descrição</p>
                            <p class="mb-0">{{ $conta->descricao ?? '-' }}</p>
                        </div>

                        <!-- Pagamento -->
                        <div class="col-md-2">
                            <p class="mb-1 text-secondary small fw-semibold">Pagamento</p>
                            <p class="mb-0">
                                {{ \Carbon\Carbon::parse($conta->data_pagamento ?? now())->format('d/m/Y') }}
                            </p>
                        </div>

                        <!-- Centro de Custo -->
                        <div class="col-md-3">
                            <p class="mb-1 text-secondary small fw-semibold">Centro de Custo</p>
                            <span class="badge bg-light text-dark border small">
                                {{ $conta->centroCusto->descricao ?? '-' }}
                            </span>
                        </div>

                        <!-- Fornecedor / Cliente -->
                        @if($conta->fornecedor)
                            <div class="col-md-3">
                                <p class="mb-1 text-secondary small fw-semibold">Fornecedor</p>
                                <span class="badge bg-info text-dark small px-2 py-1">
                                    {{ $conta->fornecedor->nome_fantasia ?? $conta->fornecedor->razao_social }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Rodapé com ações -->
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2 flex-wrap">
                        @if ($conta->conciliacoes->isNotEmpty())
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalDesvincularContas-{{ $conta->id }}" title="Desvincular transações">
                                <i class="bi bi-link-45deg"></i> Desvincular
                            </button>
                        @else
                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalVincularConta-{{ $conta->id }}" title="Vincular a uma transação">
                                <i class="bi bi-plus-circle"></i> Vincular
                            </button>

                            <form action="{{ route('extrato.excluir_conta') }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Tem certeza que deseja excluir esta conta?')">
                                @csrf
                                <input type="hidden" name="id" value="{{ $conta->id }}">
                                <input type="hidden" name="tipo" value="{{ get_class($conta) }}">
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Excluir conta">
                                    <i class="bi bi-trash"></i> Excluir
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Desvincular -->
        @if ($conta->conciliacoes->isNotEmpty())
            <div class="modal fade" id="modalDesvincularContas-{{ $conta->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('extrato.desvincular') }}">
                            @csrf
                            <input type="hidden" name="id_extrato" value="{{ $extrato->id ?? null }}">
                            <input type="hidden" name="id_conta" value="{{ $conta->id }}">
                            <input type="hidden" name="tipo_conta" value="{{ get_class($conta) }}">

                            <div class="modal-header">
                                <h5 class="modal-title">Desvincular transações conciliadas</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>

                            <div class="modal-body">
                                <p>Selecione as conciliações que deseja desvincular:</p>
                                <div class="list-group">
                                    @foreach($conta->conciliacoes as $conciliacao)
                                        <label class="list-group-item d-flex">
                                            <input type="checkbox" name="ids_transacoes[]" value="{{ $conciliacao->transacao->id }}"
                                                class="form-check-input me-2">
                                            <span>
                                                {{ $conciliacao->transacao->descricao ?? 'Sem descrição' }}
                                                <br>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($conciliacao->transacao->data)->format('d/m/Y') }}
                                                    - R$ {{ number_format($conciliacao->transacao->valor, 2, ',', '.') }}
                                                </small>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger">Desvincular Selecionadas</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Modal Vincular -->
        <div class="modal fade" id="modalVincularConta-{{ $conta->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('extrato.vincular') }}">
                        @csrf
                        <input type="hidden" name="id_conta" value="{{ $conta->id }}">
                        <input type="hidden" name="id_extrato" value="{{ $extrato->id }}">
                        <input type="hidden" name="tipo_conta" value="{{ get_class($conta) }}">

                        <div class="modal-header">
                            <h5 class="modal-title">Vincular transações à conta #{{ $conta->id }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>

                        <div class="modal-body">
                            <p>Selecione as transações para vincular:</p>
                            <div class="list-group">
                                @forelse($transacoes as $transacao)
                                    @php
                                        $tipoEsperado = str_contains(get_class($conta), 'ContaPagar') ? 'DEBIT' : 'CREDIT';
                                    @endphp

                                    @continue($transacao->tipo !== $tipoEsperado)

                                    <label class="list-group-item d-flex">
                                        <input type="checkbox" name="ids_transacoes[]" value="{{ $transacao->id }}"
                                            class="form-check-input me-2">
                                        <span>
                                            {{ $transacao->descricao ?? 'Sem descrição' }}
                                            <br>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($transacao->data)->format('d/m/Y') }}
                                                - R$ {{ number_format($transacao->valor, 2, ',', '.') }}
                                            </small>
                                        </span>
                                    </label>
                                @empty
                                    <div class="alert alert-warning">
                                        Nenhuma transação disponível para vincular.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Vincular Selecionadas</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                Nenhuma conta a pagar encontrada.
            </div>
        </div>
    @endforelse
</div>

@if ($totalPaginasPagamento > 1)
    <nav class="mt-3">
        <ul class="pagination justify-content-center">
            @for ($i = 1; $i <= $totalPaginasPagamento; $i++)
                <li class="page-item {{ $i == $pagePagamento ? 'active' : '' }}">
                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page_pagamento' => $i]) }}">
                        {{ $i }}
                    </a>
                </li>
            @endfor
        </ul>
    </nav>
@endif