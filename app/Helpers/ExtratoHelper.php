<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

if (!function_exists('buscarContasPorExtrato')) {
    /**
     * Busca contas (pagar ou receber) do mês do extrato + pendentes do mês anterior.
     *
     * @param string $modelo        Classe do modelo (ex: ContaPagar::class ou ContaReceber::class)
     * @param int $empresaId        ID da empresa
     * @param \App\Models\Extrato $extrato  Extrato com inicio/fim do mês
     * @return Collection
     */
    function buscarContasPorExtrato(string $modelo, int $empresaId, $extrato): Collection
    {
        $mesAnterior = Carbon::parse($extrato->inicio)->subMonth();

        return $modelo::where('empresa_id', $empresaId)
            ->where(function ($query) use ($extrato, $mesAnterior) {
                $query->whereBetween('data_vencimento', [$extrato->inicio, $extrato->fim]) // mês do extrato
                    ->orWhere(function ($q) use ($mesAnterior) {
                        $q->whereBetween('data_vencimento', [
                            $mesAnterior->copy()->startOfMonth()->toDateString(),
                            $mesAnterior->copy()->endOfMonth()->toDateString(),
                        ])
                            ->where('status', 0); // apenas em aberto
                    });
            })
            ->orderBy('id', 'desc')
            ->get();
    }
}
