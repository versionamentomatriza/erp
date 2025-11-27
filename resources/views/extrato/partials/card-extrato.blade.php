<div class="col">
    <div class="card h-100 shadow-sm border-0 {{ request()->get('extrato') == $extrato->id ? 'border-primary' : '' }}">
        <div class="card-header bg-verde-matriza p-2 d-flex justify-content-between align-items-center">
            <strong>{{ $extrato->banco ?? 'Banco não informado' }}</strong>

            <div class="d-flex align-items-center">
                @if(request()->get('extrato') == $extrato->id)
                    <span class="badge bg-success me-2">Selecionado</span>
                @endif

                <!-- Dropdown menu -->
                <div class="dropdown">
                    <button class="btn btn-sm bg-white dropdown-toggle" type="button"
                        id="menuExtrato{{ $extrato->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                        ⋮
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuExtrato{{ $extrato->id }}">
                        <li>
                            <a class="dropdown-item"
                                href="{{ route('extrato.movimentacao_bancaria', ['extrato_id' => $extrato->id]) }}">
                                Relatório de fluxo de caixa
                            </a>
                        </li>
                        <!-- Se quiser pode adicionar mais opções -->
                    </ul>
                </div>
            </div>
        </div>

        <div class="card-body p-2">
            <p class="mb-1"><small><strong>Período:</strong>
                    {{ \Carbon\Carbon::parse($extrato->inicio)->format('d/m/Y') }}
                    - {{ \Carbon\Carbon::parse($extrato->fim)->format('d/m/Y') }}
                </small></p>
            <p class="mb-1"><small><strong>Saldo Inicial:</strong>
                    R$ {{ number_format($extrato->saldo_inicial ?? 0, 2, ',', '.') }}
                </small></p>
            <p class="mb-1"><small><strong>Saldo Final:</strong>
                    R$ {{ number_format($extrato->saldo_final ?? 0, 2, ',', '.') }}
                </small></p>
            <p class="mb-1"><small><strong>Status:</strong>
                    @if($extrato->status === 'conciliado')
                        <span class="badge bg-success">Conciliado</span>
                    @else
                        <span class="badge bg-warning text-dark">Pendente</span>
                    @endif
                </small></p>
        </div>

        <div class="card-footer text-center p-2 bg-light">
            <a href="{{ route('extrato.conciliar', ['extrato' => $extrato->id]) }}"
                class="btn btn-sm btn-outline-success w-100 {{ request()->get('extrato') == $extrato->id ? 'disabled' : '' }}">
                Selecionar
            </a>
        </div>
    </div>
</div>