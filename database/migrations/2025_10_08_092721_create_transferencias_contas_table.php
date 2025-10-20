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
        Schema::create('transferencias_contas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_origem_id')->constrained('contas_financeiras')->cascadeOnDelete();
            $table->foreignId('conta_destino_id')->constrained('contas_financeiras')->cascadeOnDelete();
            $table->foreignId('transacao_id')->constrained('transacoes')->cascadeOnDelete();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferencias_contas');
    }
};
