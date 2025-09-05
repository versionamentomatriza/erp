<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemServicoNfce extends Model
{
    use HasFactory;

    protected $fillable = [
        'nfce_id', 'servico_id', 'quantidade', 'valor_unitario', 'sub_total', 'observacao'
    ];

    public function servico(){
        return $this->belongsTo(Servico::class, 'servico_id');
    }
}
