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
        Schema::create('configuracao_supers', function (Blueprint $table) {
            $table->id();
            
            $table->string('cpf_cnpj', 20);
            $table->string('name');
            $table->string('email')->unique();
            $table->string('telefone', 20);

            $table->string('mercadopago_public_key', 120)->nullable();
            $table->string('mercadopago_access_token', 120)->nullable();
            $table->string('sms_key', 120)->nullable();
            $table->string('token_whatsapp', 120)->nullable();

            $table->string('usuario_correios', 30)->nullable();
            $table->string('codigo_acesso_correios', 100)->nullable();
            $table->string('cartao_postagem_correios', 100)->nullable();
            $table->text('token_correios')->nullable();
            $table->string('token_expira_correios', 30)->nullable();
            $table->string('dr_correios', 30)->nullable();
            $table->string('contrato_correios', 30)->nullable();
            $table->string('token_auth_nfse', 255)->nullable();
            $table->integer('timeout_nfe')->default(8);
            $table->integer('timeout_nfce')->default(8);
            $table->integer('timeout_cte')->default(8);
            $table->integer('timeout_mdfe')->default(8);

            // alter table configuracao_supers add column sms_key varchar(120) default null;
            // alter table configuracao_supers add column token_whatsapp varchar(120) default null;

            // alter table configuracao_supers add column usuario_correios varchar(30) default null;
            // alter table configuracao_supers add column codigo_acesso_correios varchar(100) default null;
            // alter table configuracao_supers add column cartao_postagem_correios varchar(100) default null;
            // alter table configuracao_supers add column token_correios text;
            // alter table configuracao_supers add column token_expira_correios varchar(30) default null;
            // alter table configuracao_supers add column dr_correios varchar(30) default null;
            // alter table configuracao_supers add column contrato_correios varchar(30) default null;
            // alter table configuracao_supers add column token_auth_nfse varchar(255) default null;

            // alter table configuracao_supers add column timeout_nfe integer default 8;
            // alter table configuracao_supers add column timeout_nfce integer default 8;
            // alter table configuracao_supers add column timeout_cte integer default 8;
            // alter table configuracao_supers add column timeout_mdfe integer default 8;
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracao_supers');
    }
};
