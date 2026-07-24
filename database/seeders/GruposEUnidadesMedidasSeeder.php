<?php

namespace Database\Seeders;

use App\Models\GrupoProduto;
use App\Models\UnidadeMedida;
use Illuminate\Database\Seeder;

class GruposEUnidadesMedidasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grupos = [
            'Analgésico e Antitérmico' => 'Medicamento',
            'Antibiótico' => 'Medicamento',
            'Antidepressivo' => 'Medicamento',
            'Vacina' => 'Medicamento',
            'Material de Uso Coletivo' => 'Material',
            'Material Cirúrgico' => 'Material',
            'Material de Curativos' => 'Material',
            'Equipamento Descartável' => 'Material',
            'Material de Limpeza' => 'Material',
            'Material de Escritório' => 'Material',
        ];

        foreach ($grupos as $nome => $tipo) {
            GrupoProduto::updateOrCreate(
                ['nome' => $nome],
                [
                    'tipo' => $tipo,
                    'status' => 'A',
                ]
            );
        }

        $unidades = [
            'Unidade',
            'Caixa',
            'Frasco',
            'Ampola',
            'Comprimido',
            'Cartela',
            'Grama',
            'Pacote',
            'Mililitro',
            'Rolo',
        ];

        foreach ($unidades as $nome) {
            UnidadeMedida::updateOrCreate(
                ['nome' => $nome],
                [
                    'quantidade_unidade_minima' => 1,
                    'status' => 'A',
                ]
            );
        }
    }
}
