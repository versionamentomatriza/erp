<div class="col-md-6">
    <div class="card shadow-none border-1">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="card-title">{{ $conta->nome }}</h5>
                    <div class="text-muted small">
                        Banco: {{ $conta->banco ?? '-' }} | Agência: {{ $conta->agencia ?? '-' }}
                    </div>
                </div>

                <!-- Menu de ações -->
                <div class="dropdown">
                    @unless ($extrato->status === 'conciliado')
                        <button class="btn btn-sm bg-white dropdown-toggle no-shadow" type="button" id="menuAcoes-{{ $conta->id }}"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            ⋮
                        </button>
                    @endunless
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuAcoes-{{ $conta->id }}">
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                data-bs-target="#editarSaldoModal-{{ $conta->id }}">
                                Editar saldo atual
                            </a>
                        </li>
                        {{-- Aqui no futuro dá pra adicionar mais opções (ex: Excluir, Detalhes etc.) --}}
                    </ul>
                </div>
            </div>

            <hr class="my-2">

            <div class="d-flex justify-content-between">
                <span>Saldo Inicial:</span>
                <span>R$ {{ number_format($conta->saldo_inicial, 2, ',', '.') }}</span>
            </div>

            <div class="d-flex justify-content-between">
                <span>Saldo Atual (BD):</span>
                <span>R$ {{ number_format($conta->saldo_atual, 2, ',', '.') }}</span>
            </div>

            <div class="d-flex justify-content-between fw-semibold">
                <span>Saldo Calculado:</span>
                <span>
                    @if(abs($saldoCalculado - $conta->saldo_atual) !== 0)
                        <i class="bi bi-exclamation-triangle-fill text-warning ms-1"
                            title="Diferença detectada entre o saldo calculado e o saldo atual da conta."></i>
                    @endif
                    R$ {{ number_format($saldoCalculado, 2, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="editarSaldoModal-{{ $conta->id }}" tabindex="-1"
    aria-labelledby="editarSaldoModalLabel-{{ $conta->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('contas-financeiras.update', $conta->id) }}" method="POST" class="modal-content">
            @csrf
            @method('PUT')

            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-semibold mb-0" id="editarSaldoModalLabel-{{ $conta->id }}">
                    Editar Saldo - {{ $conta->nome }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label for="saldo_atual-{{ $conta->id }}" class="form-label">Saldo Atual</label>
                    <input type="text" class="form-control moeda" id="saldo_atual-{{ $conta->id }}" name="saldo_atual"
                        value="{{ number_format($conta->saldo_atual, 2, ',', '.') }}">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">Salvar</button>
            </div>
        </form>
    </div>
</div>