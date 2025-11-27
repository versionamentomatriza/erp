<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conciliacao extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'conciliacoes';

    protected $fillable = [
        'transacao_id',
        'extrato_id',
        'conta_financeira_id',
        'conciliavel_id',
        'conciliavel_tipo',
        'valor_conciliado',
        'data_conciliacao',
        'conta_financeira_id',
    ];

    public function transacao()
    {
        return $this->belongsTo(Transacao::class, 'transacao_id');
    }

    public function extrato()
    {
        return $this->belongsTo(Extrato::class, 'extrato_id');
    }

    public function contaFinanceira()
    {
        return $this->belongsTo(ContaFinanceira::class, 'conta_financeira_id');
    }

    public function conciliavel()
    {
        return $this->morphTo();
    }
}
