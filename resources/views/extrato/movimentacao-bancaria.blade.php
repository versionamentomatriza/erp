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
                        <i class="bi bi-calendar3"></i> {{ $extrato->banco ?? 'Banco não informado' }}:
                        {{ $extrato->inicio }} — {{ $extrato->fim }}
                    </div>
                    {{--
                    <div class="text-muted small">
                        <i class="bi bi-cash-stack"></i> Saldo inicial do extrato:
                        <strong>R$ {{ number_format($extrato->saldo_inicial, 2, ',', '.') }}</strong>
                    </div>
                    <div class="text-muted small">
                        <i class="bi bi-cash-stack"></i> Saldo final do extrato:
                        <strong>R$ {{ number_format($extrato->saldo_final, 2, ',', '.') }}</strong>
                    </div>
                    <div class="text-muted small">
                        <i class="bi bi-cash-stack"></i> Saldo de acordo com a movimentação:
                        <strong>R$ {{ number_format($saldoConciliado, 2, ',', '.') }}</strong>
                    </div>
                    <div class="text-muted small">
                        <i class="bi bi-cash-stack"></i> Diferença (saldo final do extrato - saldo de acordo com a
                        movimentação):
                        <strong>R$ {{ number_format($extrato->saldo_final - $saldoConciliado, 2, ',', '.') }}</strong>
                        @if(($extrato->saldo_final - $saldoConciliado) != 0)
                        <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                        @endif
                    </div>
                    --}}
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

            <!-- Contas Financeiras -->
            <div class="row g-3 mb-3">
                @foreach($contasFinanceiras as $conta)
                    @php
                        $saldoCalculado = $conta->calcularSaldoAtual($extrato->fim);
                    @endphp
                    @include('extrato.partials.card-conta-financeira', ['conta' => $conta, 'extrato' => $extrato]);
                @endforeach
            </div>

            <!-- Tabela -->
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Conta</th>
                            <th class="text-end">Valor (R$)</th>
                            <th class="text-end">% Receita Líquida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Receita -->
                        <tr class="table-group-divider">
                            <td class="fw-semibold">Receita Bruta</td>
                            <td class="text-end">R$ {{ number_format($movimentacao['receita_bruta'], 2, ',', '.') }}</td>
                            <td class="text-end">
                                {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['receita_bruta'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                        @foreach(($movimentacao['contas_receber_por_grupo']['receita_bruta'] ?? []) as $conta)
                            <tr>
                                <td class="ps-4 text-muted">{{ $conta->descricao ?? $conta->nome }}</td>
                                <td class="text-end">R$
                                    {{ number_format($conta->valor_recebido ?? $conta->valor_integral, 2, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        @endforeach

                        <tr>
                            <td class="text-muted">(-) Deduções</td>
                            <td class="text-end text-danger">R$
                                {{ number_format($movimentacao['deducao_receita'], 2, ',', '.') }}</td>
                            <td class="text-end text-danger">
                                {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['deducao_receita'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                        @foreach(($movimentacao['contas_pagar_por_grupo']['deducao_receita'] ?? []) as $conta)
                            <tr>
                                <td class="ps-4 text-muted">{{ $conta->descricao ?? $conta->nome }}</td>
                                <td class="text-end text-danger">R$
                                    {{ number_format($conta->valor_pago ?? $conta->valor_integral, 2, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        @endforeach
                        @foreach(($movimentacao['contas_receber_por_grupo']['deducao_receita'] ?? []) as $conta)
                            <tr>
                                <td class="ps-4 text-muted">{{ $conta->descricao ?? $conta->nome }}</td>
                                <td class="text-end text-danger">R$
                                    {{ number_format($conta->valor_recebido ?? $conta->valor_integral, 2, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        @endforeach

                        <tr class="table-secondary">
                            <td class="fw-semibold">= Receita Líquida</td>
                            <td class="text-end fw-semibold">R$
                                {{ number_format($movimentacao['receita_liquida'], 2, ',', '.') }}</td>
                            <td class="text-end fw-semibold">100,00%</td>
                        </tr>

                        <!-- Custos -->
                        <tr class="table-group-divider">
                            <td class="text-muted">(-) Custos</td>
                            <td class="text-end text-danger">R$ {{ number_format($movimentacao['custo'], 2, ',', '.') }}
                            </td>
                            <td class="text-end text-danger">
                                {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['custo'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                        @foreach(($movimentacao['contas_pagar_por_grupo']['custo'] ?? []) as $conta)
                            <tr>
                                <td class="ps-4 text-muted">{{ $conta->descricao ?? $conta->nome }}</td>
                                <td class="text-end text-danger">R$
                                    {{ number_format($conta->valor_pago ?? $conta->valor_integral, 2, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        @endforeach

                        <tr class="table-secondary">
                            <td class="fw-semibold">= Lucro Bruto</td>
                            <td class="text-end fw-semibold">R$
                                {{ number_format($movimentacao['lucro_bruto'], 2, ',', '.') }}</td>
                            <td class="text-end fw-semibold">
                                {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['lucro_bruto'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>

                        <!-- Despesas -->
                        <tr class="table-group-divider">
                            <td class="text-muted">(-) Despesas com Vendas</td>
                            <td class="text-end text-danger">R$
                                {{ number_format($movimentacao['despesa_venda'], 2, ',', '.') }}</td>
                            <td class="text-end text-danger">
                                {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['despesa_venda'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                        @foreach(($movimentacao['contas_pagar_por_grupo']['despesa_venda'] ?? []) as $conta)
                            <tr>
                                <td class="ps-4 text-muted">{{ $conta->descricao ?? $conta->nome }}</td>
                                <td class="text-end text-danger">R$
                                    {{ number_format($conta->valor_pago ?? $conta->valor_integral, 2, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        @endforeach

                        <tr>
                            <td class="text-muted">(-) Despesas Administrativas</td>
                            <td class="text-end text-danger">R$
                                {{ number_format($movimentacao['despesa_adm'], 2, ',', '.') }}</td>
                            <td class="text-end text-danger">
                                {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['despesa_adm'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                        @foreach(($movimentacao['contas_pagar_por_grupo']['despesa_adm'] ?? []) as $conta)
                            <tr>
                                <td class="ps-4 text-muted">{{ $conta->descricao ?? $conta->nome }}</td>
                                <td class="text-end text-danger">R$
                                    {{ number_format($conta->valor_pago ?? $conta->valor_integral, 2, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        @endforeach

                        <tr class="table-secondary">
                            <td class="fw-semibold">= Resultado Operacional</td>
                            <td class="text-end fw-semibold">R$
                                {{ number_format($movimentacao['resultado_operacional'], 2, ',', '.') }}</td>
                            <td class="text-end fw-semibold">
                                {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['resultado_operacional'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>

                        <!-- Financeiro -->
                        <tr class="table-group-divider">
                            <td>(+) Receitas Financeiras</td>
                            <td class="text-end">R$ {{ number_format($movimentacao['receita_financeira'], 2, ',', '.') }}
                            </td>
                            <td class="text-end">
                                {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['receita_financeira'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                        @foreach(($movimentacao['contas_receber_por_grupo']['receita_financeira'] ?? []) as $conta)
                            <tr>
                                <td class="ps-4 text-muted">{{ $conta->descricao ?? $conta->nome }}</td>
                                <td class="text-end">R$
                                    {{ number_format($conta->valor_recebido ?? $conta->valor_integral, 2, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        @endforeach

                        <tr>
                            <td class="text-muted">(-) Despesas Financeiras</td>
                            <td class="text-end text-danger">R$
                                {{ number_format($movimentacao['despesa_financeira'], 2, ',', '.') }}</td>
                            <td class="text-end text-danger">
                                {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['despesa_financeira'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                        @foreach(($movimentacao['contas_pagar_por_grupo']['despesa_financeira'] ?? []) as $conta)
                            <tr>
                                <td class="ps-4 text-muted">{{ $conta->descricao ?? $conta->nome }}</td>
                                <td class="text-end text-danger">R$
                                    {{ number_format($conta->valor_pago ?? $conta->valor_integral, 2, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        @endforeach

                        <tr class="table-secondary">
                            <td class="fw-semibold">= Resultado Antes IR/CSLL</td>
                            <td class="text-end fw-semibold">R$
                                {{ number_format($movimentacao['resultado_antes_ir'], 2, ',', '.') }}</td>
                            <td class="text-end fw-semibold">
                                {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['resultado_antes_ir'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>

                        <!-- Impostos -->
                        <tr class="table-group-divider">
                            <td class="text-muted">(-) IRPJ e CSLL</td>
                            <td class="text-end text-danger">R$ {{ number_format($movimentacao['ir_csll'], 2, ',', '.') }}
                            </td>
                            <td class="text-end text-danger">
                                {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['ir_csll'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                        @foreach(($movimentacao['contas_pagar_por_grupo']['imposto_lucro'] ?? []) as $conta)
                            <tr>
                                <td class="ps-4 text-muted">{{ $conta->descricao ?? $conta->nome }}</td>
                                <td class="text-end text-danger">R$
                                    {{ number_format($conta->valor_pago ?? $conta->valor_integral, 2, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        @endforeach

                        <tr class="table-success">
                            <td class="fw-bold">= Lucro Líquido do Período</td>
                            <td class="text-end fw-bold">R$ {{ number_format($movimentacao['lucro_liquido'], 2, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold">
                                {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['lucro_liquido'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection