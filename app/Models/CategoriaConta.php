<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaConta extends Model
{
    use HasFactory;

    protected $table = 'categoria_contas';

    public $timestamps = false;

    /**
     * Relacionamento: uma categoria pode ter várias contas a pagar.
     */
    public function contasPagar()
    {
        return $this->hasMany(ContaPagar::class, 'categoria_conta_id');
    }

    /**
     * Relacionamento: uma categoria pode ter várias contas a receber.
     */
    public function contasReceber()
    {
        return $this->hasMany(ContaReceber::class, 'categoria_conta_id');
    }
}
