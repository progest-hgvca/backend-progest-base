<?php

namespace Database\Factories;

use App\Models\Estoque;
use App\Models\Produto;
use App\Models\Setores;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstoqueFactory extends Factory
{
    protected $model = Estoque::class;

    public function definition()
    {
        return [
            'setor_id' => Setores::factory(),
            'produto_id' => Produto::factory(),
            'quantidade_minima' => 10,
            'quantidade_atual' => 50,
            'status_disponibilidade' => 'D',
        ];
    }
}
