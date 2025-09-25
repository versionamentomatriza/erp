<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaFinanceira extends Model
{
    use HasFactory;

    protected $table = 'contas_financeiras';

    public function conciliacoes(){
        return $this->hasMany(Conciliacao::class, 'conta_financeira_id');
    }
}
