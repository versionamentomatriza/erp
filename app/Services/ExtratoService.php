<?php

namespace App\Services;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Empresa;
use App\Models\Extrato;
use App\Models\ExtratoTransacao;
use App\Models\Transacao;
use Illuminate\Support\Collection;

class ExtratoService
{
    public static function gerarDRE($empresa, $inicio, $fim)
    {
        $extratos = Extrato::where('empresa_id', $empresa->id)
            ->whereDate('inicio', '>=', $inicio)
            ->whereDate('fim', '<=', $fim)
            ->get();

        // Aceita um único extrato ou vários
        $extratos = $extratos instanceof Collection
            ? $extratos
            : collect([$extratos]);
            
        $empresaId  = $request->empresa_id ?? auth()->user()->empresa_id;

        // ==========================================================
        // BUSCA DE CONTAS RELACIONADAS
        // ==========================================================
        $contasReceber = ContaReceber::where('empresa_id', $empresaId)
            ->whereBetween('data_competencia', [$inicio, $fim])
            ->get();

        $contasPagar = ContaPagar::where('empresa_id', $empresaId)
            ->whereBetween('data_competencia', [$inicio, $fim])
            ->get();

        // ==========================================================
        // AGRUPAMENTO E SOMA
        // ==========================================================
        $rec = self::somarGrupo($contasReceber, 'valor_recebido', 'valor_integral');
        $pag = self::somarGrupo($contasPagar,   'valor_pago',     'valor_integral');

        $get = fn($map, $k) => isset($map[$k]) ? (float)$map[$k] : 0.0;

        // ==========================================================
        // BLOCO DRE
        // ==========================================================
        $receita_bruta       = $get($rec, 'receita_bruta') + $get($rec, 'outras_receitas');
        $deducao_receita     = $get($pag, 'deducao_receita') + $get($rec, 'deducao_receita');
        $custo               = $get($pag, 'custo');
        $despesa_venda       = $get($pag, 'despesa_venda');
        $despesa_adm         = $get($pag, 'despesa_adm');
        $receita_financeira  = $get($rec, 'receita_financeira');
        $despesa_financeira  = $get($pag, 'despesa_financeira');
        $ir_csll             = $get($pag, 'imposto_lucro');

        // ==========================================================
        // RESULTADOS
        // ==========================================================
        $receita_liquida         = $receita_bruta - $deducao_receita;
        $lucro_bruto             = $receita_liquida - $custo;
        $resultado_operacional   = $lucro_bruto - $despesa_venda - $despesa_adm;
        $resultado_financeiro    = $receita_financeira - $despesa_financeira;
        $resultado_antes_ir      = $resultado_operacional + $resultado_financeiro;
        $lucro_liquido           = $resultado_antes_ir - $ir_csll;

        // ==========================================================
        // MARGENS (%)
        // ==========================================================
        $margem_bruta   = $receita_liquida != 0 ? ($lucro_bruto / $receita_liquida) * 100 : 0;
        $margem_liquida = $receita_liquida != 0 ? ($lucro_liquido / $receita_liquida) * 100 : 0;

        // ==========================================================
        // RETORNO
        // ==========================================================
        return [
            'receita_bruta'             => $receita_bruta,
            'deducao_receita'           => $deducao_receita,
            'receita_liquida'           => $receita_liquida,
            'custo'                     => $custo,
            'lucro_bruto'               => $lucro_bruto,
            'despesa_venda'             => $despesa_venda,
            'despesa_adm'               => $despesa_adm,
            'resultado_operacional'     => $resultado_operacional,
            'receita_financeira'        => $receita_financeira,
            'despesa_financeira'        => $despesa_financeira,
            'resultado_antes_ir'        => $resultado_antes_ir,
            'ir_csll'                   => $ir_csll,
            'lucro_liquido'             => $lucro_liquido,
            'margem_bruta'              => round($margem_bruta, 2),
            'margem_liquida'            => round($margem_liquida, 2),
            'contas_receber_por_grupo'  => self::agruparContasPorGrupo($contasReceber),
            'contas_pagar_por_grupo'    => self::agruparContasPorGrupo($contasPagar),
            'sum_receber_por_grupo'     => $rec,
            'sum_pagar_por_grupo'       => $pag,
        ];
    }

