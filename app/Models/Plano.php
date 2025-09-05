<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plano extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'descricao', 'maximo_nfes', 'maximo_nfces', 'imagem', 'visivel_clientes',
        'status', 'valor', 'intervalo_dias', 'maximo_ctes', 'maximo_cte_os', 'maximo_mdfes', 'modulos',
        'visivel_contadores', 'auto_cadastro', 'segmento_id', 'fiscal', 'valor_implantacao'
    ];

    public function getImgAttribute()
    {
        if($this->imagem == ""){
            return "/imgs/no-image.png";
        }
        return "/uploads/planos/$this->imagem";
    }

    public static function formasPagamento(){
        return [
            'dinheiro' => 'Dinheiro',
            'cartao_credito' => 'Cartão de Crédito',
            'cartao_debito' => 'Cartão de Débito',
            'pix' => 'PIX',
			'boleto' => 'Boleto'
        ];
    }

    public function segmento(){
        return $this->belongsTo(Segmento::class, 'segmento_id');
    }
}
