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
        Schema::table('contas_tables', function (Blueprint $table) {
            Schema::table('contas_pagars', function (Blueprint $table) {
                $table->decimal('valor_total', 15, 2)->nullable()->after('valor');
                $table->integer('parcela')->nullable()->after('valor_total');
            });

            Schema::table('contas_recebers', function (Blueprint $table) {
                $table->decimal('valor_total', 15, 2)->nullable()->after('valor');
                $table->integer('parcela')->nullable()->after('valor_total');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contas_pagars', function (Blueprint $table) {
            $table->dropColumn(['valor_total', 'parcela']);
        });

        Schema::table('contas_recebers', function (Blueprint $table) {
            $table->dropColumn(['valor_total', 'parcela']);
        });
    }
};
