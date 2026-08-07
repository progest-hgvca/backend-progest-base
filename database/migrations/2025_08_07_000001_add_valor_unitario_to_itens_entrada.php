<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValorUnitarioToItensEntrada extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('itens_entrada', function (Blueprint $table) {
            // Valor unitário do produto nesta entrada (nullable pois entradas antigas não possuem)
            $table->decimal('valor_unitario', 12, 4)->nullable()->after('quantidade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('itens_entrada', function (Blueprint $table) {
            $table->dropColumn('valor_unitario');
        });
    }
}
