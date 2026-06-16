<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Renomeia a coluna 'codigo_simpras' (nomenclatura antiga) para 'codigo_simpas'
 * em bancos já existentes.
 *
 * A migration original (create_produtos_table) foi editada para já criar a coluna
 * como 'codigo_simpas', o que cobre instalações novas (migrate:fresh). Esta migration
 * cobre os bancos que já haviam sido migrados com o nome antigo, evitando o erro
 * "Unknown column 'codigo_simpas'".
 */
class RenameCodigoSimprasToCodigoSimpasInProdutos extends Migration
{
    public function up()
    {
        if (
            Schema::hasTable('produtos') &&
            Schema::hasColumn('produtos', 'codigo_simpras') &&
            !Schema::hasColumn('produtos', 'codigo_simpas')
        ) {
            // CHANGE via statement para não depender de doctrine/dbal.
            DB::statement('ALTER TABLE produtos CHANGE codigo_simpras codigo_simpas VARCHAR(255) NULL');
        }
    }

    public function down()
    {
        if (
            Schema::hasTable('produtos') &&
            Schema::hasColumn('produtos', 'codigo_simpas') &&
            !Schema::hasColumn('produtos', 'codigo_simpras')
        ) {
            DB::statement('ALTER TABLE produtos CHANGE codigo_simpas codigo_simpras VARCHAR(255) NULL');
        }
    }
}
