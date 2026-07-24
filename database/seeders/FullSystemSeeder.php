<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FullSystemSeeder extends Seeder
{
    /**
     * Seed the application's database with fake/test data.
     *
     * Comando para rodar:
     * php artisan db:seed --class=FullSystemSeeder
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            PolosESetoresSeeder::class,         // Polos + Setores + Relacionamentos
            UsuariosEPerfisSeeder::class,       // Usuários de teste + Vínculo com Setores
            GruposEUnidadesMedidasSeeder::class,// Unidade de Medida + Grupos
            FornecedoresSeeder::class,          // Fornecedores
            ProdutosSeeder::class,              // Produtos e relacionamento com fornecedores
            DadosFakeRelatoriosSeeder::class,   // Estoques, Movimentações e Entradas falsas para relatórios
        ]);
    }
}
