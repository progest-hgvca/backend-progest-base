<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Por padrão, ao rodar `php artisan migrate --seed`, apenas a configuração
     * mínima e inicial será injetada no banco (Admin, Polo Inicial e Setor Raiz).
     *
     * Se desejar popular o banco com todos os dados Fake e de Teste, rode:
     * php artisan db:seed --class=FullSystemSeeder
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AdminInicialSeeder::class,
        ]);
    }
}
