<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferenciaConta extends Model
{
    use HasFactory;

    protected $table = 'transferencias_contas';

    protected $fillable = [
        'conta_origem_id',
        'conta_destino_id',
        'transacao_id',
        'empresa_id',
    ];

    // 🔗 Relações

    public function contaOrigem()
    {
        return $this->belongsTo(ContaFinanceira::class, 'conta_origem_id');
    }

    public function contaDestino()
    {
        return $this->belongsTo(ContaFinanceira::class, 'conta_destino_id');
    }

    public function transacao()
    {
        return $this->belongsTo(Transacao::class, 'transacao_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
