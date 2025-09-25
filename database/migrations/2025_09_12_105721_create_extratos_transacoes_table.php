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
        if (!Schema::hasTable('extratos_transacoes')) {
            Schema::create('extratos_transacoes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('extrato_id');
                $table->unsignedBigInteger('transacao_id');

                $table->foreign('extrato_id')->references('id')->on('extratos')->onDelete('cascade');
                $table->foreign('transacao_id')->references('id')->on('transacoes')->onDelete('cascade');

                $table->unique(['extrato_id', 'transacao_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extratos_transacoes');
    }
};
