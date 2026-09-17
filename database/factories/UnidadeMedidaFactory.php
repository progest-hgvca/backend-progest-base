<?php

namespace Database\Factories;

use App\Models\UnidadeMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnidadeMedidaFactory extends Factory
{
    protected $model = UnidadeMedida::class;

    public function definition()
    {
        return [
            'nome' => 'UNIDADE',
            'sigla' => 'UN',
            'status' => 'A',
        ];
    }
}
