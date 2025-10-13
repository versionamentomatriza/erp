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
                <td class="text-end fw-semibold">R$ {{ number_format($movimentacao['receita_bruta'], 2, ',', '.') }}</td>
                <td class="text-end fw-semibold">
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
                <td class="fw-semibold">(+ Outras Receitas)</td>
                <td class="text-end fw-semibold">
                    R$ {{ number_format($movimentacao['sum_receber_por_grupo']['outras_receitas'] ?? 0, 2, ',', '.') }}
                </td>
                <td class="text-end fw-semibold">
                    {{ $movimentacao['receita_liquida'] > 0 
                        ? number_format((($movimentacao['sum_receber_por_grupo']['outras_receitas'] ?? 0) / $movimentacao['receita_liquida']) * 100, 2, ',', '.') 
                        : '0,00' }}%
                </td>
            </tr>

            @foreach(($movimentacao['contas_receber_por_grupo']['outras_receitas'] ?? []) as $conta)
                <tr>
                    <td class="ps-4 text-muted">{{ $conta->descricao ?? $conta->nome }}</td>
                    <td class="text-end">R$
                        {{ number_format($conta->valor_recebido ?? $conta->valor_integral, 2, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            @endforeach

            <tr>
                <td class="text-muted fw-semibold">(-) Deduções</td>
                <td class="text-end text-danger fw-semibold">R$
                    {{ number_format($movimentacao['deducao_receita'], 2, ',', '.') }}</td>
                <td class="text-end text-danger fw-semibold">
                    {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['deducao_receita'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                </td>
            </tr>

            @foreach(($movimentacao['contas_pagar_por_grupo']['deducao_receita'] ?? []) as $conta)
                <tr>
                    <td class="ps-4 text-muted">{{ $conta->descricao ?? $conta->nome }}</td>
                    <td class="text-end">R$
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
                <td class="text-muted fw-semibold">(-) Custos</td>
                <td class="text-end text-danger fw-semibold">R$ {{ number_format($movimentacao['custo'], 2, ',', '.') }}
                </td>
                <td class="text-end text-danger fw-semibold">
                    {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['custo'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                </td>
            </tr>
        
            @foreach(($movimentacao['contas_pagar_por_grupo']['custo'] ?? []) as $conta)
                <tr>
                    <td class="ps-4 text-muted">{{ $conta->descricao ?? $conta->nome }}</td>
                    <td class="text-end">R$
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
                <td class="text-muted fw-semibold">(-) Despesas com Vendas</td>
                <td class="text-end text-danger fw-semibold">R$
                    {{ number_format($movimentacao['despesa_venda'], 2, ',', '.') }}</td>
                <td class="text-end text-danger fw-semibold">
                    {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['despesa_venda'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                </td>
            </tr>

            @foreach(($movimentacao['contas_pagar_por_grupo']['despesa_venda'] ?? []) as $conta)
                <tr>
                    <td class="ps-4 text-muted">{{ $conta->descricao ?? $conta->nome }}</td>
                    <td class="text-end">R$
                        {{ number_format($conta->valor_pago ?? $conta->valor_integral, 2, ',', '.') }}</td>
                    <td></td>
                </tr>
            @endforeach

            <tr>
                <td class="text-muted fw-semibold">(-) Despesas Administrativas</td>
                <td class="text-end text-danger fw-semibold">R$
                    {{ number_format($movimentacao['despesa_adm'], 2, ',', '.') }}</td>
                <td class="text-end text-danger fw-semibold">
                    {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['despesa_adm'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                </td>
            </tr>

            @foreach(($movimentacao['contas_pagar_por_grupo']['despesa_adm'] ?? []) as $conta)
                <tr>
                    <td class="ps-4 text-muted">{{ $conta->descricao ?? $conta->nome }}</td>
                    <td class="text-end">R$
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
                <td class="fw-semibold">(+) Receitas Financeiras</td>
                <td class="text-end fw-semibold">R$ {{ number_format($movimentacao['receita_financeira'], 2, ',', '.') }}
                </td>
                <td class="text-end fw-semibold">
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
                <td class="text-muted fw-semibold">(-) Despesas Financeiras</td>
                <td class="text-end text-danger fw-semibold">R$
                    {{ number_format($movimentacao['despesa_financeira'], 2, ',', '.') }}</td>
                <td class="text-end text-danger fw-semibold">
                    {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['despesa_financeira'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                </td>
            </tr>

            @foreach(($movimentacao['contas_pagar_por_grupo']['despesa_financeira'] ?? []) as $conta)
                <tr>
                    <td class="ps-4 text-muted">{{ $conta->descricao ?? $conta->nome }}</td>
                    <td class="text-end">R$
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
                <td class="text-muted fw-semibold">(-) IRPJ e CSLL</td>
                <td class="text-end text-danger fw-semibold">R$ {{ number_format($movimentacao['ir_csll'], 2, ',', '.') }}
                </td>
                <td class="text-end text-danger fw-semibold">
                    {{ $movimentacao['receita_liquida'] > 0 ? number_format(($movimentacao['ir_csll'] / $movimentacao['receita_liquida']) * 100, 2, ',', '.') : '0,00' }}%
                </td>
            </tr>

            @foreach(($movimentacao['contas_pagar_por_grupo']['imposto_lucro'] ?? []) as $conta)
                <tr>
                    <td class="ps-4 text-muted">{{ $conta->descricao ?? $conta->nome }}</td>
                    <td class="text-end">R$
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