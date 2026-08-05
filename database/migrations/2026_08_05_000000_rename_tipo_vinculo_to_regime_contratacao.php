<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RenameTipoVinculoToRegimeContratacao extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Drop foreign key constraint on users first to avoid issues when renaming table/columns
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tipo_vinculo']);
        });

        // 2. Rename the reference table
        Schema::rename('tipo_vinculo', 'regime_contratacao');

        // 3. Rename the column in users table using raw SQL to avoid doctrine/dbal dependency issues
        DB::statement('ALTER TABLE users CHANGE tipo_vinculo regime_contratacao_id bigint unsigned NOT NULL');

        // 4. Re-add the foreign key with the new column name
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('regime_contratacao_id')->references('id')->on('regime_contratacao')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['regime_contratacao_id']);
        });

        DB::statement('ALTER TABLE users CHANGE regime_contratacao_id tipo_vinculo bigint unsigned NOT NULL');

        Schema::rename('regime_contratacao', 'tipo_vinculo');

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('tipo_vinculo')->references('id')->on('tipo_vinculo')->onDelete('restrict');
        });
    }
}
