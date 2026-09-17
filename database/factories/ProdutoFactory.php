<?php

namespace Database\Factories;

use App\Models\Produto;
use App\Models\GrupoProduto;
use App\Models\UnidadeMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProdutoFactory extends Factory
{
    protected $model = Produto::class;

    public function definition()
    {
        return [
            'nome' => $this->faker->words(3, true),
            'marca' => $this->faker->company(),
            'codigo_simpas' => 'SIMP-' . $this->faker->unique()->numberBetween(1000, 9999),
            'codigo_barras' => $this->faker->ean13(),
            'grupo_produto_id' => function () {
                return GrupoProduto::firstOrCreate(
                    ['nome' => 'MEDICAMENTOS GERAIS'],
                    ['tipo' => 'Medicamento', 'controlado' => false, 'status' => 'A']
                )->id;
            },
            'unidade_medida_id' => function () {
                return UnidadeMedida::firstOrCreate(
                    ['nome' => 'UNIDADE'],
                    ['quantidade_unidade_minima' => 1, 'status' => 'A']
                )->id;
            },
            'status' => 'A',
        ];
    }
}
