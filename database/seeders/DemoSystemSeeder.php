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
            PolosESetoresDemoSeeder::class,     // 13 setores
            AdminInicialSeeder::class,          // Usuário adminti
            UsuariosEPerfisSeeder::class,       // Usuários de teste fakes
            GruposEUnidadesMedidasSeeder::class,// Unidade de Medida + Grupos fakes
            FornecedoresSeeder::class,          // Fornecedores fakes
            ProdutosSeeder::class,              // Produtos catálogo antigo (pequeno)
            DadosFakeRelatoriosSeeder::class,   // Estoques e Movimentações fakes
        ]);
    }
}
