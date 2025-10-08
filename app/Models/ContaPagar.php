<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaPagar extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'nfe_id',
        'fornecedor_id',
        'descricao',
        'valor_integral',
        'valor_pago',
        'data_vencimento',
        'data_pagamento',
        'status',
        'observacao',
        'tipo_pagamento',
        'caixa_id',
        'local_id',
        'arquivo',
        'centro_custo_id',
        'categoria_conta_id'
    ];

    public function categoriaConta()
    {
        return $this->belongsTo(CategoriaConta::class, 'categoria_conta_id');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function localizacao()
    {
        return $this->belongsTo(Localizacao::class, 'local_id');
    }

    public function diasAtraso()
    {
        $d = date('Y-m-d');
        $d2 = $this->data_vencimento;
        $dif = strtotime($d2) - strtotime($d);
        $dias = floor($dif / (60 * 60 * 24));

        if ($dias == 0) {
            return "conta vence hoje";
        }

        if ($dias > 0) {
            return "$dias dia(s) para o vencimento";
        } else {
            return "conta vencida à " . ($dias * -1) . " dia(s)";
        }
    }
    public function centroCusto()
    {
        return $this->belongsTo(\App\Models\CentroCusto::class, 'centro_custo_id');
    }

    public function conciliacoes()
    {
        return $this->morphMany(Conciliacao::class, 'conciliavel', 'conciliavel_tipo', 'conciliavel_id');
    }

    public function conciliada()
    {
        return $this->conciliacoes()->exists();
    }

    public function valorConciliado()
    {
        $valor = 0;
        foreach ($this->conciliacoes as $conciliacao) $valor += $conciliacao->transacao->valor;
        return $valor;
    }
}
