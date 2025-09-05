<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('config_gerals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->enum('balanca_valor_peso', ['peso', 'valor']);
            $table->integer('balanca_digito_verificador')->nullable();
            $table->boolean('confirmar_itens_prevenda')->default(0);
            $table->boolean('gerenciar_estoque')->default(0);
            $table->text('notificacoes');
            $table->decimal('margem_combo', 5,2)->default(50);
            $table->decimal('percentual_desconto_orcamento', 5,2)->nullable();
            $table->decimal('percentual_lucro_produto', 10,2)->default(50);

            $table->text('tipos_pagamento_pdv');
            $table->string('senha_manipula_valor', 20)->nullable();
            $table->boolean('abrir_modal_cartao')->default(1);

            // alter table config_gerals add column confirmar_itens_prevenda boolean default 0;
            // alter table config_gerals modify column balanca_digito_verificador integer default null;
            // alter table config_gerals add column notificacoes text;
            // alter table config_gerals add column margem_combo decimal(5,2) default 50;
            // alter table config_gerals add column gerenciar_estoque boolean default 0;
            // alter table config_gerals add column percentual_lucro_produto decimal(10,2) default 0;
            // alter table config_gerals add column tipos_pagamento_pdv text;
            // alter table config_gerals add column senha_manipula_valor varchar(20) default null;
            // alter table config_gerals add column abrir_modal_cartao boolean default 1;

            // alter table config_gerals add column percentual_desconto_orcamento decimal(5,2) default null;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_gerals');
    }
};
