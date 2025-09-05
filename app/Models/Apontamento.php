<?php

namespace App\Models;

use App\Models\Produto;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apontamento extends Model
{
    use HasFactory;

    protected $fillable = ['produto_id', 'quantidade'];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
