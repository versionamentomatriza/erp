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
        Schema::create('item_carrinho_deliveries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('carrinho_id')->constrained('carrinho_deliveries');
            $table->foreignId('produto_id')->constrained('produtos');
            $table->foreignId('tamanho_id')->nullable()->constrained('tamanho_pizzas');

            $table->decimal('quantidade', 8,3);
            $table->decimal('valor_unitario', 10,2);
            $table->decimal('sub_total', 10,3);
            $table->string('observacao', 50)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_carrinho_deliveries');
    }
};
