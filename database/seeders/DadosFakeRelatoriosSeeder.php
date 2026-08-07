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

                // Valor unitário aleatório entre R$ 2,50 e R$ 800,00
                $valorUnitario = round(rand(250, 80000) / 100, 2);

                ItensEntrada::create([
                    'entrada_id'     => $entrada->id,
                    'produto_id'     => $produto->id,
                    'quantidade'     => $quantidade,
                    'valor_unitario' => $valorUnitario,
                    'lote'           => 'L' . date('Y', strtotime($dataEntrada)) . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'data_fabricacao'=> null, // opcional — não obrigatório
                    'data_vencimento'=> $dataEntrada->copy()->addMonths(rand(12, 36)),
                    'created_at'     => $dataEntrada,
                    'updated_at'     => $dataEntrada,
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
            $numLotes = rand(1, 3);

            for ($i = 0; $i < $numLotes; $i++) {
                $dataVencimento = Carbon::now()->addMonths(rand(6, 24));

                EstoqueLote::firstOrCreate(
                    [
                        'setor_id'   => $estoque->setor_id,
                        'produto_id' => $estoque->produto_id,
                        'lote'       => 'L' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    ],
                    [
                        'quantidade_disponivel' => rand(10, 200),
                        'data_fabricacao'       => null, // opcional
                        'data_vencimento'       => $dataVencimento,
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

        // Separar distribuidores (Farmácia Central / Almoxarifado) de consumidores
        $setoresDistribuidores = array_filter($this->setores, function($s) {
            $nome = strtolower($s->nome);
            return str_contains($nome, 'farmácia central') || str_contains($nome, 'farmacia central') || str_contains($nome, 'almoxarifado');
        });

        $setoresConsumidores = array_filter($this->setores, function($s) {
            $nome = strtolower($s->nome);
            return !str_contains($nome, 'farmácia central') && !str_contains($nome, 'farmacia central') && !str_contains($nome, 'almoxarifado');
        });

        if (empty($setoresConsumidores) || empty($setoresDistribuidores)) {
            $this->command->warn('⚠️ Não há setores consumidores ou distribuidores suficientes para gerar movimentações.');
            return;
        }

        for ($i = 1; $i <= 100; $i++) {
            // Regra: origem sempre é um setor CONSUMIDOR fazendo solicitação para um DISTRIBUIDOR
            $setorOrigem  = $setoresConsumidores[array_rand($setoresConsumidores)];
            $setorDestino = $setoresDistribuidores[array_rand($setoresDistribuidores)];
            $usuario      = $this->usuarios[array_rand($this->usuarios)];
            $tipo         = $tipos[array_rand($tipos)];

            // Datas aleatórias dos últimos 12 meses
            $dataMovimentacao = Carbon::now()->subDays(rand(0, 365));

            $movimentacao = Movimentacao::create([
                'usuario_id'          => $usuario->id,
                'setor_origem_id'     => $setorOrigem->id,
                'setor_destino_id'    => $setorDestino->id,
                'tipo'                => $tipo,
                'data_hora'           => $dataMovimentacao,
                'status_solicitacao'  => rand(0, 10) > 2 ? 'A' : 'P', // 80% aprovadas
                'observacao'          => 'Movimentação fake para testes - tipo ' . $tipo,
                'created_at'          => $dataMovimentacao,
                'updated_at'          => $dataMovimentacao,
            ]);

            // Gerar de 1 a 4 itens por movimentação
            $numItens = rand(1, 4);

            for ($j = 0; $j < $numItens; $j++) {
                $produto             = $this->produtos[array_rand($this->produtos)];
                $quantidadeSolicitada = rand(5, 50);

                ItemMovimentacao::create([
                    'movimentacao_id'     => $movimentacao->id,
                    'produto_id'          => $produto->id,
                    'quantidade_solicitada' => $quantidadeSolicitada,
                    'quantidade_liberada' => $movimentacao->status_solicitacao === 'A' ? $quantidadeSolicitada : 0,
                    'lote'                => 'L' . date('Y', strtotime($dataMovimentacao)) . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'created_at'          => $dataMovimentacao,
                    'updated_at'          => $dataMovimentacao,
                ]);
            }

            if ($i % 25 == 0) {
                $this->command->info("  ✓ {$i}/100 movimentações criadas");
            }
        }
    }
}
