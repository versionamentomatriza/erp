<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'razao_social', 'nome_fantasia', 'cpf_cnpj', 'ie', 'contribuinte', 'consumidor_final',
        'email', 'telefone', 'cidade_id', 'rua', 'cep', 'numero', 'bairro', 'complemento'
    ];

    protected $appends = [ 'endereco', 'info' ];

    public function getInfoAttribute()
    {
        return "$this->razao_social $this->cpf_cnpj";
    }
    
    public function getEnderecoAttribute()
    {
        return "$this->rua, $this->numero - $this->bairro";
    }

    public function cidade(){
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }
}
