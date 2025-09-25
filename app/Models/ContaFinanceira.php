<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function conciliacoes(){
        return $this->hasMany(Conciliacao::class, 'conta_financeira_id');
    }
}
