<?php

namespace App\Services;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\ExtratoTransacao;
use App\Models\Transacao;
use Illuminate\Support\Collection;

class ExtratoService
{
    public static function gerarDRE($extrato)
    {
        $extratoId = $extrato->id;

        $contasReceber = ContaReceber::where('empresa_id', $extrato->empresa_id)
            ->whereHas('conciliacoes.transacao.extratos', function ($q) use ($extratoId) {
                $q->where('extratos.id', $extratoId);
            })
            ->get();

        $contasPagar = ContaPagar::where('empresa_id', $extrato->empresa_id)
            ->whereHas('conciliacoes.transacao.extratos', function ($q) use ($extratoId) {
                $q->where('extratos.id', $extratoId);
            })
            ->get();

        $rec = self::somarGrupo($contasReceber, 'valor_recebido', 'valor_integral');
        $pag = self::somarGrupo($contasPagar,   'valor_pago',     'valor_integral');

        $get = fn($map, $k) => (float)($map[$k] ?? 0);

        // Blocos DRE
        $receita_bruta       = $get($rec, 'receita_bruta');                          
        $deducao_receita     = $get($pag, 'deducao_receita') + $get($rec, 'deducao_receita'); // pode estar em pagar (impostos sobre vendas)
        $custo               = $get($pag, 'custo');
        $despesa_venda       = $get($pag, 'despesa_venda');
        $despesa_adm         = $get($pag, 'despesa_adm');
        $receita_financeira  = $get($rec, 'receita_financeira');
        $despesa_financeira  = $get($pag, 'despesa_financeira');

        // Resultados
        $receita_liquida         = $receita_bruta - $deducao_receita;
        $lucro_bruto             = $receita_liquida - $custo;
        $resultado_operacional   = $lucro_bruto - $despesa_venda - $despesa_adm;
        $resultado_financeiro    = $receita_financeira - $despesa_financeira;
        $resultado_antes_ir      = $resultado_operacional + $resultado_financeiro;

        $ir_csll = $get($pag, 'imposto_lucro');

        $lucro_liquido = $resultado_antes_ir - $ir_csll;

        return [
            'receita_bruta'         => $receita_bruta,
            'deducao_receita'       => $deducao_receita,
            'receita_liquida'       => $receita_liquida,
            'custo'                 => $custo,
            'lucro_bruto'           => $lucro_bruto,
            'despesa_venda'         => $despesa_venda,
            'despesa_adm'           => $despesa_adm,
            'resultado_operacional' => $resultado_operacional,
            'receita_financeira'    => $receita_financeira,
            'despesa_financeira'    => $despesa_financeira,
            'resultado_antes_ir'    => $resultado_antes_ir,
            'ir_csll'               => $ir_csll,
            'lucro_liquido'         => $lucro_liquido,
            // úteis para detalhamento por grupo na tela:
            'sum_receber_por_grupo' => $rec,
            'sum_pagar_por_grupo'   => $pag,
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

            // 🔹 firstOrCreate evita 2 queries por transação
            $t = Transacao::firstOrCreate($dados, $dados);
            ExtratoTransacao::firstOrCreate([
                'extrato_id' => $extratoId,
                'transacao_id' => $t->id,
            ]);

            $arr->push($t);
        }

        // 🔹 Ordena por 'data' decrescente antes de retornar
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
}
