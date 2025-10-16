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
        Schema::table('conciliacoes', function (Blueprint $table) {
            $table->unsignedBigInteger('conta_financeira_id')->nullable()->after('conta_empresa_id');

            $table->foreign('conta_financeira_id')
                ->references('id')
                ->on('contas_financeiras')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conciliacoes', function (Blueprint $table) {
            $table->dropForeign(['conta_financeira_id']);
            $table->dropColumn('conta_financeira_id');
        });
    }
};
