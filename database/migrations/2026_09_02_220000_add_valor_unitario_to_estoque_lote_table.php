<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValorUnitarioToEstoqueLoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('estoque_lote', function (Blueprint $table) {
            $table->decimal('valor_unitario', 12, 4)->nullable()->after('quantidade_disponivel');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('estoque_lote', function (Blueprint $table) {
            $table->dropColumn('valor_unitario');
        });
    }
}
