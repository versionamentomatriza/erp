<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToEmpresasTable extends Migration
{
    public function up()
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->integer('cargo_funcao')->nullable(); // Cargo ou função
            $table->integer('atividade')->nullable();   // Atividade da empresa
            $table->integer('qtd_funcionarios')->nullable(); // Quantidade de funcionários
        });
    }

    public function down()
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['cargo_funcao', 'atividade', 'qtd_funcionarios']);
        });
    }
}
