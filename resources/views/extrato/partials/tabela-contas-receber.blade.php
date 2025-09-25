@php
    $perPage = 15;
    $pageRecebimento = request()->get('page_recebimento', 1);
    $offsetRecebimento = ($pageRecebimento - 1) * $perPage;
    $contasReceberPaginadas = $contasReceber->slice($offsetRecebimento, $perPage);
    $totalPaginasReceber = ceil($contasReceber->count() / $perPage);
@endphp

<div class="row g-3">
    @forelse($contasReceberPaginadas as $conta)
        <div class="col-12">
            @php
                $isConciliada = $conta->conciliada();
                $statusLabel = $isConciliada ? 'Conciliada' : 'Pendente';
                $statusClass = $isConciliada ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning';
            @endphp

            <div class="card card-conta shadow-sm border-0 rounded-3 mb-4 position-relative">
                <span class="position-absolute top-0 end-0 mt-2 me-2 px-2 py-1 small text-uppercase fw-semibold {{ $statusClass }}" style="border-radius: 0.25rem; font-size: 0.65rem;">
                    {{ $statusLabel }}
                </span>

                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h6 class="text-primary fw-bold mb-0"><i class="bi bi-hash"></i> {{ $conta->id }}</h6>
                        <span class="fw-bold text-success fs-6">R$ {{ number_format($conta->valor_pago ?? 0, 2, ',', '.') }}</span>
                    </div>

                    <div class="row gy-3">
                        <div class="col-md-12">
                            <p class="mb-1 text-secondary small fw-semibold">Descrição</p>
                            <p class="mb-0">{{ $conta->descricao ?? '-' }}</p>
                        </div>

                        <div class="col-md-3">
                            <p class="mb-1 text-secondary small fw-semibold">Vencido em</p>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1 text-secondary small fw-semibold">Recebido em</p>
                            <p class="mb-0">{{ $conta->data_recebimento ? \Carbon\Carbon::parse($conta->data_recebimento)->format('d/m/Y') : '-' }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1 text-secondary small fw-semibold">Valor Integral</p>
                            <p class="mb-0">R$ {{ number_format($conta->valor_integral ?? 0, 2, ',', '.') }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1 text-secondary small fw-semibold">Valor Recebido</p>
                            <p class="mb-0">R$ {{ number_format($conta->valor_recebido ?? 0, 2, ',', '.') }}</p>
                        </div>
                        @if ($conta->cliente)
                            <div class="col-md-12">
                                <p class="mb-1 text-secondary small fw-semibold">Cliente</p>
                                <p class="mb-0">
                                    {{ $conta->cliente->nome_fantasia ?? $conta->cliente->razao_social ?? '-' }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2 flex-wrap">
                        @if ($conta->conciliacoes->isNotEmpty())
                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalDesvincularContas-{{ $conta->id }}">
                                Desconciliar
                            </button>
                        @else
                            <form action="{{ route('extrato.excluir_conta') }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Tem certeza que deseja excluir esta conta?')">
                                @csrf
                                <input type="hidden" name="id" value="{{ $conta->id }}">
                                <input type="hidden" name="tipo" value="{{ get_class($conta) }}">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    Excluir</button>
                            </form>
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalVincularConta-{{ $conta->id }}">
                                Conciliar
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Desvincular Contas -->
        @if($conta->conciliacoes->isNotEmpty())
        <div class="modal fade" id="modalDesvincularContas-{{ $conta->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="formDesvincularRecebimento-{{ $conta->id }}" method="POST" action="{{ route('extrato.desvincular') }}">
                        @csrf
                        <input type="hidden" name="id_extrato" value="{{ $extrato->id ?? null }}">
                        <input type="hidden" name="id_conta" value="{{ $conta->id }}">
                        <input type="hidden" name="tipo_conta" value="{{ get_class($conta) }}">

                        <div class="modal-header border-bottom py-3 bg-laranja-matriza">
                            <h5 class="modal-title fw-semibold mb-0">Desconciliar transações</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <p>Selecione as conciliações que deseja desconciliar:</p>
                            <div class="list-group">
                                @foreach($conta->conciliacoes as $conciliacao)
                                    <label class="list-group-item d-flex" style="border: none;">
                                        <input type="checkbox" name="ids_transacoes[]" value="{{ $conciliacao->transacao->id }}" class="form-check-input me-2">
                                        <span>
                                            {{ $conciliacao->transacao->descricao ?? 'Sem descrição' }}<br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($conciliacao->transacao->data)->format('d/m/Y') }} - R$ {{ number_format($conciliacao->transacao->valor, 2, ',', '.') }}</small>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Desconciliar Selecionadas</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Modal Vincular Contas Receber -->
        <div class="modal fade" id="modalVincularConta-{{ $conta->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="formConciliarRecebimento-{{ $conta->id }}" method="POST" action="{{ route('extrato.vincular') }}">
                        @csrf
                        <input type="hidden" name="id_conta" value="{{ $conta->id }}">
                        <input type="hidden" name="id_extrato" value="{{ $extrato->id }}">
                        <input type="hidden" name="tipo_conta" value="{{ get_class($conta) }}">

                        <div class="modal-header border-bottom py-3 bg-verde-matriza">
                            <h5 class="modal-title fw-semibold mb-0">Conciliar conta #{{ $conta->id }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col">
                                    <label for="contaFinanceira" class="form-label">Conta Financeira</label>
                                    <select name="id_conta_financeira" id="contaFinanceira" class="form-select" required>
                                        <option value="">Selecione</option>
                                        @foreach ($contasFinanceiras as $conta)
                                            <option value="{{ $conta->id }}">{{ $conta->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            @if ($conta->status === 0)
                                <div class="mb-3">
                                    <label for="valor_recebido-{{ $conta->id }}" class="form-label">Valor Recebido</label>
                                    <input type="text" name="valor_recebido" id="valor_recebido-{{ $conta->id }}" class="form-control" placeholder="Digite o valor recebido" value="{{ old('valor_recebido') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="data_recebimento-{{ $conta->id }}" class="form-label">Data de Recebimento</label>
                                    <input type="date" name="data_recebimento" id="data_recebimento-{{ $conta->id }}" class="form-control" value="{{ old('data_recebimento') }}" required>
                                </div>
                            @endif

                            <p>Selecione as transações:</p>
                            <div class="list-group">
                                @forelse($transacoes as $transacao)
                                    @php $tipoEsperado = str_contains(get_class($conta), 'ContaPagar') ? 'DEBIT' : 'CREDIT'; @endphp
                                    @continue($transacao->tipo !== $tipoEsperado)

                                    <label class="list-group-item d-flex" style="border: none;">
                                        <input type="checkbox" name="ids_transacoes[]" value="{{ $transacao->id }}" class="form-check-input me-2">
                                        <span>
                                            {{ $transacao->descricao ?? 'Sem descrição' }}<br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($transacao->data)->format('d/m/Y') }} - R$ {{ number_format($transacao->valor, 2, ',', '.') }}</small>
                                        </span>
                                    </label>
                                @empty
                                    <div class="alert alert-warning">Nenhuma transação disponível para conciliar.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Conciliar Selecionadas</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @empty
        <div class="col-12">
            <div class="alert alert-info text-center">Nenhuma conta a receber encontrada.</div>
        </div>
    @endforelse
</div>

@if ($totalPaginasReceber > 1)
    <nav>
        <ul class="pagination justify-content-center">
            @for ($i = 1; $i <= $totalPaginasReceber; $i++)
                <li class="page-item {{ $i == $pageRecebimento ? 'active' : '' }}">
                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page_recebimento' => $i]) }}">{{ $i }}</a>
                </li>
            @endfor
        </ul>
    </nav>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form[id^="formConciliarRecebimento"]');

    forms.forEach(form => {
        const contaId = form.id.split('-')[1];
        const input = document.getElementById(`valor_recebido-${contaId}`);
        if (!input) return;

        input.addEventListener('input', function () {
            let value = this.value.replace(/\D/g, '');
            value = (value / 100).toFixed(2) + '';
            value = value.replace('.', ',');
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            this.value = value;
        });

        form.addEventListener('submit', function () {
            if (input.value) {
                let normalized = input.value.replace(/\./g, '').replace(',', '.');
                input.value = normalized;
            }
        });
    });
});
</script>
