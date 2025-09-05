<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroCusto extends Model
{
    use HasFactory;

    protected $table = 'centro_custos'; // Nome da tabela no banco
    protected $fillable = [
        'descricao',
        'empresa_id'
    ];
}
