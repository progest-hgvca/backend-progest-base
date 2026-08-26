<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ExcelProdutosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $filePath = base_path('Lista cadastro PRODUTOS.xlsx');

        if (!file_exists($filePath)) {
            $this->command->warn('⚠️ Arquivo Excel não encontrado em: ' . $filePath);
            return;
        }

        $now = Carbon::now();

        $this->command->info('Importando e classificando produtos do Excel...');
        
        $data = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
            public function array(array $array) { return $array; }
        }, $filePath);

        $rows = $data[0] ?? [];
        $count = 0;

        // Cache para IDs de grupo e unidade para não estourar o DB
        $grupos_cache = [];
        $unidades_cache = [];

        foreach ($rows as $index => $row) {
            // Ignorar cabecalho
            if ($index === 0) continue;

            $codigo = $row[0] ?? null;
            $nome   = $row[1] ?? null;

            if (empty($nome)) {
                continue;
            }

            // --- 1. Algoritmo de Classificação Inteligente ---
            $nome_lower = strtolower($nome);
            
            $grupo_nome = 'GERAL';
            $grupo_tipo = 'Material';
            $unidade_nome = 'UNIDADE';
            
            // Regras de Inferência para UNIDADE
            if (Str::contains($nome_lower, ['comprimido', ' drg', ' dragea', ' cp', ' comp'])) {
                $unidade_nome = 'COMP';
            } elseif (Str::contains($nome_lower, ['ampola', ' amp'])) {
                $unidade_nome = 'AMP';
            } elseif (Str::contains($nome_lower, ['frasco', ' fa ', ' f/a', ' fr '])) {
                $unidade_nome = 'FA';
            } elseif (Str::contains($nome_lower, ['caixa', ' cx '])) {
                $unidade_nome = 'CX';
            } elseif (Str::contains($nome_lower, ['capsula', ' cap'])) {
                $unidade_nome = 'CAPS';
            } elseif (Str::contains($nome_lower, ['tubo', ' tb'])) {
                $unidade_nome = 'TB';
            } elseif (Str::contains($nome_lower, ['envelope', ' env'])) {
                $unidade_nome = 'ENV';
            } elseif (Str::contains($nome_lower, ['seringa', ' ser'])) {
                $unidade_nome = 'SER';
            } elseif (Str::contains($nome_lower, ['pacote', ' pct'])) {
                $unidade_nome = 'PCT';
            } elseif (Str::contains($nome_lower, ['galão', ' gl'])) {
                $unidade_nome = 'GL';
            } elseif (Str::contains($nome_lower, ['litro', ' lts', ' lt '])) {
                $unidade_nome = 'L';
            } elseif (Str::contains($nome_lower, ['metro', ' mts', ' mt '])) {
                $unidade_nome = 'M';
            } elseif (Str::contains($nome_lower, ['rolo', ' rl'])) {
                $unidade_nome = 'RL';
            } elseif (Str::contains($nome_lower, ['kilo', ' kg '])) {
                $unidade_nome = 'KG';
            } elseif (Str::contains($nome_lower, ['par ', ' pares'])) {
                $unidade_nome = 'PAR';
            }

            // Regras de Inferência para GRUPO
            if (Str::contains($nome_lower, ['vitamina', 'acido ascorbico', 'colecalciferol', 'complexo b', 'polivitaminico'])) {
                $grupo_nome = 'VITAMINAS E SUPLEMENTOS';
                $grupo_tipo = 'Medicamento';
            } elseif (Str::contains($nome_lower, ['diazepam', 'clonazepam', 'morfina', 'tramadol', 'fenobarbital', 'midazolam'])) {
                $grupo_nome = 'MEDICAMENTOS CONTROLADOS';
                $grupo_tipo = 'Medicamento';
            } elseif (Str::contains($nome_lower, ['amoxicilina', 'cefalexina', 'ceftriaxona', 'azitromicina', 'ciprofloxacino', 'levofloxacino', 'meropenem'])) {
                $grupo_nome = 'ANTIBIÓTICOS';
                $grupo_tipo = 'Medicamento';
            } elseif (Str::contains($nome_lower, ['injetavel', ' inj', ' ev', ' iv ', ' im ', ' sc '])) {
                $grupo_nome = 'INJETÁVEIS';
                $grupo_tipo = 'Medicamento';
            } elseif (Str::contains($nome_lower, ['soro', 'ringer', 'cloreto de sodio 0,9%', 'glicose', 'agua para injecao'])) {
                $grupo_nome = 'SOLUÇÕES E SOROS';
                $grupo_tipo = 'Medicamento';
            } elseif (Str::contains($nome_lower, ['cloridrato', 'sulfato', 'dipirona', 'paracetamol', 'acido', 'sodio', 'potassio', 'comprimido', 'ampola', 'mg', 'ml'])) {
                $grupo_nome = 'MEDICAMENTOS';
                $grupo_tipo = 'Medicamento';
            } elseif (Str::contains($nome_lower, ['seringa', 'agulha', 'cateter', 'atadura', 'gaze', 'luva', 'esparadrapo', 'scalp', 'sonda', 'fio', 'máscara', 'compressa'])) {
                $grupo_nome = 'MATERIAL MÉDICO HOSPITALAR';
                $grupo_tipo = 'Material';
            } elseif (Str::contains($nome_lower, ['papel', 'caneta', 'clips', 'grampo', 'envelope', 'pasta', 'etiqueta', 'caderno', 'livro', 'toner', 'cartucho'])) {
                $grupo_nome = 'MATERIAL DE EXPEDIENTE';
                $grupo_tipo = 'Material';
            } elseif (Str::contains($nome_lower, ['detergente', 'desinfetante', 'sabão', 'sabonete', 'toalha', 'higiênico', 'álcool', 'vassoura', 'rodo', 'saco'])) {
                $grupo_nome = 'MATERIAL DE LIMPEZA E HIGIENE';
                $grupo_tipo = 'Material';
            }
            // --- Fim do Algoritmo ---

            // Garantir Grupo
            if (!isset($grupos_cache[$grupo_nome])) {
                DB::table('grupo_produto')->updateOrInsert(
                    ['nome' => $grupo_nome],
                    ['tipo' => $grupo_tipo, 'status' => 'A', 'created_at' => $now, 'updated_at' => $now]
                );
                $grupos_cache[$grupo_nome] = DB::table('grupo_produto')->where('nome', $grupo_nome)->first()->id;
            }

            // Garantir Unidade
            if (!isset($unidades_cache[$unidade_nome])) {
                DB::table('unidade_medida')->updateOrInsert(
                    ['nome' => $unidade_nome],
                    ['quantidade_unidade_minima' => 1, 'status' => 'A', 'created_at' => $now, 'updated_at' => $now]
                );
                $unidades_cache[$unidade_nome] = DB::table('unidade_medida')->where('nome', $unidade_nome)->first()->id;
            }

            DB::table('produtos')->updateOrInsert(
                ['nome' => $nome],
                [
                    'codigo_simpas'    => $codigo,
                    'marca'            => 'Diversas',
                    'grupo_produto_id' => $grupos_cache[$grupo_nome],
                    'unidade_medida_id'=> $unidades_cache[$unidade_nome],
                    'status'           => 'A',
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]
            );
            $count++;
        }

        $this->command->info("✅ {$count} produtos importados e classificados com sucesso!");
        $this->command->info("📊 Grupos criados: " . count($grupos_cache));
        $this->command->info("📏 Unidades criadas: " . count($unidades_cache));
    }
}
