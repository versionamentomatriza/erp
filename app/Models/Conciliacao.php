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
        'conciliavel_id',
        'conciliavel_type',
        'valor_conciliado',
        'data_conciliacao',
    ];

    public function transacao()
    {
        return $this->belongsTo(Transacao::class, 'transacao_id');
    }

    public function extrato()
    {
        return $this->belongsTo(Extrato::class, 'extrato_id');
    }

    public function conciliavel()
    {
        return $this->morphTo();
    }
}
