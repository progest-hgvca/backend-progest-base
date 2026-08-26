<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuario_polo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('polo_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('polo_id')->references('id')->on('polos')->onDelete('cascade');

            // Unique constraint: um usuário só pode ser admin de um polo uma única vez
            $table->unique(['usuario_id', 'polo_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usuario_polo');
    }
};
