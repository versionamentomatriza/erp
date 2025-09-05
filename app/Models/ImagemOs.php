<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;




class ImagemOs extends Model
{
    use HasFactory;

    protected $table = 'imagens_os';

    protected $fillable = [
        'usuario_id',
        'ordem_servico_id',
        'arquivo',
    ];
	

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Retorna a URL completa da imagem armazenada.
     */
    public function getImagemUrlAttribute()
    {
        return asset('storage/' . $this->arquivo);
    }
}
