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
        Schema::create('extratos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('banco')->nullable();
            $table->date('inicio');
            $table->date('fim');
            $table->decimal('saldo_inicial', 15, 2)->nullable();
            $table->decimal('saldo_final', 15, 2)->nullable();
            $table->enum('status', ['pendente', 'conciliado'])->default('pendente');
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extratos');
    }
};
