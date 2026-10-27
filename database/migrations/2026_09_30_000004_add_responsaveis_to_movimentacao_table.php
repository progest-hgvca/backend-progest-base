<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Completa o rastreio dos quatro responsáveis da requisição.
 *
 * Já existiam `usuario_id` (quem solicitou) e `aprovador_usuario_id` (quem
 * autorizou). Faltavam quem separou/atendeu e quem recebeu — que hoje saem
 * em branco na via impressa.
 *
 * `motivo_rejeicao` já era enviado pelo frontend e descartado silenciosamente
 * pelo backend; passa a ser persistido.
 */
class AddResponsaveisToMovimentacaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movimentacao', function (Blueprint $table) {
            $table->foreignId('atendente_usuario_id')->nullable()->after('aprovador_usuario_id')
                ->constrained('users')->onDelete('restrict')
                ->comment('Quem separou/liberou fisicamente os itens');
            $table->dateTime('atendido_em')->nullable()->after('atendente_usuario_id');

            $table->foreignId('recebedor_usuario_id')->nullable()->after('atendido_em')
                ->constrained('users')->onDelete('restrict')
                ->comment('Quem confirmou o recebimento no setor de destino');
            $table->dateTime('recebido_em')->nullable()->after('recebedor_usuario_id');

            $table->text('motivo_rejeicao')->nullable()->after('recebido_em');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movimentacao', function (Blueprint $table) {
            $table->dropForeign(['atendente_usuario_id']);
            $table->dropForeign(['recebedor_usuario_id']);
            $table->dropColumn([
                'atendente_usuario_id',
                'atendido_em',
                'recebedor_usuario_id',
                'recebido_em',
                'motivo_rejeicao',
            ]);
        });
    }
}
