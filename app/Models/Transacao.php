<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transacao extends Model
{
    protected $table = 'transacoes';

    protected $fillable = [
        'codigo',
        'tipo',
        'banco',
        'descricao',
        'data',
        'valor',
    ];

    public function extratos()
    {
        return $this->belongsToMany(Extrato::class, 'extratos_transacoes', 'transacao_id', 'extrato_id');
    }

    public function conciliacoes()
    {
        return $this->hasMany(Conciliacao::class, 'transacao_id');
    }

    public function valorConciliado()
    {
        return $this->conciliacoes->sum('valor_conciliado');
    }

    public function conciliada()
    {
        return $this->conciliacoes->count() > 0;
    }
}
