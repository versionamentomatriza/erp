@extends('layouts.app', ['title' => 'Conciliação Bancária'])

@section('title', 'Conciliação Bancária')

@section('content')
    <div class="container py-4">

        <div class="card shadow-lg border-0 p-4">
            <!-- Cabeçalho -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 mb-1">Demonstração do Resultado do Exercício (DRE)</h1>
                    <div class="text-muted small">
                        <i class="bi bi-building"></i> {{ $empresa->nome_fantasia ?? $empresa->nome }} <br>
                        <i class="bi bi-calendar3"></i> {{ $extrato->inicio }} — {{ $extrato->fim }}
                    </div>
                    <div class="text-muted small">
                        <i class="bi bi-cash-stack"></i> Saldo final do extrato:
                        <strong>R$ {{ number_format($extrato->saldo_final, 2, ',', '.') }}</strong>
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
                            <div class="fs-5 fw-semibold">R$ {{ number_format($dre['receita_liquida'], 2, ',', '.') }}</div>
                            <div class="small text-muted">Bruta - Deduções</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-none border-0">
                        <div class="card-body">
                            <div class="text-muted small">Lucro Bruto</div>
                            <div class="fs-5 fw-semibold">R$ {{ number_format($dre['lucro_bruto'], 2, ',', '.') }}</div>
                            <div class="small text-muted">Receita Líquida - Custos</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-none border-0">
                        <div class="card-body">
                            <div class="text-muted small">Resultado Operacional</div>
                            <div class="fs-5 fw-semibold">R$ {{ number_format($dre['resultado_operacional'], 2, ',', '.') }}
                            </div>
                            <div class="small text-muted">Lucro Bruto - Despesas Op.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-none border-0">
                        <div class="card-body">
                            <div class="text-muted small">Lucro Líquido</div>
                            <div class="fs-5 fw-semibold {{ $dre['lucro_liquido'] < 0 ? 'text-danger' : 'text-success' }}">
                                R$ {{ number_format($dre['lucro_liquido'], 2, ',', '.') }}
                            </div>
                            <div class="small text-muted">Resultado final do período</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela -->
            <div class="bg-white fw-semibold mb-4">Relatório DRE</div>
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
                            <td class="text-end">R$ {{ number_format($dre['receita_bruta'], 2, ',', '.') }}</td>
                            <td class="text-end">
                                {{ $dre['receita_liquida'] > 0 ? number_format(($dre['receita_bruta'] / $dre['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">(-) Deduções</td>
                            <td class="text-end text-danger">R$ {{ number_format($dre['deducao_receita'], 2, ',', '.') }}
                            </td>
                            <td class="text-end text-danger">
                                {{ $dre['receita_liquida'] > 0 ? number_format(($dre['deducao_receita'] / $dre['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                        <tr class="table-secondary">
                            <td class="fw-semibold">= Receita Líquida</td>
                            <td class="text-end fw-semibold">R$ {{ number_format($dre['receita_liquida'], 2, ',', '.') }}
                            </td>
                            <td class="text-end fw-semibold">100,00%</td>
                        </tr>

                        <!-- Custos -->
                        <tr class="table-group-divider">
                            <td class="text-muted">(-) Custos</td>
                            <td class="text-end text-danger">R$ {{ number_format($dre['custo'], 2, ',', '.') }}</td>
                            <td class="text-end text-danger">
                                {{ $dre['receita_liquida'] > 0 ? number_format(($dre['custo'] / $dre['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                        <tr class="table-secondary">
                            <td class="fw-semibold">= Lucro Bruto</td>
                            <td class="text-end fw-semibold">R$ {{ number_format($dre['lucro_bruto'], 2, ',', '.') }}</td>
                            <td class="text-end fw-semibold">
                                {{ $dre['receita_liquida'] > 0 ? number_format(($dre['lucro_bruto'] / $dre['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>

                        <!-- Despesas -->
                        <tr class="table-group-divider">
                            <td class="text-muted">(-) Despesas com Vendas</td>
                            <td class="text-end text-danger">R$ {{ number_format($dre['despesa_venda'], 2, ',', '.') }}</td>
                            <td class="text-end text-danger">
                                {{ $dre['receita_liquida'] > 0 ? number_format(($dre['despesa_venda'] / $dre['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">(-) Despesas Administrativas</td>
                            <td class="text-end text-danger">R$ {{ number_format($dre['despesa_adm'], 2, ',', '.') }}</td>
                            <td class="text-end text-danger">
                                {{ $dre['receita_liquida'] > 0 ? number_format(($dre['despesa_adm'] / $dre['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                        <tr class="table-secondary">
                            <td class="fw-semibold">= Resultado Operacional</td>
                            <td class="text-end fw-semibold">R$
                                {{ number_format($dre['resultado_operacional'], 2, ',', '.') }}
                            </td>
                            <td class="text-end fw-semibold">
                                {{ $dre['receita_liquida'] > 0 ? number_format(($dre['resultado_operacional'] / $dre['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>

                        <!-- Financeiro -->
                        <tr class="table-group-divider">
                            <td>(+) Receitas Financeiras</td>
                            <td class="text-end">R$ {{ number_format($dre['receita_financeira'], 2, ',', '.') }}</td>
                            <td class="text-end">
                                {{ $dre['receita_liquida'] > 0 ? number_format(($dre['receita_financeira'] / $dre['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">(-) Despesas Financeiras</td>
                            <td class="text-end text-danger">R$ {{ number_format($dre['despesa_financeira'], 2, ',', '.') }}
                            </td>
                            <td class="text-end text-danger">
                                {{ $dre['receita_liquida'] > 0 ? number_format(($dre['despesa_financeira'] / $dre['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                        <tr class="table-secondary">
                            <td class="fw-semibold">= Resultado Antes IR/CSLL</td>
                            <td class="text-end fw-semibold">R$ {{ number_format($dre['resultado_antes_ir'], 2, ',', '.') }}
                            </td>
                            <td class="text-end fw-semibold">
                                {{ $dre['receita_liquida'] > 0 ? number_format(($dre['resultado_antes_ir'] / $dre['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>

                        <!-- Impostos -->
                        <tr class="table-group-divider">
                            <td class="text-muted">(-) IRPJ e CSLL</td>
                            <td class="text-end text-danger">R$ {{ number_format($dre['ir_csll'], 2, ',', '.') }}</td>
                            <td class="text-end text-danger">
                                {{ $dre['receita_liquida'] > 0 ? number_format(($dre['ir_csll'] / $dre['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                        <tr class="table-success">
                            <td class="fw-bold">= Lucro Líquido do Período</td>
                            <td class="text-end fw-bold">R$ {{ number_format($dre['lucro_liquido'], 2, ',', '.') }}</td>
                            <td class="text-end fw-bold">
                                {{ $dre['receita_liquida'] > 0 ? number_format(($dre['lucro_liquido'] / $dre['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection