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
        Schema::table('conta_pagars', function (Blueprint $table) {
            if (!Schema::hasColumn('conta_pagars', 'categoria_conta_id')) {
                $table->unsignedBigInteger('categoria_conta_id')->nullable()->after('local_id');
                $table->foreign('categoria_conta_id')
                    ->references('id')
                    ->on('categoria_contas')
                    ->onDelete('set null');
            }
        });

        Schema::table('conta_recebers', function (Blueprint $table) {
            if (!Schema::hasColumn('conta_recebers', 'categoria_conta_id')) {
                $table->unsignedBigInteger('categoria_conta_id')->nullable()->after('local_id');
                $table->foreign('categoria_conta_id')
                    ->references('id')
                    ->on('categoria_contas')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conta_pagars', function (Blueprint $table) {
            $table->dropForeign(['categoria_conta_id']);
            $table->dropColumn('categoria_conta_id');
        });

        Schema::table('conta_recebers', function (Blueprint $table) {
            $table->dropForeign(['categoria_conta_id']);
            $table->dropColumn('categoria_conta_id');
        });
    }
};
