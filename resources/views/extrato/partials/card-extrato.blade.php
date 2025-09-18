<div class="col">
    <div class="card h-100 shadow-sm border-0 {{ request()->get('extrato') == $e->id ? 'border-primary' : '' }}">
        <div class="card-header bg-success text-white p-2 d-flex justify-content-between align-items-center">
            <strong>{{ $e->banco ?? 'Banco não informado' }}</strong>

            <div class="d-flex align-items-center">
                @if(request()->get('extrato') == $e->id)
                    <span class="badge bg-success me-2">Selecionado</span>
                @endif

                <!-- Dropdown menu -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-light text-success dropdown-toggle" type="button"
                        id="menuExtrato{{ $e->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                        ⋮
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuExtrato{{ $e->id }}">
                        <li>
                            <a class="dropdown-item"
                                href="{{ route('extrato.movimentacao_bancaria', ['extrato' => $e->id]) }}">
                                Relatório de movimentação bancária
                            </a>
                        </li>
                        <!-- Se quiser pode adicionar mais opções -->
                    </ul>
                </div>
            </div>
        </div>

        <div class="card-body p-2">
            <p class="mb-1"><small><strong>Período:</strong>
                    {{ \Carbon\Carbon::parse($e->inicio)->format('d/m/Y') }}
                    - {{ \Carbon\Carbon::parse($e->fim)->format('d/m/Y') }}
                </small></p>
            <p class="mb-1"><small><strong>Saldo Inicial:</strong>
                    R$ {{ number_format($e->saldo_inicial ?? 0, 2, ',', '.') }}
                </small></p>
            <p class="mb-1"><small><strong>Saldo Final:</strong>
                    R$ {{ number_format($e->saldo_final ?? 0, 2, ',', '.') }}
                </small></p>
            <p class="mb-1"><small><strong>Status:</strong>
                    @if($e->status === 'conciliado')
                        <span class="badge bg-success">Conciliado</span>
                    @else
                        <span class="badge bg-warning text-dark">Pendente</span>
                    @endif
                </small></p>
        </div>

        <div class="card-footer text-center p-2 bg-light">
            <a href="{{ route('extrato.conciliar', ['extrato' => $e->id]) }}"
                class="btn btn-sm btn-outline-success w-100 {{ request()->get('extrato') == $e->id ? 'disabled' : '' }}">
                Selecionar
            </a>
        </div>
    </div>
</div>