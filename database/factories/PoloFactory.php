<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Polo;

// Arquivo de compatibilidade: PoloFactory aponta para Unidade
class PoloFactory extends Factory
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
