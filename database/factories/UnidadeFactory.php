<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Polo;

class UnidadeFactory extends Factory
{
    protected $model = Polo::class;

    public function definition()
    {
        return [
            'nome' => $this->faker->company . ' Unidade',
            'status' => 'A'
        ];
    }
}
