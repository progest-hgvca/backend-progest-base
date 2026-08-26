<?php

namespace Database\Seeders;

use App\Models\GrupoProduto;
use Illuminate\Database\Seeder;

/**
 * Cria o grupo padrão de medicamentos controlados.
 *
 * Uso: php artisan db:seed --class=MedicamentosControladosSeeder
 */
class MedicamentosControladosSeeder extends Seeder
{
    public function run()
    {
        GrupoProduto::updateOrCreate(
            ['nome' => 'Medicamentos Controlados'],
            [
                'tipo' => 'Medicamento',
                'controlado' => true,
                'status' => 'A',
            ]
        );

        $this->command->info('Grupo "Medicamentos Controlados" disponível.');
    }
}
