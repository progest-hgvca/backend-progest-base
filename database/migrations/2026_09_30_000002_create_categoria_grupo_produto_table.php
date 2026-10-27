<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lista branca de grupos de produto por categoria de setor.
 *
 * Uma linha aqui significa "setores desta categoria podem solicitar produtos
 * deste grupo". A ausência de qualquer linha para uma categoria é tratada como
 * "sem restrição" (ver Setores::produtosDisponiveis), para que o sistema não
 * bloqueie tudo caso o seeder de regras não tenha rodado.
 */
class CreateCategoriaGrupoProdutoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categoria_grupo_produto', function (Blueprint $table) {
            $table->id();
            $table->enum('categoria', ['Farmacia', 'Internamento', 'Administrativo']);
            $table->foreignId('grupo_produto_id')->constrained('grupo_produto')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['categoria', 'grupo_produto_id'], 'uk_categoria_grupo');
            $table->index('categoria');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categoria_grupo_produto');
    }
}
