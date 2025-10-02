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
        if (!Schema::hasTable('categoria_contas')) {
            Schema::create('categoria_contas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->string('nome');
                $table->string('tipo');
                $table->string('grupo_dre');
                $table->text('descricao')->nullable();

                $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_contas');
    }
};
