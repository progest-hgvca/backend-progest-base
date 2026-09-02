<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoSystemSeeder extends Seeder
{
    /**
     * Seed the application's database with fake/test data (DEMO).
     * Usa uma estrutura reduzida (13 setores) para facilitar a apresentação.
     *
     * Comando para rodar:
     * php artisan db:seed --class=DemoSystemSeeder
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            PolosESetoresDemoSeeder::class,        // 13 setores oficiais da demonstração
            AdminInicialSeeder::class,             // Usuário adminti
            UsuariosEPerfisSeeder::class,          // Usuários de teste fakes (Jean, Arthur, Pablo)
            FornecedoresSeeder::class,             // Fornecedores hospitalares reais
            CatalogoProdutosOficialSeeder::class,  // Catálogo oficial pré-processado (produtos reais da planilha)
            DadosFakeRelatoriosSeeder::class,      // Simulação realista em todos os setores (estoques, lotes, NFs e pedidos)
        ]);
    }
}
