<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Exceções pontuais por setor, aplicadas por cima das regras de categoria.
 *
 * `permitido = true`  libera um produto que a categoria não permitiria
 *                     (ex.: Salas de Emergência solicitam SOROS à CAF).
 * `permitido = false` bloqueia um produto que a categoria permitiria.
 *
 * Existe para evitar que casos isolados obriguem a afrouxar a regra de uma
 * categoria inteira.
 */
class CreateSetorProdutoExcecaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setor_produto_excecao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setor_id')->constrained('setores')->onDelete('cascade');
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade');
            $table->boolean('permitido')->default(true)
                ->comment('1 = libera apesar da categoria, 0 = bloqueia apesar da categoria');
            $table->timestamps();

            $table->unique(['setor_id', 'produto_id'], 'uk_setor_produto_excecao');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('setor_produto_excecao');
    }
}
