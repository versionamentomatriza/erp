<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Localizacao extends Model
{
    use HasFactory;

    protected $fillable = [
        'descricao', 'nome', 'nome_fantasia', 'cpf_cnpj', 'ie', 'email', 'celular', 'csc', 'csc_id', 'arquivo', 
        'senha', 'status', 'cep', 'rua', 'numero', 'bairro', 'complemento', 'cidade_id', 'tributacao', 
        'numero_ultima_nfe_producao', 'numero_ultima_nfe_homologacao', 'numero_serie_nfe',  
        'numero_ultima_nfce_producao', 'numero_ultima_nfce_homologacao', 'numero_serie_nfce', 'ambiente',
        'numero_ultima_cte_producao', 'numero_ultima_cte_homologacao', 'numero_serie_cte',
        'numero_ultima_mdfe_producao', 'numero_ultima_mdfe_homologacao', 'numero_serie_mdfe', 'logo',
        'token_nfse', 'numero_ultima_nfse', 'numero_serie_nfse', 'aut_xml', 'empresa_id'
    ];

    public function cidade(){
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }

    public function empresa(){
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function getEnderecoAttribute()
    {
        return "$this->rua, $this->numero - $this->bairro";
    }

}
