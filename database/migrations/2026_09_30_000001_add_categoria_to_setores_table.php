<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Classifica funcionalmente cada setor para definir o que ele pode solicitar:
 *
 * - Farmacia       : CAF, farmácias centrais e satélites — podem solicitar tudo.
 * - Internamento   : UTIs, clínicas, salas, centro cirúrgico — restrito a grupos clínicos.
 * - Administrativo : diretorias, RH, TI, manutenção — restrito a expediente/limpeza.
 *
 * Não confundir com `setores.tipo` (Medicamento/Material/Ambos), que descreve
 * QUE natureza de item o setor movimenta, não QUEM ele é dentro do hospital.
 */
class AddCategoriaToSetoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('setores', function (Blueprint $table) {
            $table->enum('categoria', ['Farmacia', 'Internamento', 'Administrativo'])
                ->nullable()
                ->after('tipo')
                ->comment('Classificação funcional do setor para regras de solicitação de produtos');
        });

        // Backfill a partir da taxonomia já presente nos seeders:
        // quem guarda estoque é farmácia; quem só movimenta Material é administrativo;
        // o restante é assistencial (internamento).
        DB::table('setores')->where('estoque', true)->update(['categoria' => 'Farmacia']);

        DB::table('setores')
            ->where('estoque', false)
            ->where('tipo', 'Material')
            ->update(['categoria' => 'Administrativo']);

        DB::table('setores')->whereNull('categoria')->update(['categoria' => 'Internamento']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('setores', function (Blueprint $table) {
            $table->dropColumn('categoria');
        });
    }
}
