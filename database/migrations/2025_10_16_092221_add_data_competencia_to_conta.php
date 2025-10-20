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
        Schema::table('contas_pagars', function (Blueprint $table) {
            $table->date('data_competencia')->nullable()->after('data_pagamento');
        });

        Schema::table('contas_recebers', function (Blueprint $table) {
            $table->date('data_competencia')->nullable()->after('data_recebimento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contas_a_pagar', function (Blueprint $table) {
            $table->dropColumn('data_competencia');
        });

        Schema::table('contas_a_receber', function (Blueprint $table) {
            $table->dropColumn('data_competencia');
        });
    }
};
