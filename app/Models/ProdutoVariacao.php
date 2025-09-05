<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoVariacao extends Model
{
    use HasFactory;

    protected $fillable = [
        'produto_id', 'descricao', 'valor', 'codigo_barras', 'referencia', 'imagem'
    ];

    public function produto(){
        return $this->belongsTo(Produto::class, 'produto_id');
    }
    
    public function getImgAttribute()
    {
        if($this->imagem == ""){
            return "/imgs/no-image.png";
        }
        return "/uploads/produtos/$this->imagem";
    }

}
