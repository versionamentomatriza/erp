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
        if (!Schema::hasTable('conciliacoes')) {
            Schema::create('conciliacoes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('transacao_id');
                $table->unsignedBigInteger('extrato_id');
                $table->string('conciliavel_tipo'); // polimórfico
                $table->unsignedBigInteger('conciliavel_id');
                $table->decimal('valor_conciliado', 16, 7);
                $table->date('data_conciliacao');

                $table->foreign('transacao_id')->references('id')->on('transacoes')->onDelete('cascade');
                $table->foreign('extrato_id')->references('id')->on('extratos')->onDelete('cascade');
                $table->foreign('conta_empresa_id')->references('id')->on('conta_empresas')->onDelete('cascade');

                $table->index(['conciliavel_tipo', 'conciliavel_id']); // melhora consultas polimórficas
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conciliacoes');
    }
};
