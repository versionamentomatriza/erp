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
        'saldo_final',
        'empresa_id',
        'status',
    ];

    public function transacoes()
    {
        return $this->belongsToMany(Transacao::class, 'extrato_transacao');
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
}
