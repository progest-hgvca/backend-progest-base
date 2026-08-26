<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterTipoInSetoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Alterar o tipo da coluna 'tipo' para suportar 'Ambos'
        DB::statement("ALTER TABLE setores MODIFY COLUMN tipo ENUM('Medicamento', 'Material', 'Ambos') DEFAULT 'Material'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE setores MODIFY COLUMN tipo ENUM('Medicamento', 'Material') DEFAULT 'Material'");
    }
}
