<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FullSystemSeeder extends Seeder
{
    /**
     * Seed the application's database with fake/test data (FULL STRESS).
     *
     * Comando para rodar:
     * php artisan db:seed --class=FullSystemSeeder
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            PolosESetoresFullSeeder::class,        // 63 setores
            AdminInicialSeeder::class,             // Usuário adminti
            UsuariosEPerfisSeeder::class,          // Usuários distribuídos
            FornecedoresSeeder::class,             // Fornecedores fakes
            CatalogoProdutosOficialSeeder::class,  // Catálogo oficial pré-processado (417 produtos classificados)
            DadosFakeRelatoriosSeeder::class,      // Estoques e Movimentações em larga escala
        ]);
    }
}
