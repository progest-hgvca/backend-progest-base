<?php

namespace Database\Factories;

use App\Models\GrupoProduto;
use Illuminate\Database\Eloquent\Factories\Factory;

class GrupoProdutoFactory extends Factory
{
    protected $model = GrupoProduto::class;

    public function definition()
    {
        return [
            'nome' => $this->faker->words(2, true),
            'tipo' => 'Medicamento',
            'controlado' => false,
            'status' => 'A',
        ];
    }
}
