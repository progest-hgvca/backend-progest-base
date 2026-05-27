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

        // 3. Rename 'unidade_id' to 'setor_id' em 'estoque', 'estoque_lote' e 'estoque_auditoria'
        //    'unidade_id' era o nome histórico quando a tabela se chamava 'unidades' (agora 'polos').
        //    A FK aponta para setores.id, portanto o nome correto é 'setor_id'.

        // 3a. estoque
        if (Schema::hasTable('estoque') && Schema::hasColumn('estoque', 'unidade_id')) {
            // Dropar triggers que referenciam NEW.unidade_id antes de renomear
            DB::unprepared('DROP TRIGGER IF EXISTS after_update_estoque');
            DB::unprepared('DROP TRIGGER IF EXISTS after_insert_estoque');

            DB::statement('ALTER TABLE estoque CHANGE unidade_id setor_id BIGINT UNSIGNED NOT NULL');

            // Recriar triggers com o novo nome de coluna
            DB::unprepared('
                CREATE TRIGGER after_update_estoque
                AFTER UPDATE ON estoque
                FOR EACH ROW
                BEGIN
                    IF OLD.quantidade_atual <> NEW.quantidade_atual THEN
                        INSERT INTO estoque_auditoria (
                            estoque_id, produto_id, setor_id,
                            quantidade_anterior, quantidade_nova, diferenca, operacao, usuario
                        ) VALUES (
                            NEW.id, NEW.produto_id, NEW.setor_id,
                            OLD.quantidade_atual, NEW.quantidade_atual,
                            NEW.quantidade_atual - OLD.quantidade_atual,
                            "UPDATE", USER()
                        );
                    END IF;
                END
            ');

            DB::unprepared('
                CREATE TRIGGER after_insert_estoque
                AFTER INSERT ON estoque
                FOR EACH ROW
                BEGIN
                    INSERT INTO estoque_auditoria (
                        estoque_id, produto_id, setor_id,
                        quantidade_anterior, quantidade_nova, diferenca, operacao, usuario
                    ) VALUES (
                        NEW.id, NEW.produto_id, NEW.setor_id,
                        0, NEW.quantidade_atual, NEW.quantidade_atual,
                        "INSERT", USER()
                    );
                END
            ');
        }

        // 3b. estoque_lote
        if (Schema::hasTable('estoque_lote') && Schema::hasColumn('estoque_lote', 'unidade_id')) {
            Schema::table('estoque_lote', function (Blueprint $table) {
                if ($this->hasForeignKey('estoque_lote', 'estoque_lote_unidade_id_foreign')) {
                    $table->dropForeign('estoque_lote_unidade_id_foreign');
                }
            });
            Schema::table('estoque_lote', function (Blueprint $table) {
                $table->dropUnique('unique_estoque_lote');
            });
            DB::statement('ALTER TABLE estoque_lote CHANGE unidade_id setor_id BIGINT UNSIGNED NOT NULL');
            Schema::table('estoque_lote', function (Blueprint $table) {
                $table->foreign('setor_id')->references('id')->on('setores')->onDelete('restrict');
                $table->unique(['setor_id', 'produto_id', 'lote'], 'unique_estoque_lote');
            });
        }

        // 3c. estoque_auditoria (se a coluna ainda não foi renomeada pelo trigger step acima)
        if (Schema::hasTable('estoque_auditoria') && Schema::hasColumn('estoque_auditoria', 'unidade_id')) {
            DB::statement('ALTER TABLE estoque_auditoria CHANGE unidade_id setor_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE estoque_auditoria DROP INDEX IF EXISTS idx_unidade_id');
            DB::statement('CREATE INDEX idx_setor_id ON estoque_auditoria (setor_id)');
        }

        // 4. Rename 'setor_fornecedor' to 'setor_distribuidor'
        if (Schema::hasTable('setor_fornecedor') && !Schema::hasTable('setor_distribuidor')) {
            // drop UK before renaming
            Schema::table('setor_fornecedor', function (Blueprint $table) {
                if ($this->hasForeignKey('setor_fornecedor', 'setor_fornecedor_setor_solicitante_id_foreign')) {
                    $table->dropForeign('setor_fornecedor_setor_solicitante_id_foreign');
                }
                if ($this->hasForeignKey('setor_fornecedor', 'setor_fornecedor_setor_fornecedor_id_foreign')) {
                    $table->dropForeign('setor_fornecedor_setor_fornecedor_id_foreign');
                }
            });
            Schema::table('setor_fornecedor', function (Blueprint $table) {
                $table->dropUnique(['setor_solicitante_id', 'setor_fornecedor_id']);
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
                $table->foreign('setor_solicitante_id', 'fk_setor_solic_id')->references('id')->on('setores')->onDelete('cascade');
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

        // 3. Reverter renomeação de setor_id → unidade_id
        if (Schema::hasTable('estoque_auditoria') && Schema::hasColumn('estoque_auditoria', 'setor_id')) {
            DB::statement('ALTER TABLE estoque_auditoria CHANGE setor_id unidade_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE estoque_auditoria DROP INDEX IF EXISTS idx_setor_id');
            DB::statement('CREATE INDEX idx_unidade_id ON estoque_auditoria (unidade_id)');
        }

        if (Schema::hasTable('estoque_lote') && Schema::hasColumn('estoque_lote', 'setor_id')) {
            Schema::table('estoque_lote', function (Blueprint $table) {
                try { $table->dropUnique('unique_estoque_lote'); } catch (\Exception $e) {}
                $table->dropForeign(['setor_id']);
            });
            DB::statement('ALTER TABLE estoque_lote CHANGE setor_id unidade_id BIGINT UNSIGNED NOT NULL');
            Schema::table('estoque_lote', function (Blueprint $table) {
                $table->foreign('unidade_id')->references('id')->on('setores')->onDelete('restrict');
                $table->unique(['unidade_id', 'produto_id', 'lote'], 'unique_estoque_lote');
            });
        }

        if (Schema::hasTable('estoque') && Schema::hasColumn('estoque', 'setor_id')) {
            DB::unprepared('DROP TRIGGER IF EXISTS after_update_estoque');
            DB::unprepared('DROP TRIGGER IF EXISTS after_insert_estoque');

            DB::statement('ALTER TABLE estoque CHANGE setor_id unidade_id BIGINT UNSIGNED NOT NULL');

            DB::unprepared('
                CREATE TRIGGER after_update_estoque
                AFTER UPDATE ON estoque
                FOR EACH ROW
                BEGIN
                    IF OLD.quantidade_atual <> NEW.quantidade_atual THEN
                        INSERT INTO estoque_auditoria (
                            estoque_id, produto_id, unidade_id,
                            quantidade_anterior, quantidade_nova, diferenca, operacao, usuario
                        ) VALUES (
                            NEW.id, NEW.produto_id, NEW.unidade_id,
                            OLD.quantidade_atual, NEW.quantidade_atual,
                            NEW.quantidade_atual - OLD.quantidade_atual,
                            "UPDATE", USER()
                        );
                    END IF;
                END
            ');

            DB::unprepared('
                CREATE TRIGGER after_insert_estoque
                AFTER INSERT ON estoque
                FOR EACH ROW
                BEGIN
                    INSERT INTO estoque_auditoria (
                        estoque_id, produto_id, unidade_id,
                        quantidade_anterior, quantidade_nova, diferenca, operacao, usuario
                    ) VALUES (
                        NEW.id, NEW.produto_id, NEW.unidade_id,
                        0, NEW.quantidade_atual, NEW.quantidade_atual,
                        "INSERT", USER()
                    );
                END
            ');
        }

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
