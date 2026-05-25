<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RefactorNomenclatureTablesAndColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Rename 'unidades' table to 'polos'
        if (Schema::hasTable('unidades') && !Schema::hasTable('polos')) {
            Schema::rename('unidades', 'polos');
        }

        // 2. Rename 'unidade_id' to 'polo_id' in 'setores'
        if (Schema::hasTable('setores') && Schema::hasColumn('setores', 'unidade_id')) {
            Schema::table('setores', function (Blueprint $table) {
                // Drop foreign key if exists
                if ($this->hasForeignKey('setores', 'setores_unidade_id_foreign')) {
                    $table->dropForeign('setores_unidade_id_foreign');
                }
                // Use DB statement to avoid doctrine/dbal dependency
                DB::statement('ALTER TABLE setores CHANGE unidade_id polo_id BIGINT UNSIGNED NOT NULL');
            });

            // Re-add foreign key if needed
            Schema::table('setores', function (Blueprint $table) {
                $table->foreign('polo_id')->references('id')->on('polos')->onDelete('restrict');
            });
        }

        // 3. (Skipped) 'estoque.unidade_id' actually refers to 'setores(id)', so it remains untouched.

        // 4. Rename 'setor_fornecedor' to 'setor_distribuidor'
        if (Schema::hasTable('setor_fornecedor') && !Schema::hasTable('setor_distribuidor')) {
            // drop UK before renaming
            Schema::table('setor_fornecedor', function (Blueprint $table) {
                // Ignore errors if UK doesn't exist
                try {
                    $table->dropUnique('uk_setor_fornecedor');
                } catch (\Exception $e) {}
            });

            Schema::rename('setor_fornecedor', 'setor_distribuidor');
        }

        // 5. Rename 'setor_fornecedor_id' to 'setor_distribuidor_id'
        if (Schema::hasTable('setor_distribuidor') && Schema::hasColumn('setor_distribuidor', 'setor_fornecedor_id')) {
            Schema::table('setor_distribuidor', function (Blueprint $table) {
                if ($this->hasForeignKey('setor_distribuidor', 'setor_fornecedor_setor_fornecedor_id_foreign')) {
                    $table->dropForeign('setor_fornecedor_setor_fornecedor_id_foreign');
                }
                if ($this->hasForeignKey('setor_distribuidor', 'setor_distribuidor_setor_fornecedor_id_foreign')) {
                    $table->dropForeign('setor_distribuidor_setor_fornecedor_id_foreign');
                }

                DB::statement('ALTER TABLE setor_distribuidor CHANGE setor_fornecedor_id setor_distribuidor_id BIGINT UNSIGNED NOT NULL');
            });
            
            Schema::table('setor_distribuidor', function (Blueprint $table) {
                $table->foreign('setor_distribuidor_id', 'fk_setor_distrib_id')->references('id')->on('setores')->onDelete('cascade');
                
                // Re-add unique constraint
                $table->unique(['setor_solicitante_id', 'setor_distribuidor_id'], 'uk_setor_distribuidor');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert renaming
        if (Schema::hasTable('setor_distribuidor')) {
            Schema::table('setor_distribuidor', function (Blueprint $table) {
                try {
                    $table->dropUnique('uk_setor_distribuidor');
                } catch (\Exception $e) {}
                if ($this->hasForeignKey('setor_distribuidor', 'fk_setor_distrib_id')) {
                    $table->dropForeign('fk_setor_distrib_id');
                }
                DB::statement('ALTER TABLE setor_distribuidor CHANGE setor_distribuidor_id setor_fornecedor_id BIGINT UNSIGNED NOT NULL');
            });
            Schema::rename('setor_distribuidor', 'setor_fornecedor');
        }

        // 3. (Skipped) 'estoque.unidade_id' untouched.

        if (Schema::hasTable('setores') && Schema::hasColumn('setores', 'polo_id')) {
            Schema::table('setores', function (Blueprint $table) {
                $table->dropForeign(['polo_id']);
                DB::statement('ALTER TABLE setores CHANGE polo_id unidade_id BIGINT UNSIGNED NOT NULL');
            });
        }

        if (Schema::hasTable('polos')) {
            Schema::rename('polos', 'unidades');
        }
    }

    private function hasForeignKey($table, $key)
    {
        $conn = DB::connection();
        $keys = $conn->select(DB::raw("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.key_column_usage 
            WHERE table_schema = DATABASE() 
              AND table_name = '$table' 
              AND constraint_name = '$key'
        "));
        return count($keys) > 0;
    }
}
