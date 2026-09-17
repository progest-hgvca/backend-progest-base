<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddConsumoToMovimentacaoTipoEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `movimentacao` MODIFY COLUMN `tipo` ENUM('T', 'D', 'S', 'C') NOT NULL COMMENT 'T = Transferência, D = Devolução, S = Saída, C = Consumo Interno'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `movimentacao` MODIFY COLUMN `tipo` ENUM('T', 'D', 'S') NOT NULL COMMENT 'T = Transferência, D = Devolução, S = Saída'");
    }
}
