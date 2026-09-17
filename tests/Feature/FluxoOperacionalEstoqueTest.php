<?php

namespace Tests\Feature;

use App\Models\Estoque;
use App\Models\EstoqueLote;
use App\Models\ItemMovimentacao;
use App\Models\Movimentacao;
use App\Models\Produto;
use App\Models\Setores;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FluxoOperacionalEstoqueTest extends TestCase
{
    use RefreshDatabase;

    protected $distribuidor;
    protected $consumidor;
    protected $solicitante;
    protected $almoxarife;
    protected $produto;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setores (Distribuidor com estoque e Consumidor sem estoque físico)
        $this->distribuidor = Setores::factory()->create([
            'nome' => 'CAF Almoxarifado Central',
            'estoque' => 1
        ]);

        $this->consumidor = Setores::factory()->create([
            'nome' => 'Enfermaria Geral',
            'estoque' => 0
        ]);

        // Autoriza distribuidor para o solicitante
        DB::table('setor_distribuidor')->insert([
            'setor_solicitante_id' => $this->consumidor->id,
            'setor_distribuidor_id' => $this->distribuidor->id,
        ]);

        // 2. Usuários e Vínculos de Perfil
        $this->solicitante = User::factory()->create(['name' => 'Usuário Solicitante']);
        $this->almoxarife = User::factory()->create(['name' => 'Usuário Almoxarife']);

        DB::table('usuario_setor')->insert([
            ['usuario_id' => $this->solicitante->id, 'setor_id' => $this->consumidor->id, 'perfil' => 'solicitante'],
            ['usuario_id' => $this->almoxarife->id, 'setor_id' => $this->distribuidor->id, 'perfil' => 'almoxarife'],
        ]);

        // 3. Produto Base
        $this->produto = Produto::factory()->create([
            'nome' => 'Dipirona Sódica 500mg/ml'
        ]);

        // 4. Saldo Inicial no Distribuidor (100 unidades no lote LOTE-DIP-01)
        Estoque::updateOrCreate([
            'produto_id' => $this->produto->id,
            'setor_id' => $this->distribuidor->id,
        ], [
            'quantidade_atual' => 100,
            'quantidade_minima' => 10,
            'status_disponibilidade' => 'D'
        ]);

        EstoqueLote::create([
            'produto_id' => $this->produto->id,
            'setor_id' => $this->distribuidor->id,
            'lote' => 'LOTE-DIP-01',
            'quantidade_disponivel' => 100,
            'data_vencimento' => now()->addYear()->toDateString()
        ]);
    }

    /**
     * 1. Solicitante cria pedido para outro setor; saldo do distribuidor permanece inalterado até atendimento.
     */
    public function test_solicitante_cria_pedido_sem_afetar_saldo_imediato()
    {
        $payload = [
            'usuario_id' => $this->solicitante->id,
            'setor_origem_id' => $this->distribuidor->id,
            'setor_destino_id' => $this->consumidor->id,
            'tipo' => 'S',
            'status_solicitacao' => 'P',
            'itens' => [
                [
                    'produto_id' => $this->produto->id,
                    'quantidade_solicitada' => 25,
                ]
            ]
        ];

        $response = $this->actingAs($this->solicitante)->postJson('/api/movimentacao/add', $payload);

        $response->assertStatus(201)
                 ->assertJson(['status' => true]);

        // Valida que o pedido foi gravado como pendente (P)
        $this->assertDatabaseHas('movimentacao', [
            'usuario_id' => $this->solicitante->id,
            'setor_origem_id' => $this->distribuidor->id,
            'setor_destino_id' => $this->consumidor->id,
            'tipo' => 'S',
            'status_solicitacao' => 'P',
        ]);

        // O saldo do distribuidor DEVE permanecer estritamente inalterado (100 unidades)
        $saldoAtual = Estoque::where('produto_id', $this->produto->id)
            ->where('setor_id', $this->distribuidor->id)
            ->value('quantidade_atual');

        $loteSaldo = EstoqueLote::where('produto_id', $this->produto->id)
            ->where('setor_id', $this->distribuidor->id)
            ->where('lote', 'LOTE-DIP-01')
            ->value('quantidade_disponivel');

        $this->assertEquals(100, (float) $saldoAtual, 'O saldo do estoque não pode sofrer alteração na criação do pedido pendente.');
        $this->assertEquals(100, (float) $loteSaldo, 'O saldo do lote não pode sofrer alteração na criação do pedido pendente.');
    }

    /**
     * 2. Almoxarife aprova/atende; saldo do lote é decrementado atomicamente e aprovado_por registra o ID do almoxarife.
     */
    public function test_almoxarife_atende_pedido_com_deducao_estrita_e_lock()
    {
        // Cria pedido pendente de 25 unidades
        $mov = Movimentacao::create([
            'usuario_id' => $this->solicitante->id,
            'setor_origem_id' => $this->distribuidor->id,
            'setor_destino_id' => $this->consumidor->id,
            'tipo' => 'S',
            'status_solicitacao' => 'P',
            'data_hora' => now()
        ]);

        $item = ItemMovimentacao::create([
            'movimentacao_id' => $mov->id,
            'produto_id' => $this->produto->id,
            'quantidade_solicitada' => 25,
            'quantidade_liberada' => 0
        ]);

        // Almoxarife atende e aprova a liberação
        $response = $this->actingAs($this->almoxarife)->postJson("/api/movimentacao/{$mov->id}/process", [
            'action' => 'approve',
            'itens' => [
                [
                    'id' => $item->id,
                    'quantidade_liberada' => 25
                ]
            ]
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);

        // Valida registro de aprovação e auditoria do almoxarife
        $mov->refresh();
        $this->assertEquals('A', $mov->status_solicitacao);
        $this->assertEquals($this->almoxarife->id, $mov->aprovador_usuario_id);

        // Valida dedução atômica no estoque do distribuidor (100 - 25 = 75)
        $saldoEstoque = Estoque::where('produto_id', $this->produto->id)
            ->where('setor_id', $this->distribuidor->id)
            ->value('quantidade_atual');

        $saldoLote = EstoqueLote::where('produto_id', $this->produto->id)
            ->where('setor_id', $this->distribuidor->id)
            ->where('lote', 'LOTE-DIP-01')
            ->value('quantidade_disponivel');

        $this->assertEquals(75, (float) $saldoEstoque);
        $this->assertEquals(75, (float) $saldoLote);

        // Valida que o item gravou o lote e a quantidade liberada
        $item->refresh();
        $this->assertEquals(25, (float) $item->quantidade_liberada);
        $this->assertStringContainsString('LOTE-DIP-01', (string) $item->lote);
    }

    /**
     * 3. Setor com estoque consome lote próprio; baixa imediata sem necessidade de aprovação externa.
     */
    public function test_consumo_interno_baixa_imediata_no_proprio_setor()
    {
        // Saldo inicial do distribuidor: 100 unidades no lote LOTE-DIP-01
        $response = $this->actingAs($this->almoxarife)->postJson('/api/movimentacao/consumo-interno', [
            'produto_id' => $this->produto->id,
            'setor_id' => $this->distribuidor->id,
            'lote' => 'LOTE-DIP-01',
            'quantidade' => 15,
            'observacao' => 'Uso em procedimento ambulatorial do setor'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);

        // Baixa imediata de 15 unidades sem pendência de aprovação (100 - 15 = 85)
        $saldoEstoque = Estoque::where('produto_id', $this->produto->id)
            ->where('setor_id', $this->distribuidor->id)
            ->value('quantidade_atual');

        $saldoLote = EstoqueLote::where('produto_id', $this->produto->id)
            ->where('setor_id', $this->distribuidor->id)
            ->where('lote', 'LOTE-DIP-01')
            ->value('quantidade_disponivel');

        $this->assertEquals(85, (float) $saldoEstoque);
        $this->assertEquals(85, (float) $saldoLote);

        // Movimentação criada já como Aprovada (A) e do tipo Consumo (C)
        $this->assertDatabaseHas('movimentacao', [
            'setor_origem_id' => $this->distribuidor->id,
            'setor_destino_id' => $this->distribuidor->id,
            'tipo' => 'C',
            'status_solicitacao' => 'A',
            'aprovador_usuario_id' => $this->almoxarife->id
        ]);
    }

    /**
     * 4. Devolução de item atendido credita saldo exatamente no mesmo lote de origem e valida teto máximo atendido.
     */
    public function test_devolucao_restitui_saldo_ao_lote_original()
    {
        // Estado inicial pós-atendimento:
        // 20 unidades foram liberadas para o setor consumidor a partir do LOTE-DIP-01
        // O distribuidor ficou com 80 unidades no saldo e no lote
        Estoque::where('produto_id', $this->produto->id)
            ->where('setor_id', $this->distribuidor->id)
            ->update(['quantidade_atual' => 80]);

        EstoqueLote::where('produto_id', $this->produto->id)
            ->where('setor_id', $this->distribuidor->id)
            ->where('lote', 'LOTE-DIP-01')
            ->update(['quantidade_disponivel' => 80]);

        $movOriginal = Movimentacao::create([
            'usuario_id' => $this->solicitante->id,
            'setor_origem_id' => $this->distribuidor->id,
            'setor_destino_id' => $this->consumidor->id,
            'tipo' => 'S',
            'status_solicitacao' => 'A',
            'aprovador_usuario_id' => $this->almoxarife->id,
            'data_hora' => now()
        ]);

        $itemOriginal = ItemMovimentacao::create([
            'movimentacao_id' => $movOriginal->id,
            'produto_id' => $this->produto->id,
            'quantidade_solicitada' => 20,
            'quantidade_liberada' => 20,
            'lote' => json_encode([
                [
                    'lote' => 'LOTE-DIP-01',
                    'data_vencimento' => now()->addYear()->toDateString(),
                    'qtd' => 20
                ]
            ])
        ]);

        // A) Validação de Teto Máximo: Tentar devolver mais do que o liberado (ex: 25 > 20) deve falhar com 422
        $responseExcede = $this->actingAs($this->solicitante)->postJson("/api/movimentacao/{$movOriginal->id}/devolver", [
            'motivo' => 'Tentativa irregular de devolver acima do recebido',
            'itens' => [
                [
                    'item_movimentacao_id' => $itemOriginal->id,
                    'quantidade_devolvendo' => 25
                ]
            ]
        ]);

        $responseExcede->assertStatus(422);

        // B) Solicitação de Devolução Válida (devolver 8 unidades das 20 recebidas)
        $responseDev = $this->actingAs($this->solicitante)->postJson("/api/movimentacao/{$movOriginal->id}/devolver", [
            'motivo' => 'Devolução de sobra de medicamentos',
            'itens' => [
                [
                    'item_movimentacao_id' => $itemOriginal->id,
                    'quantidade_devolvendo' => 8
                ]
            ]
        ]);

        $responseDev->assertStatus(200);

        // Verifica que a movimentação de devolução (D) foi criada como pendente (P)
        $movDevolucao = Movimentacao::where('tipo', 'D')
            ->where('setor_origem_id', $this->consumidor->id)
            ->where('setor_destino_id', $this->distribuidor->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($movDevolucao);
        $this->assertEquals('P', $movDevolucao->status_solicitacao);

        // Saldo do distribuidor AINDA não deve ter mudado antes da aprovação
        $this->assertEquals(80, (float) Estoque::where('produto_id', $this->produto->id)->where('setor_id', $this->distribuidor->id)->value('quantidade_atual'));
        $this->assertEquals(80, (float) EstoqueLote::where('produto_id', $this->produto->id)->where('setor_id', $this->distribuidor->id)->where('lote', 'LOTE-DIP-01')->value('quantidade_disponivel'));

        // C) Almoxarife faz a conferência física e aprova a devolução
        $responseAprovacao = $this->actingAs($this->almoxarife)->postJson("/api/movimentacao/{$movDevolucao->id}/process", [
            'action' => 'approve'
        ]);

        $responseAprovacao->assertStatus(200);

        // Valida que o saldo foi restituído exatamente no lote original (80 + 8 = 88)
        $saldoRestituido = Estoque::where('produto_id', $this->produto->id)
            ->where('setor_id', $this->distribuidor->id)
            ->value('quantidade_atual');

        $loteRestituido = EstoqueLote::where('produto_id', $this->produto->id)
            ->where('setor_id', $this->distribuidor->id)
            ->where('lote', 'LOTE-DIP-01')
            ->value('quantidade_disponivel');

        $this->assertEquals(88, (float) $saldoRestituido, 'O saldo geral do estoque deve ser restituído após a aprovação da devolução.');
        $this->assertEquals(88, (float) $loteRestituido, 'O lote original deve ser creditado com a quantidade devolvida.');

        $movDevolucao->refresh();
        $this->assertEquals('A', $movDevolucao->status_solicitacao);
        $this->assertEquals($this->almoxarife->id, $movDevolucao->aprovador_usuario_id);
    }
}
