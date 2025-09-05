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
        Schema::create('item_pedido_deliveries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('produto_id')->constrained('produtos');
            $table->foreignId('pedido_id')->constrained('pedido_deliveries');
            $table->foreignId('tamanho_id')->nullable()->constrained('tamanho_pizzas');
            $table->boolean('status'); 
            
            $table->enum('estado', ['novo', 'pendente', 'preparando', 'finalizado'])->default('novo');
            $table->decimal('quantidade', 8,2);
            $table->decimal('valor_unitario', 12,2);
            $table->decimal('sub_total', 12,2);
            $table->string('observacao', 50)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_pedido_deliveries');
    }
};
