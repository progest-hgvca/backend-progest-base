<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevolucoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devolucoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movimentacao_id')->constrained('movimentacao')->onDelete('restrict');
            $table->foreignId('item_movimentacao_id')->constrained('item_movimentacao')->onDelete('restrict');
            $table->string('lote')->nullable();
            $table->decimal('quantidade', 10, 3);
            $table->string('motivo', 255)->nullable();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('devolucoes');
    }
}
