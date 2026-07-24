<?php

namespace Database\Seeders;

use App\Models\GrupoProduto;
use App\Models\Produto;
use App\Models\UnidadeMedida;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ProdutosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catalogo = [
            'Analgésico e Antitérmico' => [
                ['nome' => 'Paracetamol 500mg', 'marca' => 'Genérico', 'unidade' => 'Comprimido'],
                ['nome' => 'Dipirona Sódica 1g', 'marca' => 'Farmaco', 'unidade' => 'Ampola'],
                ['nome' => 'Ibuprofeno 400mg', 'marca' => 'Genérico', 'unidade' => 'Comprimido'],
            ],
            'Antibiótico' => [
                ['nome' => 'Amoxicilina 500mg', 'marca' => 'VidaFarma', 'unidade' => 'Comprimido'],
                ['nome' => 'Ceftriaxona 1g', 'marca' => 'BioLab', 'unidade' => 'Ampola'],
                ['nome' => 'Azitromicina 500mg', 'marca' => 'Genérico', 'unidade' => 'Comprimido'],
                ['nome' => 'Cefalexina 500mg', 'marca' => 'Genérico', 'unidade' => 'Comprimido'],
            ],
            'Vacina' => [
                ['nome' => 'Vacina Influenza Trivalente', 'marca' => 'ImunoPlus', 'unidade' => 'Frasco'],
                ['nome' => 'Vacina Hepatite B', 'marca' => 'ImunoPlus', 'unidade' => 'Frasco'],
            ],
            'Antidepressivo' => [
                ['nome' => 'Sertralina 50mg', 'marca' => 'PharmaLife', 'unidade' => 'Comprimido'],
                ['nome' => 'Fluoxetina 20mg', 'marca' => 'PharmaLife', 'unidade' => 'Comprimido'],
            ],
            'Material de Curativos' => [
                ['nome' => 'Gaze Esterilizada', 'marca' => 'HealthCare', 'unidade' => 'Pacote'],
                ['nome' => 'Esparadrapo 5cm', 'marca' => 'HealthCare', 'unidade' => 'Rolo'],
                ['nome' => 'Algodão 500g', 'marca' => 'HealthCare', 'unidade' => 'Pacote'],
            ],
            'Material Cirúrgico' => [
                ['nome' => 'Seringa 10ml', 'marca' => 'SafeHands', 'unidade' => 'Unidade'],
                ['nome' => 'Agulha 40x12', 'marca' => 'SafeHands', 'unidade' => 'Caixa'],
                ['nome' => 'Cateter Venoso 22G', 'marca' => 'SafeHands', 'unidade' => 'Unidade'],
            ],
            'Equipamento Descartável' => [
                ['nome' => 'Luvas de Procedimento M', 'marca' => 'SafeHands', 'unidade' => 'Caixa'],
                ['nome' => 'Máscara Cirúrgica Tripla', 'marca' => 'SafeHands', 'unidade' => 'Caixa'],
                ['nome' => 'Luva Cirúrgica 7.5', 'marca' => 'SafeHands', 'unidade' => 'Caixa'],
            ],
            'Material de Uso Coletivo' => [
                ['nome' => 'Soro Fisiológico 0,9%', 'marca' => 'HospClean', 'unidade' => 'Mililitro'],
                ['nome' => 'Álcool 70%', 'marca' => 'HospClean', 'unidade' => 'Frasco'],
            ],
            'Material de Limpeza' => [
                ['nome' => 'Detergente Neutro 5L', 'marca' => 'LimpaMais', 'unidade' => 'Frasco'],
                ['nome' => 'Rolo de Papel Toalha', 'marca' => 'LimpaMais', 'unidade' => 'Rolo'],
            ],
            'Material de Escritório' => [
                ['nome' => 'Papel A4 500 Folhas', 'marca' => 'OfficeMax', 'unidade' => 'Pacote'],
                ['nome' => 'Caneta Esferográfica Azul', 'marca' => 'EscritaPro', 'unidade' => 'Caixa'],
            ],
        ];

        foreach ($catalogo as $nomeGrupo => $produtos) {
            $grupo = GrupoProduto::firstWhere('nome', $nomeGrupo);

            if (!$grupo) {
                Log::warning('Grupo de produto não encontrado durante seeding de produtos', [
                    'grupo' => $nomeGrupo,
                ]);
                continue;
            }

            foreach ($produtos as $dadosProduto) {
                $unidade = UnidadeMedida::firstWhere('nome', $dadosProduto['unidade']);

                if (!$unidade) {
                    Log::warning('Unidade de medida não encontrada durante seeding de produtos', [
                        'produto' => $dadosProduto['nome'],
                        'unidade' => $dadosProduto['unidade'],
                    ]);
                    continue;
                }

                Produto::updateOrCreate(
                    [
                        'nome' => $dadosProduto['nome'],
                        'grupo_produto_id' => $grupo->id,
                    ],
                    [
                        'marca' => $dadosProduto['marca'] ?? null,
                        'codigo_simpas' => $dadosProduto['codigo_simpas'] ?? null,
                        'codigo_barras' => $dadosProduto['codigo_barras'] ?? null,
                        'unidade_medida_id' => $unidade->id,
                        'status' => 'A',
                    ]
                );
            }
        }
    }
}
