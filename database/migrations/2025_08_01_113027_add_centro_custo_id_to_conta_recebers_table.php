<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conta_recebers', function (Blueprint $table) {
            $table->unsignedBigInteger('centro_custo_id')->nullable()->after('empresa_id');
            $table->foreign('centro_custo_id')->references('id')->on('centro_custos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('conta_recebers', function (Blueprint $table) {
            $table->dropForeign(['centro_custo_id']);
            $table->dropColumn('centro_custo_id');
        });
    }
};
