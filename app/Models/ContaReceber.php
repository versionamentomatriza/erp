<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaReceber extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'nfe_id', 'nfce_id', 'cliente_id', 'descricao', 'valor_integral', 'valor_recebido', 'data_vencimento',
        'data_recebimento', 'status', 'observacao', 'tipo_pagamento', 'caixa_id', 'local_id', 'arquivo','centro_custo_id'
    ];

    protected $appends = [ 'info' ];

    public function getInfoAttribute()
    {
        return "Cliente: " . $this->cliente->info . " - valor: R$ " . __moeda($this->valor_integral) . ", vencimento: " . __data_pt($this->data_vencimento, 0);
    }

    public function localizacao()
    {
        return $this->belongsTo(Localizacao::class, 'local_id');
    }
    
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function boleto()
    {
        return $this->hasOne(Boleto::class, 'conta_receber_id');
    }

    public static function tiposPagamento()
    {
        return [
            '01' => 'Dinheiro',
            '02' => 'Cheque',
            '03' => 'Cartão de Crédito',
            '04' => 'Cartão de Débito',
            '05' => 'Crédito Loja',
            '06' => 'Crediário',
            '10' => 'Vale Alimentação',
            '11' => 'Vale Refeição',
            '12' => 'Vale Presente',
            '13' => 'Vale Combustível',
            '14' => 'Duplicata Mercantil',
            '15' => 'Boleto Bancário',
            '16' => 'Depósito Bancário',
            '17' => 'Pagamento Instantâneo (PIX)',
            '90' => 'Sem Pagamento',
            '99' => 'Outros',
        ];
    }

    public function diasAtraso(){
        $d = date('Y-m-d');
        $d2 = $this->data_vencimento;
        $dif = strtotime($d2) - strtotime($d);
        $dias = floor($dif / (60 * 60 * 24));
        if($dias == 0){
            return "conta vence hoje";
        }

        if($dias > 0){
            return "$dias dia(s) para o vencimento";
        }else{
            return "conta vencida à " . ($dias*-1) . " dia(s)";
        }
    } 
    public function getStatus()
{
    return match ($this->status) {
        0 => 'Pendente',
        1 => 'Pago',
        default => 'Desconhecido',
    };
}
     public function centroCusto()
    {
        return $this->belongsTo(\App\Models\CentroCusto::class, 'centro_custo_id');
    }

}
