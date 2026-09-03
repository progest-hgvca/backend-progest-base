<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * ESTE É O SEEDER DE PRODUÇÃO / ENTREGA AO CLIENTE.
     * Vai carregar apenas a estrutura oficial e o catálogo real.
     */
    public function run()
    {
        $this->call([
            RegimeContratacaoSeeder::class,        // Regimes de contratação hospitalares e do setor de saúde
            PolosESetoresFullSeeder::class,        // Estrutura oficial completa (63 setores)
            AdminInicialSeeder::class,             // Cria adminti no setor de TI
            CatalogoProdutosOficialSeeder::class,  // Catálogo oficial pré-processado (leve e ultrarrápido)
        ]);
    }
}
