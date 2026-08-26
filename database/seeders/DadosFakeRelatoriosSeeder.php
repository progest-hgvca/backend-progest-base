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
        $this->command->info('📥 Gerando entradas de estoque (mínimo necessário)...');

        $setoresComEstoque = array_filter($this->setores, fn($s) => $s->estoque);

        if (empty($setoresComEstoque)) {
            $this->command->warn('⚠️ Nenhum setor com estoque habilitado encontrado.');
            return;
        }

        for ($i = 1; $i <= 10; $i++) {
            $setor = $setoresComEstoque[array_rand($setoresComEstoque)];
            $fornecedor = $this->fornecedores[array_rand($this->fornecedores)];
            $dataEntrada = Carbon::now()->subDays(rand(0, 30));

            $entrada = Entrada::create([
                'nota_fiscal' => 'NF-FAKE-' . date('Y') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'setor_id' => $setor->id,
                'fornecedor_id' => $fornecedor->id,
                'created_at' => $dataEntrada,
                'updated_at' => $dataEntrada,
            ]);

            $numItens = rand(1, 3);
            $produtosCompativeis = array_filter($this->produtos, fn($p) => $p->grupoProduto && $p->grupoProduto->tipo === $setor->tipo);

            for ($j = 0; $j < $numItens; $j++) {
                if (empty($produtosCompativeis)) continue;

                $produto = $produtosCompativeis[array_rand($produtosCompativeis)];
                $quantidade = rand(50, 200);
                $valorUnitario = round(rand(250, 10000) / 100, 2);

                ItensEntrada::create([
                    'entrada_id'     => $entrada->id,
                    'produto_id'     => $produto->id,
                    'quantidade'     => $quantidade,
                    'valor_unitario' => $valorUnitario,
                    'lote'           => 'L' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'data_vencimento'=> $dataEntrada->copy()->addMonths(rand(12, 36)),
                    'created_at'     => $dataEntrada,
                    'updated_at'     => $dataEntrada,
                ]);

                $estoque = Estoque::where('setor_id', $setor->id)
                    ->where('produto_id', $produto->id)
                    ->first();

                if ($estoque) {
                    $estoque->quantidade_atual += $quantidade;
                    $estoque->status_disponibilidade = 'D';
                    $estoque->save();
                }
            }
        }
        $this->command->info("  ✓ 10 entradas criadas");
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
        $this->command->info('🔄 Gerando cenários de movimentações para demonstração...');

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

        $setorOrigem  = array_values($setoresConsumidores)[0];
        $setorDestino = array_values($setoresDistribuidores)[0];
        $usuario      = $this->usuarios[array_rand($this->usuarios)];
        
        // Em vez de pegar qualquer produto, pegar um que efetivamente tenha estoque no distribuidor!
        $estoqueDestino = Estoque::where('setor_id', $setorDestino->id)
            ->where('quantidade_atual', '>=', 50)
            ->first();

        // Se não achar nenhum com saldo, usa qualquer um (e o seeder terá que criar saldo para ele)
        $produto_id = $estoqueDestino ? $estoqueDestino->produto_id : $this->produtos[array_rand($this->produtos)]->id;

        // Se não tinha estoque suficiente, força a criação do saldo para evitar o erro de trigger negativo
        if (!$estoqueDestino) {
            $estoqueDestino = Estoque::firstOrCreate(
                ['setor_id' => $setorDestino->id, 'produto_id' => $produto_id],
                ['quantidade_minima' => 10, 'status_disponibilidade' => 'D']
            );
            $estoqueDestino->quantidade_atual = 100;
            $estoqueDestino->save();
        }

        $cenarios = [
            ['status' => 'P', 'obs' => 'Movimentação Pendente (Aguardando Distribuidor)', 'liberada' => null],
            ['status' => 'A', 'obs' => 'Movimentação Aprovada (Liberação Total)', 'liberada' => 'total'],
            ['status' => 'A', 'obs' => 'Movimentação Aprovada Parcial (Liberação Menor)', 'liberada' => 'parcial'],
            ['status' => 'R', 'obs' => 'Movimentação Rejeitada (Recusa Integral)', 'liberada' => 'zero'],
            ['status' => 'C', 'obs' => 'Movimentação Cancelada (Pelo Solicitante)', 'liberada' => null],
            ['status' => 'D', 'obs' => 'Rascunho (Não enviada)', 'liberada' => null],
        ];

        foreach ($cenarios as $index => $cenario) {
            $dataMov = Carbon::now()->subDays(6 - $index);

            $movimentacao = Movimentacao::create([
                'usuario_id'          => $usuario->id,
                'setor_origem_id'     => $setorOrigem->id,
                'setor_destino_id'    => $setorDestino->id,
                'tipo'                => 'S', // Saída / Solicitação
                'data_hora'           => $dataMov,
                'status_solicitacao'  => $cenario['status'],
                'observacao'          => $cenario['obs'],
                'created_at'          => $dataMov,
                'updated_at'          => $dataMov,
            ]);

            $qtdSolicitada = rand(10, 30);
            $qtdLiberada = 0;

            if ($cenario['liberada'] === 'total') $qtdLiberada = $qtdSolicitada;
            if ($cenario['liberada'] === 'parcial') $qtdLiberada = rand(1, $qtdSolicitada - 1);

            ItemMovimentacao::create([
                'movimentacao_id'       => $movimentacao->id,
                'produto_id'            => $produto_id,
                'quantidade_solicitada' => $qtdSolicitada,
                'quantidade_liberada'   => $qtdLiberada,
                'lote'                  => $cenario['liberada'] && $qtdLiberada > 0 ? 'L' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) : null,
                'created_at'            => $dataMov,
                'updated_at'            => $dataMov,
            ]);

            // REGRA DE NEGÓCIO FALTANTE: Se aprovado, abater do destino e somar na origem
            if ($qtdLiberada > 0) {
                // Abater do distribuidor (setor destino da solicitação)
                $estoqueDestino->quantidade_atual -= $qtdLiberada;
                $estoqueDestino->save();

                // Somar no consumidor (setor origem da solicitação)
                $estoqueOrigem = Estoque::firstOrCreate(
                    ['setor_id' => $setorOrigem->id, 'produto_id' => $produto_id],
                    ['quantidade_minima' => 5, 'quantidade_atual' => 0, 'status_disponibilidade' => 'I']
                );
                $estoqueOrigem->quantidade_atual += $qtdLiberada;
                $estoqueOrigem->status_disponibilidade = 'D';
                $estoqueOrigem->save();
            }
        }

        $this->command->info("  ✓ 6 cenários de movimentação criados com atualização de saldo real.");
    }
}
