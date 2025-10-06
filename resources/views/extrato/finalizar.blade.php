@extends('layouts.app', ['title' => 'Conciliação Bancária'])

@section('content')
    <div class="container py-4">
        <div class="card border-0">
            <div class="card-header">
                <h5 class="mb-0">Ajustes de Conciliação</h5>
            </div>
            <div class="card-body">
                @unless ($transacoes->isEmpty())
                    <p class="text-muted">
                        Algumas transações do extrato <strong>{{ $extrato->banco }}</strong> possuem diferenças entre o valor conciliado
                        e o valor original.
                        Você pode <strong>escolher quais diferenças deseja ajustar</strong> gerando contas a pagar ou a receber.
                    </p>
                @endunless

                <form action="{{ route('extrato.excedente') }}" method="POST">
                    @csrf
                    <input type="hidden" name="extrato_id" value="{{ $extrato->id }}">
                    <input type="hidden" name="empresa_id" value="{{ $extrato->empresa_id }}">

                    {{-- Atualizações de contas financeiras com saldo divergente --}}
                    @foreach($contasFinanceirasEnvolvidas as $conta)
                        @php
                            $saldoCalculado = $conta->calcularSaldoAtual($extrato->id);
                        @endphp

                        @if($conta->saldo_atual !== $saldoCalculado)
                            <div class="card border-warning mb-3 shadow-sm">
                                <div class="card-header bg-warning text-dark fw-bold">
                                    ⚠️ {{ $conta->nome ?? 'Conta #' . $conta->id }} com saldo divergente
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label">Saldo atual</label>
                                            <input type="number" step="0.01" class="form-control"
                                                name="contas_divergentes[{{ $conta->id }}][saldo_atual]"
                                                value="{{ $conta->saldo_atual }}" disabled>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Saldo calculado (recomendado)</label>
                                            <input type="number" step="0.01" class="form-control bg-light"
                                                value="{{ $saldoCalculado }}" readonly>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Atualizar para</label>
                                            <input type="number" step="0.01" class="form-control border-primary"
                                                name="contas_divergentes[{{ $conta->id }}][novo_saldo]"
                                                value="{{ $saldoCalculado }}">
                                        </div>
                                    </div>

                                    <input type="hidden" name="contas_divergentes[{{ $conta->id }}][id]" value="{{ $conta->id }}">
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if($transacoes->isEmpty())
                        <div class="alert alert-success">
                            Todas as transações estão conciliadas corretamente.
                        </div>
                    @else
                        <div class="d-flex justify-content-end mb-3">
                            <button type="button" id="selectAll" class="btn btn-sm btn-outline-primary">
                                Selecionar todos
                            </button>
                            <button type="button" id="unselectAll" class="btn btn-sm btn-outline-secondary ms-2">
                                Desmarcar todos
                            </button>
                        </div>

                        <div class="row row-cols-1 g-3">
                            @foreach($transacoes as $t)
                                @php
                                    $excedente = $t->valorConciliado() - $t->valor;
                                    $tipoConta = $excedente > 0 ? 'pagar' : 'receber';
                                @endphp

                                <div class="col">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header {{ $excedente > 0 ? 'bg-warning text-dark' : 'bg-info text-white' }} p-2">
                                            Transação #{{ $t->id }} - {{ $t->descricao ?? 'Sem descrição' }}
                                            <span class="badge bg-dark ms-2">{{ strtoupper($tipoConta) }}</span>
                                        </div>

                                        <div class="card-body p-2">
                                            <p class="mb-1"><strong>Data:</strong> {{ \Carbon\Carbon::parse($t->data)->format('d/m/Y') }}
                                            </p>
                                            <p class="mb-1"><strong>Valor original:</strong> R$ {{ number_format($t->valor, 2, ',', '.') }}
                                            </p>
                                            <p class="mb-1"><strong>Valor conciliado:</strong> R$
                                                {{ number_format($t->valorConciliado(), 2, ',', '.') }}</p>
                                            <p class="mb-1"><strong>Diferença:</strong> R$ {{ number_format(abs($excedente), 2, ',', '.') }}
                                            </p>

                                            <div class="mb-2 form-check">
                                                <input class="form-check-input incluir-checkbox" type="checkbox" id="incluir_{{ $t->id }}"
                                                    name="transacoes[{{ $t->id }}][incluir]" value="1">
                                                <label class="form-check-label" for="incluir_{{ $t->id }}">
                                                    Gerar conta para essa diferença
                                                </label>
                                            </div>

                                            <input type="hidden" name="transacoes[{{ $t->id }}][id]" value="{{ $t->id }}">
                                            <input type="hidden" name="transacoes[{{ $t->id }}][tipo]" value="{{ $tipoConta }}">

                                            <div class="mb-2">
                                                <label for="descricao_{{ $t->id }}" class="form-label">Descrição da conta</label>
                                                <input type="text" class="form-control input-field" id="descricao_{{ $t->id }}"
                                                    name="transacoes[{{ $t->id }}][descricao]" placeholder="Descreva brevemente a conta"
                                                    disabled required>
                                            </div>

                                            <div class="mb-2">
                                                <label for="categoria_{{ $t->id }}" class="form-label">Categoria</label>
                                                <select class="form-select input-field" id="categoria_{{ $t->id }}"
                                                    name="transacoes[{{ $t->id }}][categoria_id]" disabled required>
                                                    <option value="" selected disabled>Selecione...</option>
                                                    @if($tipoConta === 'pagar')
                                                        @foreach($categoriasPagar as $categoria)
                                                            <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                                                        @endforeach
                                                    @else
                                                        @foreach($categoriasReceber as $categoria)
                                                            <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>

                                            <div class="mb-2">
                                                <label for="centro_custo_{{ $t->id }}" class="form-label">Centro de Custo (opcional)</label>
                                                <select class="form-select input-field" id="centro_custo_{{ $t->id }}"
                                                    name="transacoes[{{ $t->id }}][centro_custo_id]" disabled>
                                                    <option value="" selected>Não informado</option>
                                                    @foreach($centrosCustos as $centro)
                                                        <option value="{{ $centro->id }}">{{ $centro->descricao }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            @if($tipoConta === 'receber')
                                                <div class="mb-2">
                                                    <label for="data_vencimento_{{ $t->id }}" class="form-label">Data de vencimento</label>
                                                    <input type="date" class="form-control input-field" id="data_vencimento_{{ $t->id }}"
                                                        name="transacoes[{{ $t->id }}][data_receber]" disabled required>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-3 text-end">
                        <a href="{{ route('extrato.conciliar', ['extrato' => $extrato->id]) }}" class="btn btn-secondary">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            Finalizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.incluir-checkbox').forEach(cb => {
            cb.addEventListener('change', function () {
                const card = this.closest('.card-body');
                const inputs = card.querySelectorAll('.input-field');
                inputs.forEach(input => input.disabled = !this.checked);
            });
        });

        document.getElementById('selectAll').addEventListener('click', function () {
            document.querySelectorAll('.incluir-checkbox').forEach(cb => {
                cb.checked = true;
                cb.dispatchEvent(new Event('change'));
            });
        });

        document.getElementById('unselectAll').addEventListener('click', function () {
            document.querySelectorAll('.incluir-checkbox').forEach(cb => {
                cb.checked = false;
                cb.dispatchEvent(new Event('change'));
            });
        });
    </script>
@endsection