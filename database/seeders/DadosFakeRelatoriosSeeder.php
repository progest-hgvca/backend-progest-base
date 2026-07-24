<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entrada;
use App\Models\ItensEntrada;
use App\Models\Movimentacao;
use App\Models\ItemMovimentacao;
use App\Models\Setores;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\User;
use App\Models\Estoque;
use App\Models\EstoqueLote;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DadosFakeRelatoriosSeeder extends Seeder
{
    private $setores = [];
    private $fornecedores = [];
    private $produtos = [];
    private $usuarios = [];

    public function run()
    {
        $this->command->info('🚀 Iniciando geração de dados fake para relatórios (baseado na estrutura existente)...');

        $this->setores = Setores::all()->all();
        $this->fornecedores = Fornecedor::all()->all();
        $this->produtos = Produto::with('grupoProduto')->get()->all();
        $this->usuarios = User::all()->all();

        if (empty($this->setores) || empty($this->produtos) || empty($this->usuarios) || empty($this->fornecedores)) {
            $this->command->error('❌ É necessário rodar os seeders anteriores (PolosESetores, Produtos, Fornecedores, Usuarios) primeiro!');
            return;
        }

        DB::transaction(function () {
            // 1. Estoque
            $this->garantirEstoque();

            // 2. Entradas
            $this->gerarEntradas();

            // 3. Estoque Lote (criado pelas entradas, mas adicionamos mais variações)
            $this->gerarEstoqueLote();

            // 4. Movimentações
            $this->gerarMovimentacoes();
        });

        $this->command->info('✅ Todos os dados fake para relatórios foram gerados com sucesso!');
    }

    private function garantirEstoque()
    {
        $this->command->info('📊 Garantindo registros de estoque...');

        $count = 0;
        $setoresComEstoque = array_filter($this->setores, fn($s) => $s->estoque);

        foreach ($setoresComEstoque as $setor) {
            foreach ($this->produtos as $produto) {
                // Verificar compatibilidade de tipo (garante que tem grupoProduto com eager load)
                if ($produto->grupoProduto && $produto->grupoProduto->tipo !== $setor->tipo) {
                    continue;
                }

                Estoque::firstOrCreate(
                    [
                        'setor_id' => $setor->id,
                        'produto_id' => $produto->id,
                    ],
                    [
                        'quantidade_atual' => 0,
                        'quantidade_minima' => rand(10, 50),
                        'status_disponibilidade' => 'I',
                    ]
                );
                $count++;
            }
        }

        $this->command->info('  ✓ ' . $count . ' registros de estoque garantidos');
    }

    private function gerarEntradas()
    {
        $this->command->info('📥 Gerando entradas de estoque (últimos 12 meses)...');

        $setoresComEstoque = array_filter($this->setores, fn($s) => $s->estoque);

        if (empty($setoresComEstoque)) {
            $this->command->warn('⚠️ Nenhum setor com estoque habilitado encontrado.');
            return;
        }

        for ($i = 1; $i <= 150; $i++) {
            $setor = $setoresComEstoque[array_rand($setoresComEstoque)];
            $fornecedor = $this->fornecedores[array_rand($this->fornecedores)];
            
            // Datas aleatórias dos últimos 12 meses
            $dataEntrada = Carbon::now()->subDays(rand(0, 365));

            $entrada = Entrada::create([
                'nota_fiscal' => 'NF-FAKE-' . date('Y', strtotime($dataEntrada)) . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'setor_id' => $setor->id,
                'fornecedor_id' => $fornecedor->id,
                'created_at' => $dataEntrada,
                'updated_at' => $dataEntrada,
            ]);

            // Gerar de 1 a 5 itens por entrada
            $numItens = rand(1, 5);
            $produtosCompativeis = array_filter($this->produtos, fn($p) => $p->grupoProduto && $p->grupoProduto->tipo === $setor->tipo);
            
            for ($j = 0; $j < $numItens; $j++) {
                if (empty($produtosCompativeis)) continue;
                
                $produto = $produtosCompativeis[array_rand($produtosCompativeis)];
                $quantidade = rand(50, 500);
                
                ItensEntrada::create([
                    'entrada_id' => $entrada->id,
                    'produto_id' => $produto->id,
                    'quantidade' => $quantidade,
                    'lote' => 'L' . date('Y', strtotime($dataEntrada)) . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'data_fabricacao' => $dataEntrada->copy()->subMonths(rand(1, 6)),
                    'data_vencimento' => $dataEntrada->copy()->addMonths(rand(12, 36)),
                    'created_at' => $dataEntrada,
                    'updated_at' => $dataEntrada,
                ]);

                // Atualizar estoque
                $estoque = Estoque::where('setor_id', $setor->id)
                    ->where('produto_id', $produto->id)
                    ->first();
                    
                if ($estoque) {
                    $estoque->quantidade_atual += $quantidade;
                    $estoque->status_disponibilidade = 'D';
                    $estoque->save();
                }
            }

            if ($i % 30 == 0) {
                $this->command->info("  ✓ {$i}/150 entradas criadas");
            }
        }
    }

    private function gerarEstoqueLote()
    {
        $this->command->info('📦 Gerando lotes de estoque adicionais...');

        $count = 0;
        $estoques = Estoque::where('quantidade_atual', '>', 0)->limit(50)->get();

        foreach ($estoques as $estoque) {
            // Gerar 1-3 lotes por estoque
            $numLotes = rand(1, 3);
            
            for ($i = 0; $i < $numLotes; $i++) {
                $dataFabricacao = Carbon::now()->subMonths(rand(1, 12));
                $dataVencimento = Carbon::now()->addMonths(rand(6, 24));
                
                EstoqueLote::firstOrCreate(
                    [
                        'setor_id' => $estoque->setor_id,
                        'produto_id' => $estoque->produto_id,
                        'lote' => 'L' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    ],
                    [
                        'quantidade_disponivel' => rand(10, 200),
                        'data_fabricacao' => $dataFabricacao,
                        'data_vencimento' => $dataVencimento,
                    ]
                );
                $count++;
            }
        }

        $this->command->info('  ✓ ' . $count . ' lotes de estoque criados');
    }

    private function gerarMovimentacoes()
    {
        $this->command->info('🔄 Gerando movimentações (últimos 12 meses)...');

        $tipos = ['T', 'S', 'D']; // T = Transferência, D = Devolução, S = Saída

        for ($i = 1; $i <= 100; $i++) {
            $setorOrigem = $this->setores[array_rand($this->setores)];
            $setoresDestino = array_filter($this->setores, fn($s) => $s->id !== $setorOrigem->id);
            
            if (empty($setoresDestino)) continue;
            
            $setorDestino = $setoresDestino[array_rand($setoresDestino)];
            $usuario = $this->usuarios[array_rand($this->usuarios)];
            $tipo = $tipos[array_rand($tipos)];
            
            // Datas aleatórias dos últimos 12 meses
            $dataMovimentacao = Carbon::now()->subDays(rand(0, 365));

            $movimentacao = Movimentacao::create([
                'usuario_id' => $usuario->id,
                'setor_origem_id' => $setorOrigem->id,
                'setor_destino_id' => $setorDestino->id,
                'tipo' => $tipo,
                'data_hora' => $dataMovimentacao,
                'status_solicitacao' => rand(0, 10) > 2 ? 'A' : 'P', // 80% aprovadas
                'observacao' => 'Movimentação fake para testes - tipo ' . $tipo,
                'created_at' => $dataMovimentacao,
                'updated_at' => $dataMovimentacao,
            ]);

            // Gerar de 1 a 4 itens por movimentação
            $numItens = rand(1, 4);
            
            for ($j = 0; $j < $numItens; $j++) {
                $produto = $this->produtos[array_rand($this->produtos)];
                $quantidadeSolicitada = rand(5, 50);
                
                ItemMovimentacao::create([
                    'movimentacao_id' => $movimentacao->id,
                    'produto_id' => $produto->id,
                    'quantidade_solicitada' => $quantidadeSolicitada,
                    'quantidade_liberada' => $movimentacao->status_solicitacao === 'A' ? $quantidadeSolicitada : 0,
                    'lote' => 'L' . date('Y', strtotime($dataMovimentacao)) . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'created_at' => $dataMovimentacao,
                    'updated_at' => $dataMovimentacao,
                ]);
            }

            if ($i % 25 == 0) {
                $this->command->info("  ✓ {$i}/100 movimentações criadas");
            }
        }
    }
}
