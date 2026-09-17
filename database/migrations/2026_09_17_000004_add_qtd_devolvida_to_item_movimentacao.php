<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQtdDevolvidaToItemMovimentacao extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_movimentacao', function (Blueprint $table) {
            $table->decimal('quantidade_devolvendo', 10, 3)->default(0)->after('quantidade_liberada');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_movimentacao', function (Blueprint $table) {
            $table->dropColumn('quantidade_devolvendo');
        });
    }
}
