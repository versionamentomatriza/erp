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
        Schema::table('empresas', function (Blueprint $table) {
            if (!Schema::hasColumn('empresas', 'nome_cobranca')) {
                $table->string('nome_cobranca')->nullable();
            }

            if (!Schema::hasColumn('empresas', 'email_cobranca')) {
                $table->string('email_cobranca')->nullable();
            }

            if (!Schema::hasColumn('empresas', 'telefone_cobranca')) {
                $table->string('telefone_cobranca')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['nome_cobranca', 'email_cobranca', 'telefone_cobranca']);
        });
    }
};
