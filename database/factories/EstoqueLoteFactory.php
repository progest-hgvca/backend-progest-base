<?php

namespace Database\Factories;

use App\Models\EstoqueLote;
use App\Models\Produto;
use App\Models\Setores;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstoqueLoteFactory extends Factory
{
    protected $model = EstoqueLote::class;

    public function definition()
    {
        return [
            'setor_id' => Setores::factory(),
            'produto_id' => Produto::factory(),
            'lote' => 'LOTE-' . $this->faker->unique()->numberBetween(100, 9999),
            'quantidade_disponivel' => 100,
            'valor_unitario' => 10.50,
            'data_vencimento' => now()->addMonths(12)->toDateString(),
            'data_fabricacao' => now()->subMonths(6)->toDateString(),
        ];
    }
}
