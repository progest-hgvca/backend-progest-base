<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Congela o custo do item no momento da aprovação.
 *
 * Antes disso, o relatório financeiro de saídas tentava redescobrir o custo
 * juntando `itens_entrada` por (produto_id, lote). Como `item_movimentacao.lote`
 * guarda um ARRAY JSON dos lotes consumidos pelo FIFO, esse join quase nunca
 * casava e o valor voltava nulo.
 *
 * `valor_unitario` é o custo médio ponderado dos lotes efetivamente consumidos;
 * `valor_total` é o valor da linha. Ambos preenchidos por
 * MovimentacaoController::transferirLotesFifo a partir de estoque_lote.valor_unitario.
 */
class AddValorUnitarioToItemMovimentacaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_movimentacao', function (Blueprint $table) {
            $table->decimal('valor_unitario', 12, 4)->nullable()->after('quantidade_liberada')
                ->comment('Custo médio ponderado dos lotes consumidos, congelado na aprovação');
            $table->decimal('valor_total', 14, 4)->nullable()->after('valor_unitario');
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
            $table->dropColumn(['valor_unitario', 'valor_total']);
        });
    }
}
