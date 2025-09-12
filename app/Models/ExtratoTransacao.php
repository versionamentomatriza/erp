<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtratoTransacao extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'extratos_transacoes';

    protected $fillable = [
        'extrato_id',
        'transacao_id',
    ];
}
