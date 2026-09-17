<?php

namespace Database\Factories;

use App\Models\Movimentacao;
use App\Models\Setores;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovimentacaoFactory extends Factory
{
    protected $model = Movimentacao::class;

    public function definition()
    {
        return [
            'usuario_id' => User::factory(),
            'setor_origem_id' => Setores::factory()->state(['estoque' => true]),
            'setor_destino_id' => Setores::factory(),
            'tipo' => 'S',
            'data_hora' => now(),
            'status_solicitacao' => 'P',
            'observacao' => $this->faker->sentence(),
        ];
    }
}
