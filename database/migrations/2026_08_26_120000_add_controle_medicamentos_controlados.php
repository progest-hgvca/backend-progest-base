<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Habilita o controle de medicamentos controlados:
 *
 * - grupo_produto.controlado: marca o grupo como "medicamento controlado".
 *   Todo produto pertencente a um grupo controlado é tratado como controlado.
 * - produtos.lista_portaria: lista da Portaria SVS/MS 344/98 do medicamento
 *   (A1, A2, A3, B1, B2, C1...), obrigatória para produtos de grupo controlado.
 */
class AddControleMedicamentosControlados extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('grupo_produto', function (Blueprint $table) {
            $table->boolean('controlado')
                ->default(false)
                ->after('tipo')
                ->comment('1 = grupo de medicamentos controlados (Portaria 344/98)');
        });

        Schema::table('produtos', function (Blueprint $table) {
            $table->string('lista_portaria', 5)
                ->nullable()
                ->after('grupo_produto_id')
                ->comment('Lista da Portaria 344/98: A1, A2, A3, B1, B2, C1...');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('grupo_produto', function (Blueprint $table) {
            $table->dropColumn('controlado');
        });

        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn('lista_portaria');
        });
    }
}
