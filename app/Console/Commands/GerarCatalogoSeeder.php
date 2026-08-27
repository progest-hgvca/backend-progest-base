<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class GerarCatalogoSeeder extends Command
{
    protected $signature = 'produtos:gerar-seeder {arquivo? : Caminho da planilha Excel (opcional)}';
    protected $description = 'Lê uma planilha Excel de produtos e gera o seeder estático CatalogoProdutosOficialSeeder.php';

    public function handle()
    {
        $filePath = $this->argument('arquivo') ?: base_path('Lista cadastro PRODUTOS.xlsx');

        if (!file_exists($filePath)) {
            $this->error("❌ Arquivo Excel não encontrado em: {$filePath}");
            return 1;
        }

        $this->info("📂 Lendo planilha Excel: {$filePath}...");

        $data = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
            public function array(array $array) { return $array; }
        }, $filePath);

        $rows = $data[0] ?? [];
        $this->info("📊 Total de linhas brutas: " . count($rows));

        $gruposMap = [];
        $unidadesMap = [];
        $produtos = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Cabeçalho

            $codigo = !empty($row[0]) ? trim((string)$row[0]) : null;
            $nome   = !empty($row[1]) ? trim((string)$row[1]) : null;

            if (empty($nome)) continue;

            $nome_lower = strtolower($nome);
            
            $grupo_nome = 'GERAL';
            $grupo_tipo = 'Material';
            $controlado = false;
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
            } elseif (Str::contains($nome_lower, ['diazepam', 'clonazepam', 'morfina', 'tramadol', 'fenobarbital', 'midazolam', 'fentanil', 'propofol', 'metadona'])) {
                $grupo_nome = 'MEDICAMENTOS CONTROLADOS';
                $grupo_tipo = 'Medicamento';
                $controlado = true;
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

            $gruposMap[$grupo_nome] = [
                'nome' => $grupo_nome,
                'tipo' => $grupo_tipo,
                'controlado' => $controlado,
            ];

            $unidadesMap[$unidade_nome] = [
                'nome' => $unidade_nome,
            ];

            $produtos[] = [
                'codigo' => $codigo,
                'nome' => $nome,
                'marca' => 'Diversas',
                'grupo' => $grupo_nome,
                'unidade' => $unidade_nome,
            ];
        }

        $gruposExport = var_export(array_values($gruposMap), true);
        $unidadesExport = var_export(array_values($unidadesMap), true);
        $produtosExport = var_export($produtos, true);

        $seederContent = <<<PHP
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Seeder OFICIAL e ESTÁTICO do Catálogo de Produtos.
 * Gerado automaticamente a partir da planilha "{$filePath}".
 *
 * Vantagens:
 * 1. Não precisa de biblioteca Excel na AWS nem parse pesado em runtime.
 * 2. Executa em menos de 1 segundo consumindo quase zero de memória RAM.
 * 3. Totalmente seguro contra falhas de Out-Of-Memory (OOM) em servidores menores (t2/t3.micro).
 */
class CatalogoProdutosOficialSeeder extends Seeder
{
    public function run()
    {
        \$now = Carbon::now();
        \$this->command->info('🚀 Inserindo Catálogo Oficial de Produtos (Seeder estático pré-processado)...');

        // 1. Grupos de Produtos
        \$grupos = {$gruposExport};

        \$grupoIds = [];
        foreach (\$grupos as \$g) {
            DB::table('grupo_produto')->updateOrInsert(
                ['nome' => \$g['nome']],
                [
                    'tipo'       => \$g['tipo'],
                    'controlado' => \$g['controlado'] ?? false,
                    'status'     => 'A',
                    'created_at' => \$now,
                    'updated_at' => \$now,
                ]
            );
            \$grupoIds[\$g['nome']] = DB::table('grupo_produto')->where('nome', \$g['nome'])->value('id');
        }

        // 2. Unidades de Medida
        \$unidades = {$unidadesExport};

        \$unidadeIds = [];
        foreach (\$unidades as \$u) {
            DB::table('unidade_medida')->updateOrInsert(
                ['nome' => \$u['nome']],
                [
                    'quantidade_unidade_minima' => 1,
                    'status'                    => 'A',
                    'created_at'                => \$now,
                    'updated_at'                => \$now,
                ]
            );
            \$unidadeIds[\$u['nome']] = DB::table('unidade_medida')->where('nome', \$u['nome'])->value('id');
        }

        // 3. Produtos (em transação em lote)
        \$produtos = {$produtosExport};

        DB::transaction(function () use (\$produtos, \$grupoIds, \$unidadeIds, \$now) {
            foreach (\$produtos as \$p) {
                DB::table('produtos')->updateOrInsert(
                    ['nome' => \$p['nome']],
                    [
                        'codigo_simpas'    => \$p['codigo'],
                        'marca'            => \$p['marca'] ?? 'Diversas',
                        'grupo_produto_id' => \$grupoIds[\$p['grupo']] ?? null,
                        'unidade_medida_id'=> \$unidadeIds[\$p['unidade']] ?? null,
                        'status'           => 'A',
                        'created_at'       => \$now,
                        'updated_at'       => \$now,
                    ]
                );
            }
        });

        \$this->command->info('✅ ' . count(\$produtos) . ' produtos oficiais do catálogo inseridos com sucesso!');
    }
}

PHP;

        $targetFile = base_path('database/seeders/CatalogoProdutosOficialSeeder.php');
        file_put_contents($targetFile, $seederContent);

        $this->info("✅ Seeder estático gerado com sucesso em: {$targetFile}");
        $this->info("📊 Grupos: " . count($gruposMap) . " | Unidades: " . count($unidadesMap) . " | Produtos: " . count($produtos));
        return 0;
    }
}
