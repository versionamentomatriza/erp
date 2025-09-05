<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transportadora extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'empresa_id', 'razao_social', 'nome_fantasia', 'cpf_cnpj', 'ie', 'email', 'telefone', 'cidade_id',
        'rua', 'cep', 'numero', 'bairro', 'complemento', 'antt', 
    ];

    public function cidade(){
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }
}
