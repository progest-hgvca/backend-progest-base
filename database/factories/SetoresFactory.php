<?php

namespace Database\Factories;

use App\Models\Setores;
use App\Models\Polo;
use Illuminate\Database\Eloquent\Factories\Factory;

class SetoresFactory extends Factory
{
    protected $model = Setores::class;

    public function definition()
    {
        return [
            'polo_id' => Polo::factory(),
            'nome' => $this->faker->words(2, true) . ' ' . $this->faker->unique()->numberBetween(1, 9999),
            'descricao' => $this->faker->sentence(),
            'status' => 'A',
            'estoque' => false,
            'tipo' => 'Ambos',
        ];
    }
}
