@php
    $perPage = 15;
    $pagePagamento = request()->get('page_pagamento', 1);
    $offsetPagamento = ($pagePagamento - 1) * $perPage;
    $contasPagarPaginadas = $contasPagar->slice($offsetPagamento, $perPage);
    $totalPaginasPagamento = ceil($contasPagar->count() / $perPage);
@endphp

<table class="table text-center table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Descrição</th>
            <th>Data do pagamento</th>
            <th>Valor</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        @forelse($contasPagarPaginadas as $conta)
            <tr>
                <td>{{ $conta->id ?? '-' }}</td>
                <td>{{ $conta->descricao ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($conta->data_pagamento ?? now())->format('d/m/Y') }}
                </td>
                <td>R$ {{ number_format($conta->valor_pago ?? 0, 2, ',', '.') }}</td>
                <td>
                    @if ($conta->conciliada())
                        <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i></span>
                    @else
                        <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle-fill"></i></span>
                    @endif
                </td>
                <td>
                    @if ($conta->conciliacoes->isNotEmpty())
                        <!-- Botão Desvincular -->
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalDesvincularContas-{{ $conta->id }}" title="Desvincular transações">
                            Desvincular
                        </button>

                        <!-- Botão Vincular -->
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                            data-bs-target="#modalVincularConta-{{ $conta->id }}" title="Vincular a outra transação">
                            Vincular
                        </button>
                    @else
                        <!-- Nenhuma conciliação ainda → só mostrar Vincular -->
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                            data-bs-target="#modalVincularConta-{{ $conta->id }}" title="Vincular a uma transação">
                            Vincular
                        </button>
                    @endif
                </td>
            </tr>

            <!-- Modal Desvincular exclusivo para essa conta -->
            @if ($conta->conciliacoes->isNotEmpty())
                <div class="modal fade" id="modalDesvincularContas-{{ $conta->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('extrato.desvincular') }}">
                                @csrf
                                <input type="hidden" name="id_conta" value="{{ $conta->id }}">
                                <input type="hidden" name="id_extrato" value="{{ $extrato->id ?? null }}">
                                <input type="hidden" name="tipo_conta" value="{{ get_class($conta) }}">

                                <div class="modal-header">
                                    <h5 class="modal-title">Desvincular transações conciliadas
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Fechar"></button>
                                </div>

                                <div class="modal-body">
                                    <p>Selecione as conciliações que deseja desvincular:</p>
                                    <div class="list-group">
                                        @foreach($conta->conciliacoes as $conciliacao)
                                            <label class="list-group-item d-flex">
                                                <input type="checkbox" name="ids_transacoes[]"
                                                    value="{{ $conciliacao->transacao->id }}" class="form-check-input me-2">
                                                <span>
                                                    {{ $conciliacao->transacao->descricao ?? 'Sem descrição' }}
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ \Carbon\Carbon::parse($conciliacao->transacao->data)->format('d/m/Y') }}
                                                        - R$
                                                        {{ number_format($conciliacao->transacao->valor, 2, ',', '.') }}
                                                    </small>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">Desvincular
                                        Selecionadas</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Vincular exclusivo para essa conta -->
            <div class="modal fade" id="modalVincularConta-{{ $conta->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('extrato.vincular') }}">
                            @csrf
                            <input type="hidden" name="id_conta" value="{{ $conta->id }}">
                            <input type="hidden" name="id_extrato" value="{{ $extrato->id }}">
                            <input type="hidden" name="tipo_conta" value="{{ get_class($conta) }}">

                            <div class="modal-header">
                                <h5 class="modal-title">Vincular transações à conta
                                    #{{ $conta->id }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Fechar"></button>
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
                                                    - R$
                                                    {{ number_format($transacao->valor, 2, ',', '.') }}
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
                                <button type="submit" class="btn btn-primary">Vincular
                                    Selecionadas</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted">Nenhuma conta a pagar
                    encontrada.</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if ($totalPaginasPagamento > 1)
    <nav>
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