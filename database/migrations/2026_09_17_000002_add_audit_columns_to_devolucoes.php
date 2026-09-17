<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuditColumnsToDevolucoes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('devolucoes', function (Blueprint $table) {
            $table->decimal('quantidade_solicitada', 10, 3)->nullable()->after('lote');
            $table->decimal('quantidade_aprovada', 10, 3)->nullable()->after('quantidade_solicitada');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('devolucoes', function (Blueprint $table) {
            $table->dropColumn(['quantidade_solicitada', 'quantidade_aprovada']);
        });
    }
}