    public static function gerarFluxoCaixa($extratos)
    {
        // Aceita um único extrato ou vários
        $extratos = $extratos instanceof Collection
            ? $extratos
            : collect([$extratos]);

        $extratoIds = $extratos->pluck('id');
        $empresaId  = $extratos->first()->empresa_id;

        $inicio = $extratos->min(fn($e) => strtotime($e->inicio));
        $fim    = $extratos->max(fn($e) => strtotime($e->fim));

        $periodo = [
            'inicio' => $inicio ? date('d/m/Y', $inicio) : null,
            'fim'    => $fim    ? date('d/m/Y', $fim) : null,
        ];

        // ==========================================================
        // BUSCA DE CONTAS RELACIONADAS
        // ==========================================================
        $contasReceber = ContaReceber::where('empresa_id', $empresaId)
            ->whereHas('conciliacoes.transacao.extratos', function ($q) use ($extratoIds) {
                $q->whereIn('extratos.id', $extratoIds);
            })
            ->get();

        $contasPagar = ContaPagar::where('empresa_id', $empresaId)
            ->whereHas('conciliacoes.transacao.extratos', function ($q) use ($extratoIds) {
                $q->whereIn('extratos.id', $extratoIds);
            })
            ->get();

        // ==========================================================
        // DEFINIÇÃO DE CATEGORIAS
        // ==========================================================
        $categorias = [
            'Receitas' => [
                'tipo' => 'entrada',
                'grupos' => ['receita_bruta', 'receita_financeira', 'outras_receitas'],
                'contas' => $contasReceber->sortBy(fn($c) => $c->data_recebimento ?? now())
            ],
            'Custos' => [
                'tipo' => 'saida',
                'grupos' => ['custo'],
                'contas' => $contasPagar->filter(
                    fn($c) =>
                    $c->categoriaConta?->grupo_dre === 'custo'
                )->sortBy(fn($c) => $c->data_pagamento ?? now())
            ],
            'Despesas Operacionais' => [
                'tipo' => 'saida',
                'grupos' => ['despesa_venda', 'despesa_adm'],
                'contas' => $contasPagar->filter(
                    fn($c) =>
                    in_array($c->categoriaConta?->grupo_dre, ['despesa_venda', 'despesa_adm'])
                )->sortBy(fn($c) => $c->data_pagamento ?? now())
            ],
            'Despesas Financeiras' => [
                'tipo' => 'saida',
                'grupos' => ['despesa_financeira'],
                'contas' => $contasPagar->filter(
                    fn($c) =>
                    $c->categoriaConta?->grupo_dre === 'despesa_financeira'
                )->sortBy(fn($c) => $c->data_pagamento ?? now())
            ],
            'Impostos' => [
                'tipo' => 'saida',
                'grupos' => ['imposto_lucro', 'deducao_receita'],
                'contas' => $contasPagar->filter(
                    fn($c) =>
                    in_array($c->categoriaConta?->grupo_dre, ['imposto_lucro', 'deducao_receita'])
                )->sortBy(fn($c) => $c->data_pagamento ?? now())
            ],
            'Outras Despesas' => [
                'tipo' => 'saida',
                'grupos' => [],
                'contas' => $contasPagar->filter(
                    fn($c) =>
                    !in_array($c->categoriaConta?->grupo_dre, [
                        'receita_bruta',
                        'outras_receitas',
                        'receita_financeira',
                        'deducao_receita',
                        'custo',
                        'despesa_venda',
                        'despesa_adm',
                        'despesa_financeira',
                        'imposto_lucro'
                    ])
                )->sortBy(fn($c) => $c->data_pagamento ?? now())
            ],
        ];

        // ==========================================================
        // SOMA DOS VALORES
        // ==========================================================
        foreach ($categorias as $nome => &$cat) {
            $cat['total'] = $cat['contas']->sum(function ($c) {
                return $c instanceof ContaReceber
                    ? $c->valor_recebido ?? $c->valor_integral
                    : ($c->valor_pago ?? $c->valor_integral);
            });
        }
        unset($cat);

        $totalEntradas = collect($categorias)
            ->where('tipo', 'entrada')
            ->sum('total');

        $totalSaidas = collect($categorias)
            ->where('tipo', 'saida')
            ->sum('total');

        $saldoFinal = $totalEntradas - $totalSaidas;

        // ==========================================================
        // RETORNO
        // ==========================================================
        return [
            'categorias' => $categorias,
            'total_entradas' => $totalEntradas,
            'total_saidas' => $totalSaidas,
            'saldo_final' => $saldoFinal,
            'periodo' => $periodo
        ];
    }

    public static function criarTransacoes(array $transacoes, $extratoId): Collection
    {
        $arr = collect();

        foreach ($transacoes as $transacao) {
            if (abs($transacao['valor']) <= 0) continue;

            $dados = [
                'codigo'    => $transacao['id'],
                'tipo'      => $transacao['tipo'],
                'banco'     => $transacao['banco'],
                'descricao' => $transacao['descricao'] ?? null,
                'data'      => $transacao['data'],
                'valor'     => abs($transacao['valor']),
            ];

            $t = Transacao::firstOrCreate($dados, $dados);

            ExtratoTransacao::firstOrCreate([
                'extrato_id'   => $extratoId,
                'transacao_id' => $t->id,
            ]);

            $arr->push($t);
        }

        return $arr->sortByDesc('data')->values();
    }

    private static function somarGrupo($collection, $campoPreferencial, $campoFallback = null)
    {
        return $collection
            ->groupBy(fn($i) => optional($i->categoriaConta)->grupo_dre ?? 'nao_classificado')
            ->map(function ($itens) use ($campoPreferencial, $campoFallback) {
                return $itens->sum(function ($i) use ($campoPreferencial, $campoFallback) {
                    $v = $i->{$campoPreferencial};
                    if ($v === null || (float)$v == 0.0) {
                        $v = $campoFallback ? $i->{$campoFallback} : 0;
                    }
                    return (float)$v;
                });
            });
    }

    private static function agruparContasPorGrupo($collection)
    {
        return $collection
            ->groupBy(fn($i) => optional($i->categoriaConta)->grupo_dre ?? 'nao_classificado');
    }
}
