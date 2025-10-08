<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ContaFinanceira extends Model
{
    use HasFactory;

    public $fillable = [
        'empresa_id',
        'nome',
        'banco',
        'agencia',
        'conta',
        'saldo_inicial',
        'saldo_atual',
    ];

    protected $table = 'contas_financeiras';

    public function conciliacoes()
    {
        return $this->hasMany(Conciliacao::class, 'conta_financeira_id');
    }

    /**
     * Calcula o saldo atual da conta financeira até uma data limite.
     *
     * @param string|\DateTime $dataLimite
     * @return float
     */
    public function calcularSaldoAtual($extratoId = null)
    {
        $saldo          = $this->saldo_inicial;
        $conciliacoes   = $this->conciliacoes();
        $extrato        = Extrato::find($extratoId);

        if ($extrato) $conciliacoes->where('extrato_id', '<=', $extrato->id);

        // Soma entradas e saídas
        $saldo += $conciliacoes->where('conciliavel_tipo', \App\Models\ContaReceber::class)->sum('valor_conciliado');
        $saldo -= $conciliacoes->where('conciliavel_tipo', \App\Models\ContaPagar::class)->sum('valor_conciliado');

        // Transferências de saída
        $saldo -= $this->transferenciasOrigem()
            ->join('transacoes', 'transferencias_contas.transacao_id', '=', 'transacoes.id')
            ->when($extrato, fn($q) => $q->whereDate('transacoes.data', '<=', $extrato->fim))
            ->sum('transacoes.valor');

        // Transferências de entrada
        $saldo += $this->transferenciasDestino()
            ->join('transacoes', 'transferencias_contas.transacao_id', '=', 'transacoes.id')
            ->when($extrato, fn($q) => $q->whereDate('transacoes.data', '<=', $extrato->fim))
            ->sum('transacoes.valor');

        return $saldo;
    }


    public function transferenciasOrigem()
    {
        return $this->hasMany(TransferenciaConta::class, 'conta_origem_id');
    }

    public function transferenciasDestino()
    {
        return $this->hasMany(TransferenciaConta::class, 'conta_destino_id');
    }
}
