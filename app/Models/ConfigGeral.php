<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigGeral extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'empresa_id', 'balanca_digito_verificador', 'balanca_valor_peso', 'confirmar_itens_prevenda', 'notificacoes',
        'margem_combo', 'gerenciar_estoque', 'percentual_lucro_produto', 'tipos_pagamento_pdv', 'senha_manipula_valor',
        'abrir_modal_cartao', 'percentual_desconto_orcamento'
    ];

    public static function getNotificacoes(){
        return [
            'Contas a pagar', 'Contas a receber', 'Alerta de estoque', 'Alerta de validade', 'Ticket'
        ];
    }
}
