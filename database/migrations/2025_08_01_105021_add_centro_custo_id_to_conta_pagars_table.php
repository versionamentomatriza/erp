<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('conta_pagars', function (Blueprint $table) {
            $table->unsignedBigInteger('centro_custo_id')->nullable()->after('fornecedor_id');

            $table->foreign('centro_custo_id')
                ->references('id')
                ->on('centro_custos')
                ->onDelete('set null'); 
        });
    }

    public function down()
    {
        Schema::table('conta_pagars', function (Blueprint $table) {
            $table->dropForeign(['centro_custo_id']);
            $table->dropColumn('centro_custo_id');
        });
    }
};
