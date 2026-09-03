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
    private $setores;
    private $fornecedores;
    private $produtos;
    private $usuarios;
    private $distribuidoresMap = [];

    public function run()
    {
        $this->command->info('🚀 Iniciando geração de dados realistas hospitalares para TODOS os setores...');

        $this->setores = Setores::with('polo')->get();
        $this->fornecedores = Fornecedor::where('status', 'A')->get();
        $this->produtos = Produto::with('grupoProduto')->get();
        $this->usuarios = User::all();

        if ($this->setores->isEmpty() || $this->produtos->isEmpty() || $this->usuarios->isEmpty() || $this->fornecedores->isEmpty()) {
            $this->command->error('❌ É necessário rodar os seeders anteriores (PolosESetores, Catálogo, Fornecedores, Usuários) primeiro!');
            return;
        }

        // Mapear relações pré-existentes de setor_distribuidor
        $relacoes = DB::table('setor_distribuidor')->get();
        foreach ($relacoes as $rel) {
            $this->distribuidoresMap[$rel->setor_solicitante_id][] = $rel->setor_distribuidor_id;
        }

        DB::transaction(function () {
            // 1. Entradas por Nota Fiscal e Estoques dos setores com estoque
            $this->gerarEntradasEEstoques();

            // 2. Lotes e Validades (vigentes, próximos ao vencimento e vencidos)
            $this->gerarEstoqueLotesEValidades();

            // 3. Movimentações clínicas e administrativas cobrindo TODOS os setores
            $this->gerarMovimentacoesCompletas();
        });

        $this->command->info('✅ Simulação hospitalar concluída com sucesso! Todos os setores foram populados.');
    }

    /**
     * 1. Gera Notas Fiscais e saldos de estoque balanceados para setores com estoque.
     */
    private function gerarEntradasEEstoques()
    {
        $this->command->info('📥 [1/3] Gerando Entradas de NF e Estoque para setores gerenciadores...');

        $setoresComEstoque = $this->setores->where('estoque', true);

        if ($setoresComEstoque->isEmpty()) {
            $this->command->warn('⚠️ Nenhum setor com estoque habilitado encontrado.');
            return;
        }

        $now = Carbon::now();
        $anoAtual = $now->year;
        $nfSeq = 1000;

        foreach ($setoresComEstoque as $setor) {
            $tipoSetor = $setor->tipo ?? 'Ambos';

            // Produtos compatíveis com o tipo do setor
            $produtosCompativeis = $this->produtos->filter(function ($p) use ($tipoSetor) {
                if ($tipoSetor === 'Ambos') return true;
                $grupoTipo = $p->grupoProduto?->tipo ?? 'Medicamento';
                return $grupoTipo === $tipoSetor;
            })->values();

            if ($produtosCompativeis->isEmpty()) {
                $produtosCompativeis = $this->produtos;
            }

            // Selecionar amostra rica de 30 a 50 produtos para ter estoque neste setor
            $qtdProdutosEstoque = min(45, $produtosCompativeis->count());
            $produtosSelecionados = $produtosCompativeis->random($qtdProdutosEstoque);

            // Garantir que medicamentos controlados estejam presentes na CAF e Farmácia de Dispensação
            if (str_contains(strtoupper($setor->nome), 'CAF') || str_contains(strtoupper($setor->nome), 'DISPENSAÇÃO') || str_contains(strtoupper($setor->nome), 'CENTRAL')) {
                $controlados = $this->produtos->filter(fn($p) => $p->grupoProduto?->controlado)->take(6);
                $produtosSelecionados = $produtosSelecionados->merge($controlados)->unique('id');
            }

            // Criar 6 a 8 Notas Fiscais distribuídas ao longo dos últimos 90 dias
            $diasHistorico = [85, 70, 56, 42, 28, 14, 3];

            foreach ($diasHistorico as $diasAtras) {
                $dataEntrada = $now->copy()->subDays($diasAtras)->setTime(rand(8, 17), rand(10, 50));
                $nfSeq += rand(3, 12);
                $nfNumero = 'NF-' . str_pad($nfSeq, 6, '0', STR_PAD_LEFT) . '/' . $anoAtual;

                // Selecionar fornecedor adequado
                $fornecedor = $this->selecionarFornecedorApropriado($tipoSetor);

                $entrada = Entrada::create([
                    'nota_fiscal'   => $nfNumero,
                    'setor_id'      => $setor->id,
                    'fornecedor_id' => $fornecedor->id,
                    'created_at'    => $dataEntrada,
                    'updated_at'    => $dataEntrada,
                ]);

                // 3 a 6 itens por Nota Fiscal
                $itensQtd = min(rand(3, 6), $produtosSelecionados->count());
                $itensAmostra = $produtosSelecionados->random($itensQtd);

                foreach ($itensAmostra as $prod) {
                    $quantidadeNF = rand(80, 400);
                    $valorUnitario = $this->calcularPrecoRealista($prod);
                    $codLote = 'L' . substr($anoAtual, 2) . str_pad(rand(101, 999), 3, '0', STR_PAD_LEFT);
                    $dataVenc = $dataEntrada->copy()->addMonths(rand(14, 34))->toDateString();
                    $dataFabr = $dataEntrada->copy()->subMonths(rand(1, 3))->toDateString();

                    ItensEntrada::create([
                        'entrada_id'      => $entrada->id,
                        'produto_id'      => $prod->id,
                        'quantidade'      => $quantidadeNF,
                        'valor_unitario'  => $valorUnitario,
                        'lote'            => $codLote,
                        'data_fabricacao' => $dataFabr,
                        'data_vencimento' => $dataVenc,
                        'created_at'      => $dataEntrada,
                        'updated_at'      => $dataEntrada,
                    ]);
                }
            }

            // Criar e balancear registros de Estoque agregado para o setor
            foreach ($produtosSelecionados as $idx => $prod) {
                $qtdMinima = rand(25, 60);

                // Variar níveis de estoque:
                // - ~15% dos itens abaixo do mínimo (críticos) para gerar alertas
                // - ~5% zerados (falta)
                // - ~80% saudáveis com bom saldo
                if ($idx % 7 === 0) {
                    // Abaixo do mínimo
                    $qtdAtual = rand(3, $qtdMinima - 5);
                    $status = 'D';
                } elseif ($idx % 19 === 0) {
                    // Em falta / zerado
                    $qtdAtual = 0;
                    $status = 'I';
                } else {
                    // Saudável
                    $qtdAtual = rand(100, 500);
                    $status = 'D';
                }

                Estoque::updateOrCreate(
                    [
                        'setor_id'   => $setor->id,
                        'produto_id' => $prod->id,
                    ],
                    [
                        'quantidade_atual'       => $qtdAtual,
                        'quantidade_minima'      => $qtdMinima,
                        'status_disponibilidade' => $status,
                        'created_at'             => $now->copy()->subDays(60),
                        'updated_at'             => $now,
                    ]
                );
            }
        }

        $this->command->info('  ✓ Entradas de NF e estoques criados.');
    }

    /**
     * 2. Gera lotes detalhados com validades diversas (normais, a vencer em 30-60 dias e vencidos).
     */
    private function gerarEstoqueLotesEValidades()
    {
        $this->command->info('📦 [2/3] Gerando EstoqueLote (vigentes, alerta de vencimento e barreira FIFO)...');

        $estoques = Estoque::where('quantidade_atual', '>', 0)->get();
        $now = Carbon::now();
        $anoAtual = $now->year;
        $loteCount = 0;

        foreach ($estoques as $idx => $estoque) {
            $saldoRestante = (float) $estoque->quantidade_atual;
            $valorUnitario = $estoque->produto ? $this->calcularPrecoRealista($estoque->produto) : null;

            // 1. Lote de Vencimento Próximo (para ~15% dos produtos) - expira em 20 a 50 dias
            if ($idx % 6 === 0 && $saldoRestante > 15) {
                $qtdLoteAlerta = rand(8, 20);
                $dataVencAlerta = $now->copy()->addDays(rand(20, 50))->toDateString();
                $loteCod = 'L' . substr($anoAtual, 2) . '-ALERT-' . str_pad(rand(10, 99), 2, '0', STR_PAD_LEFT);

                EstoqueLote::updateOrCreate(
                    [
                        'setor_id'   => $estoque->setor_id,
                        'produto_id' => $estoque->produto_id,
                        'lote'       => $loteCod,
                    ],
                    [
                        'quantidade_disponivel' => $qtdLoteAlerta,
                        'valor_unitario'        => $valorUnitario,
                        'data_fabricacao'       => $now->copy()->subMonths(18)->toDateString(),
                        'data_vencimento'       => $dataVencAlerta,
                        'created_at'            => $now->copy()->subMonths(6),
                        'updated_at'            => $now,
                    ]
                );

                $saldoRestante -= $qtdLoteAlerta;
                $loteCount++;
            }

            // 2. Lotes principais vigentes (longo prazo: 12 a 30 meses)
            $numLotes = $saldoRestante > 150 ? 2 : 1;
            for ($l = 1; $l <= $numLotes; $l++) {
                if ($saldoRestante <= 0) break;

                $qtdDesteLote = ($l === $numLotes) ? $saldoRestante : round($saldoRestante * 0.6);
                $dataVenc = $now->copy()->addMonths(rand(12, 32))->toDateString();
                $loteCod = 'L' . substr($anoAtual, 2) . '-' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);

                EstoqueLote::updateOrCreate(
                    [
                        'setor_id'   => $estoque->setor_id,
                        'produto_id' => $estoque->produto_id,
                        'lote'       => $loteCod,
                    ],
                    [
                        'quantidade_disponivel' => $qtdDesteLote,
                        'valor_unitario'        => $valorUnitario,
                        'data_fabricacao'       => $now->copy()->subMonths(rand(2, 6))->toDateString(),
                        'data_vencimento'       => $dataVenc,
                        'created_at'            => $now->copy()->subMonths(3),
                        'updated_at'            => $now,
                    ]
                );

                $saldoRestante -= $qtdDesteLote;
                $loteCount++;
            }

            // 3. Lote Vencido isolado (para ~8% dos produtos) - testa barreira de segurança FIFO
            if ($idx % 12 === 0) {
                $dataVencPassada = $now->copy()->subDays(rand(10, 45))->toDateString();
                $loteVencCod = 'L' . substr($anoAtual - 2, 2) . '-VENC-' . str_pad(rand(10, 99), 2, '0', STR_PAD_LEFT);

                EstoqueLote::updateOrCreate(
                    [
                        'setor_id'   => $estoque->setor_id,
                        'produto_id' => $estoque->produto_id,
                        'lote'       => $loteVencCod,
                    ],
                    [
                        'quantidade_disponivel' => rand(5, 15),
                        'valor_unitario'        => $valorUnitario,
                        'data_fabricacao'       => $now->copy()->subMonths(30)->toDateString(),
                        'data_vencimento'       => $dataVencPassada,
                        'created_at'            => $now->copy()->subMonths(12),
                        'updated_at'            => $now,
                    ]
                );
                $loteCount++;
            }
        }

        $this->command->info("  ✓ {$loteCount} lotes de estoque gerados.");
    }

    /**
     * 3. Gera movimentações completas para TODOS os setores do hospital.
     */
    private function gerarMovimentacoesCompletas()
    {
        $this->command->info('🔄 [3/3] Gerando Requisições e Movimentações para TODOS os setores...');

        $now = Carbon::now();
        $userSolicitante = $this->usuarios->firstWhere('email', 'jeansolicitante@gmail.com') ?? $this->usuarios->first();
        $userAlmoxarife  = $this->usuarios->firstWhere('email', 'arthuralmoxarife@gmail.com') ?? $this->usuarios->first();
        $userAdmin       = $this->usuarios->firstWhere('email', 'pabloadmin@gmail.com') ?? $this->usuarios->first();

        $farmaciasComEstoque = $this->setores->where('estoque', true)->values();
        $cafHGVC = $farmaciasComEstoque->first(fn($s) => str_contains(strtoupper($s->nome), 'CAF')) ?? $farmaciasComEstoque->first();

        $movimentacoesCriadas = 0;

        // Para CADA setor do sistema (garantindo que qualquer setor selecionado tenha dados)
        foreach ($this->setores as $setor) {
            $isDistribuidor = $setor->estoque;

            // Encontrar o distribuidor autorizado para este setor
            $distribuidorId = null;
            if (!empty($this->distribuidoresMap[$setor->id])) {
                $distribuidorId = $this->distribuidoresMap[$setor->id][0];
            }

            // Fallback para farmácia do mesmo polo ou CAF
            if (!$distribuidorId) {
                $farmaciaMesmoPolo = $farmaciasComEstoque->firstWhere('polo_id', $setor->polo_id);
                $distribuidorId = $farmaciaMesmoPolo ? $farmaciaMesmoPolo->id : $cafHGVC->id;
            }

            $distribuidor = $this->setores->firstWhere('id', $distribuidorId) ?? $cafHGVC;

            // Se o próprio setor for a CAF, seu fornecedor para transferências é outro polo ou dispensação
            if ($setor->id === $distribuidor->id) {
                $outroDistribuidor = $farmaciasComEstoque->firstWhere('id', '!=', $setor->id);
                if ($outroDistribuidor) {
                    $distribuidor = $outroDistribuidor;
                }
            }

            // Produtos compatíveis para movimentação
            $tipoSetor = $setor->tipo ?? 'Ambos';
            $produtosMov = $this->produtos->filter(function ($p) use ($tipoSetor) {
                if ($tipoSetor === 'Ambos') return true;
                $grupoTipo = $p->grupoProduto?->tipo ?? 'Medicamento';
                return $grupoTipo === $tipoSetor;
            })->values();

            if ($produtosMov->isEmpty()) {
                $produtosMov = $this->produtos;
            }

            // Obter produtos que tenham estoque real no distribuidor
            $estoquesDistribuidor = Estoque::where('setor_id', $distribuidor->id)
                ->where('quantidade_atual', '>=', 30)
                ->get();

            $produtosComSaldo = $produtosMov->whereIn('id', $estoquesDistribuidor->pluck('produto_id'))->values();
            if ($produtosComSaldo->isEmpty()) {
                $produtosComSaldo = $produtosMov->take(15);
            }

            // Definir os cenários de uso real para este setor
            $cenarios = [
                // 1. Rascunho ('C'): aberto pelo solicitante para conferência
                [
                    'status'     => 'C',
                    'tipo'       => $isDistribuidor ? 'T' : 'S',
                    'diasAtras'  => 0,
                    'obs'        => 'Rascunho de reposição setorial - aguardando validação do enfermeiro-chefe.',
                    'aprovador'  => null,
                    'liberacao'  => 'nenhuma',
                ],
                // 2. Pendente ('P'): aguardando análise do almoxarife/farmácia
                [
                    'status'     => 'P',
                    'tipo'       => $isDistribuidor ? 'T' : 'S',
                    'diasAtras'  => 1,
                    'obs'        => 'Solicitação de reposição regular para escala do plantão.',
                    'aprovador'  => null,
                    'liberacao'  => 'nenhuma',
                ],
                // 3. Aprovada Total ('A'): entregue e conferida
                [
                    'status'     => 'A',
                    'tipo'       => $isDistribuidor ? 'T' : 'S',
                    'diasAtras'  => 6,
                    'obs'        => 'Pedido conferido e liberado integralmente pela farmácia distribuidora.',
                    'aprovador'  => $userAlmoxarife->id,
                    'liberacao'  => 'total',
                ],
                // 4. Aprovada Parcial ('A'): liberação fracionada por cota
                [
                    'status'     => 'A',
                    'tipo'       => $isDistribuidor ? 'T' : 'S',
                    'diasAtras'  => 14,
                    'obs'        => 'Liberado quantitativo para 48h de consumo (cota racionada pelo distribuidor).',
                    'aprovador'  => $userAlmoxarife->id,
                    'liberacao'  => 'parcial',
                ],
                // 5. Rejeitada ('R'): justificativa clínica/gestão
                [
                    'status'     => 'R',
                    'tipo'       => $isDistribuidor ? 'T' : 'S',
                    'diasAtras'  => 20,
                    'obs'        => 'Pedido reprovado: solicitação duplicada identificada para o mesmo período/leito.',
                    'aprovador'  => $userAlmoxarife->id,
                    'liberacao'  => 'nenhuma',
                ],
                // 6. Cancelada ('X'): solicitante desistiu
                [
                    'status'     => 'X',
                    'tipo'       => $isDistribuidor ? 'T' : 'S',
                    'diasAtras'  => 26,
                    'obs'        => 'Cancelado pelo solicitante: prescrição suspensa após reavaliação do paciente.',
                    'aprovador'  => null,
                    'liberacao'  => 'nenhuma',
                ],
                // 7. Devolução Aprovada ('A', 'D'): sobra clínica devolvida ao distribuidor e aceita
                [
                    'status'        => 'A',
                    'tipo'          => 'D',
                    'is_devolucao'  => true,
                    'diasAtras'     => 3,
                    'obs'           => 'Devolução de medicamentos não administrados no leito após alta hospitalar do paciente.',
                    'aprovador'     => $userAlmoxarife->id,
                    'liberacao'     => 'total',
                ],
                // 8. Devolução Pendente ('P', 'D'): enviada pelo setor e aguardando inspeção
                [
                    'status'        => 'P',
                    'tipo'          => 'D',
                    'is_devolucao'  => true,
                    'diasAtras'     => 1,
                    'obs'           => 'Devolução de sobras de procedimento cirúrgico - aguardando conferência física na farmácia.',
                    'aprovador'     => null,
                    'liberacao'     => 'nenhuma',
                ],
            ];

            foreach ($cenarios as $c) {
                $dataMov = $now->copy()->subDays($c['diasAtras'])->setTime(rand(8, 18), rand(5, 55));
                $isDevolucao = !empty($c['is_devolucao']);

                $origemId = $isDevolucao ? $setor->id : $distribuidor->id;
                $destinoId = $isDevolucao ? $distribuidor->id : $setor->id;

                $mov = Movimentacao::create([
                    'usuario_id'           => $userSolicitante->id,
                    'setor_origem_id'      => $origemId,
                    'setor_destino_id'     => $destinoId,
                    'tipo'                 => $c['tipo'],
                    'data_hora'            => $dataMov,
                    'observacao'           => $c['obs'],
                    'status_solicitacao'   => $c['status'],
                    'aprovador_usuario_id' => $c['aprovador'],
                    'created_at'           => $dataMov,
                    'updated_at'           => $dataMov,
                ]);

                // 2 a 4 itens por movimentação
                $numItens = min(rand(2, 4), $produtosComSaldo->count());
                $itensEscolhidos = $produtosComSaldo->random($numItens);

                foreach ($itensEscolhidos as $prod) {
                    $qtdSolicitada = rand(10, 35);
                    $qtdLiberada = 0;
                    $loteJson = null;

                    if ($c['liberacao'] === 'total') {
                        $qtdLiberada = $qtdSolicitada;
                    } elseif ($c['liberacao'] === 'parcial') {
                        $qtdLiberada = max(1, $qtdSolicitada - rand(5, 12));
                    }

                    // Se houve liberação de estoque
                    if ($qtdLiberada > 0) {
                        $loteEstoque = EstoqueLote::where('setor_id', $distribuidor->id)
                            ->where('produto_id', $prod->id)
                            ->where('quantidade_disponivel', '>', 0)
                            ->whereDate('data_vencimento', '>=', $now->toDateString())
                            ->first();

                        $codLoteUsado = $loteEstoque ? $loteEstoque->lote : 'L' . substr($now->year, 2) . '-' . rand(200, 800);
                        $vencLoteUsado = $loteEstoque ? $loteEstoque->data_vencimento : $now->copy()->addMonths(18)->toDateString();

                        $loteJson = json_encode([
                            [
                                'lote'            => $codLoteUsado,
                                'data_vencimento' => $vencLoteUsado,
                                'qtd'             => $qtdLiberada,
                            ]
                        ]);

                        if ($isDevolucao) {
                            // Devolução aceita: adiciona saldo de volta ao distribuidor
                            $estDist = Estoque::where('setor_id', $distribuidor->id)->where('produto_id', $prod->id)->first();
                            if ($estDist) {
                                $estDist->quantidade_atual += $qtdLiberada;
                                $estDist->save();
                            }
                            if ($setor->estoque) {
                                $estOrigem = Estoque::where('setor_id', $setor->id)->where('produto_id', $prod->id)->first();
                                if ($estOrigem && $estOrigem->quantidade_atual >= $qtdLiberada) {
                                    $estOrigem->quantidade_atual -= $qtdLiberada;
                                    $estOrigem->save();
                                }
                            }
                        } else {
                            // Baixar do distribuidor com segurança de não negativar
                            $estDist = Estoque::where('setor_id', $distribuidor->id)->where('produto_id', $prod->id)->first();
                            if ($estDist) {
                                if ($estDist->quantidade_atual < $qtdLiberada) {
                                    $estDist->quantidade_atual += ($qtdLiberada + 50);
                                }
                                $estDist->quantidade_atual -= $qtdLiberada;
                                $estDist->save();
                            }

                            // Se o setor solicitante também gerencia estoque (ex: transferência entre farmácias), incrementa
                            if ($setor->estoque) {
                                $estDest = Estoque::firstOrCreate(
                                    ['setor_id' => $setor->id, 'produto_id' => $prod->id],
                                    ['quantidade_minima' => 20, 'quantidade_atual' => 0, 'status_disponibilidade' => 'D']
                                );
                                $estDest->quantidade_atual += $qtdLiberada;
                                $estDest->status_disponibilidade = 'D';
                                $estDest->save();
                            }
                        }
                    }

                    ItemMovimentacao::create([
                        'movimentacao_id'       => $mov->id,
                        'produto_id'            => $prod->id,
                        'quantidade_solicitada' => $qtdSolicitada,
                        'quantidade_liberada'   => $qtdLiberada,
                        'lote'                  => $loteJson,
                        'created_at'            => $dataMov,
                        'updated_at'            => $dataMov,
                    ]);
                }

                $movimentacoesCriadas++;
            }
        }

        // Cenário adicional específico para Medicamentos Controlados em setores críticos (UTIs e Centro Cirúrgico)
        $this->gerarMovimentacoesControladas($cafHGVC, $userSolicitante, $userAlmoxarife, $now);

        $this->command->info("  ✓ {$movimentacoesCriadas} movimentações geradas abrangendo todos os setores.");
    }

    /**
     * Gera movimentações de medicamentos controlados para alimentar o relatório da Portaria 344/98.
     */
    private function gerarMovimentacoesControladas($distribuidor, $solicitante, $almoxarife, $now)
    {
        $produtosControlados = $this->produtos->filter(fn($p) => $p->grupoProduto?->controlado)->values();
        if ($produtosControlados->isEmpty()) return;

        // Setores hospitalares críticos que consomem medicamentos controlados
        $setoresCriticos = $this->setores->filter(function ($s) {
            $n = strtoupper($s->nome);
            return str_contains($n, 'UTI') || str_contains($n, 'CIRÚRGICO') || str_contains($n, 'VERMELHA') || str_contains($n, 'TRAUMA');
        })->take(4);

        foreach ($setoresCriticos as $setor) {
            $dataMov = $now->copy()->subDays(rand(2, 18))->setTime(rand(9, 16), rand(0, 50));

            $mov = Movimentacao::create([
                'usuario_id'           => $solicitante->id,
                'setor_origem_id'      => $distribuidor->id,
                'setor_destino_id'     => $setor->id,
                'tipo'                 => 'S',
                'data_hora'            => $dataMov,
                'observacao'           => 'Requisição especial de sedativos e opioides (Portaria 344/98) com receituário retido.',
                'status_solicitacao'   => 'A',
                'aprovador_usuario_id' => $almoxarife->id,
                'created_at'           => $dataMov,
                'updated_at'           => $dataMov,
            ]);

            $itensCtrl = $produtosControlados->random(min(3, $produtosControlados->count()));
            foreach ($itensCtrl as $ctrl) {
                $qtd = rand(5, 15);
                ItemMovimentacao::create([
                    'movimentacao_id'       => $mov->id,
                    'produto_id'            => $ctrl->id,
                    'quantidade_solicitada' => $qtd,
                    'quantidade_liberada'   => $qtd,
                    'lote'                  => json_encode([[
                        'lote'            => 'L' . substr($now->year, 2) . '-CTRL-' . rand(10, 99),
                        'data_vencimento' => $now->copy()->addMonths(20)->toDateString(),
                        'qtd'             => $qtd
                    ]]),
                    'created_at'            => $dataMov,
                    'updated_at'            => $dataMov,
                ]);
            }
        }
    }

    /**
     * Auxiliar: seleciona fornecedor apropriado ao tipo de material/medicamento.
     */
    private function selecionarFornecedorApropriado(string $tipoSetor): Fornecedor
    {
        if ($tipoSetor === 'Material') {
            $fornecedor = $this->fornecedores->first(function ($f) {
                $n = strtoupper($f->razao_social_nome);
                return str_contains($n, 'CREMER') || str_contains($n, 'BD') || str_contains($n, 'MEDIX') || str_contains($n, 'SUPRIMENTOS');
            });
            return $fornecedor ?? $this->fornecedores->random();
        }

        $fornecedor = $this->fornecedores->first(function ($f) {
            $n = strtoupper($f->razao_social_nome);
            return str_contains($n, 'CRISTÁLIA') || str_contains($n, 'EUROFARMA') || str_contains($n, 'FRESENIUS') || str_contains($n, 'ELFA') || str_contains($n, 'SANTA CRUZ');
        });

        return $fornecedor ?? $this->fornecedores->random();
    }

    /**
     * Auxiliar: calcula preço unitário realista de acordo com grupo e tipo do produto.
     */
    private function calcularPrecoRealista(Produto $produto): float
    {
        $grupoNome = strtoupper($produto->grupoProduto?->nome ?? '');

        if (str_contains($grupoNome, 'CONTROLADO')) {
            return round(rand(550, 4800) / 100, 2); // R$ 5,50 a R$ 48,00
        }
        if (str_contains($grupoNome, 'ANTIBIÓTICO')) {
            return round(rand(850, 6500) / 100, 2); // R$ 8,50 a R$ 65,00
        }
        if (str_contains($grupoNome, 'INJETÁVEL') || str_contains($grupoNome, 'INJETAVEIS')) {
            return round(rand(320, 2800) / 100, 2); // R$ 3,20 a R$ 28,00
        }
        if (str_contains($grupoNome, 'SOLUÇ') || str_contains($grupoNome, 'SORO')) {
            return round(rand(450, 1600) / 100, 2); // R$ 4,50 a R$ 16,00
        }
        if (str_contains($grupoNome, 'LIMPEZA') || str_contains($grupoNome, 'HIGIENE')) {
            return round(rand(350, 2400) / 100, 2); // R$ 3,50 a R$ 24,00
        }
        if (str_contains($grupoNome, 'EXPEDIENTE')) {
            return round(rand(120, 3200) / 100, 2); // R$ 1,20 a R$ 32,00
        }

        // Medicamentos orais e materiais comuns
        return round(rand(35, 1250) / 100, 2); // R$ 0,35 a R$ 12,50
    }
}
