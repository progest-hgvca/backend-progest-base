<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddTriggersForAuditMovimentacao extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add BEFORE INSERT trigger
        DB::unprepared('
            CREATE TRIGGER before_insert_movimentacao_audit
            BEFORE INSERT ON movimentacao
            FOR EACH ROW
            BEGIN
                IF (NEW.status_solicitacao IN ("A", "R")) AND NEW.aprovador_usuario_id IS NULL THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Erro de Auditoria: Aprovador não pode ser nulo para movimentações aprovadas ou reprovadas.";
                END IF;
            END
        ');

        // Add BEFORE UPDATE trigger
        DB::unprepared('
            CREATE TRIGGER before_update_movimentacao_audit
            BEFORE UPDATE ON movimentacao
            FOR EACH ROW
            BEGIN
                IF (NEW.status_solicitacao IN ("A", "R")) AND NEW.aprovador_usuario_id IS NULL THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Erro de Auditoria: Aprovador não pode ser nulo para movimentações aprovadas ou reprovadas.";
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS before_insert_movimentacao_audit');
        DB::unprepared('DROP TRIGGER IF EXISTS before_update_movimentacao_audit');
    }
}
