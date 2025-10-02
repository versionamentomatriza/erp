<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extrato extends Model
{
    use HasFactory;

    protected $fillable = [
        'banco',
        'inicio',
        'fim',
        'saldo_inicial',
        'saldo_final',
        'empresa_id',
        'status',
    ];

    public function transacoes()
    {
        return $this->belongsToMany(Transacao::class, 'extratos_transacoes', 'extrato_id', 'transacao_id');
    }

    public function conciliacoes()
    {
        return $this->hasMany(Conciliacao::class, 'extrato_id');
    }

    public function finalizar()
    {
        $this->status = 'conciliado';
        $this->save();
    }

    public function calcularSaldoConciliado()
    {
        $saldo = $this->saldo_inicial;

        foreach ($this->conciliacoes as $conciliacao) {
            $valor = (float) $conciliacao->valor_conciliado;

            if ($conciliacao->conciliavel_tipo === \App\Models\ContaReceber::class) {
                $saldo += $valor;
            } elseif ($conciliacao->conciliavel_tipo === \App\Models\ContaPagar::class) {
                $saldo -= $valor;
            }
        }

        return $saldo;
    }

    public function calcularSaldosPorContaEmpresa()
    {
        $saldos = [];

        foreach ($this->conciliacoes as $conciliacao) {
            $contaEmpresaId = $conciliacao->conta_empresa_id;

            if (!isset($saldos[$contaEmpresaId])) {
                $saldos[$contaEmpresaId] = 0.0;
            }

            $valor = (float) $conciliacao->valor_conciliado;

            if ($conciliacao->conciliavel_tipo === \App\Models\ContaReceber::class) {
                $saldos[$contaEmpresaId] += $valor;
            } elseif ($conciliacao->conciliavel_tipo === \App\Models\ContaPagar::class) {
                $saldos[$contaEmpresaId] -= $valor;
            }
        }

        return $saldos; // array: [conta_empresa_id => saldo_conciliado]
    }
}
