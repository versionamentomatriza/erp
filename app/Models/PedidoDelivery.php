<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id', 'valor_total', 'tipo_pagamento', 'observacao',
        'telefone', 'estado', 'endereco_id', 'motivo_estado', 'troco_para', 'cupom_id', 'desconto', 'app',
        'empresa_id', 'valor_entrega', 'qr_code_base64', 'qr_code', 'transacao_id', 'status_pagamento',
        'pedido_lido', 'horario_cricao', 'horario_leitura', 'horario_entrega', 'motoboy_id', 'comissao_motoboy'
    ];

    public function itens(){
        return $this->hasMany(ItemPedidoDelivery::class, 'pedido_id')->with(['produto', 'adicionais', 'tamanho']);
    }

    public function cliente(){
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function motoboy(){
        return $this->belongsTo(Motoboy::class, 'motoboy_id');
    }

    public function empresa(){
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function cupom(){
        return $this->belongsTo(CupomDesconto::class, 'cupom_id');
    }

    public function endereco(){
        return $this->belongsTo(EnderecoDelivery::class, 'endereco_id')->with('bairro');
    }

    public function countItens(){
        return sizeof($this->itens);
    }

    public function sumTotal(){
        $total = 0;
        foreach($this->itens as $i){
            $total += $i->sub_total;
        }

        $this->valor_total = $total + $this->valor_entrega - $this->desconto;
        $this->save();
    }

    public static function estados(){
        return [
            'novo' => 'Novo',
            'aprovado' => 'Aprovado',
            'cancelado' => 'Cancelado',
            'finalizado' => 'Finalizado'
        ];
    }

    public function _estado(){
        if($this->estado == 'novo'){
            return "<h5 class='text-dark'>NOVO</h5>";
        }else if($this->estado == 'aprovado'){
            return "<h5 class='text-success'>APROVADO</h5>";
        }else if($this->estado == 'cancelado'){
            return "<h5 class='text-danger'>CANCELADO</h5>";
        }else{
            return "<h5 class='text-primary'>FINALIZADO</h5>";
        }
    }
}
