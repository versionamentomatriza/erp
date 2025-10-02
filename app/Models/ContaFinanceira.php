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
    public function calcularSaldoAtual($dataLimite = null)
    {
        $dataLimite = $dataLimite ? Carbon::parse($dataLimite) : Carbon::now();

        // Define os tipos de conciliáveis usando o morph map
        $tipos = [
            'conta_pagar' => \App\Models\ContaPagar::class,
            'conta_receber' => \App\Models\ContaReceber::class,
        ];

        // Soma das contas a pagar (subtrai do saldo)
        $totalPagar = $this->conciliacoes()
            ->where('conciliavel_tipo', $tipos['conta_pagar'])
            ->whereDate('data_conciliacao', '<=', $dataLimite)
            ->sum('valor_conciliado');

        // Soma das contas a receber (adiciona ao saldo)
        $totalReceber = $this->conciliacoes()
            ->where('conciliavel_tipo', $tipos['conta_receber'])
            ->whereDate('data_conciliacao', '<=', $dataLimite)
            ->sum('valor_conciliado');

        // Saldo final = saldo inicial + totalReceber - totalPagar
        return $this->saldo_inicial + $totalReceber - $totalPagar;
    }
}
