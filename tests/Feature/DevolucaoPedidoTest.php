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

class DevolucaoPedidoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        $this->setorDistribuidor = Setores::factory()->create();
        $this->setorConsumidor = Setores::factory()->create();

        DB::table('usuario_setor')->insert([
            ['usuario_id' => $this->user->id, 'setor_id' => $this->setorConsumidor->id, 'perfil' => 'solicitante'],
            ['usuario_id' => $this->user->id, 'setor_id' => $this->setorDistribuidor->id, 'perfil' => 'admin'],
        ]);

        $this->produto = Produto::factory()->create(['nome' => 'Produto Teste Devolução']);
        
        $this->movimentacao = Movimentacao::create([
            'usuario_id' => $this->user->id,
            'setor_origem_id' => $this->setorDistribuidor->id,
            'setor_destino_id' => $this->setorConsumidor->id,
            'tipo' => 'S', // Saída/Pedido
            'status_solicitacao' => 'A', // Atendido
            'data_hora' => now()
        ]);

        $this->itemMovimentacao = ItemMovimentacao::create([
            'movimentacao_id' => $this->movimentacao->id,
            'produto_id' => $this->produto->id,
            'quantidade_solicitada' => 10,
            'quantidade_liberada' => 10,
            'lote' => json_encode([
                ['lote' => 'LOTE-TESTE-1', 'quantidade' => 5],
                ['lote' => 'LOTE-TESTE-2', 'quantidade' => 5]
            ])
        ]);

        // Cria estoque no distribuidor (saldo que vai ser creditado ao devolver)
        Estoque::create([
            'produto_id' => $this->produto->id,
            'setor_id' => $this->setorDistribuidor->id,
            'quantidade_atual' => 50,
            'quantidade_minima' => 10,
            'status_disponibilidade' => 'D'
        ]);

        EstoqueLote::create([
            'produto_id' => $this->produto->id,
            'setor_id' => $this->setorDistribuidor->id,
            'lote' => 'LOTE-TESTE-1',
            'quantidade_disponivel' => 25,
            'data_vencimento' => now()->addYear()->toDateString()
        ]);
        
        EstoqueLote::create([
            'produto_id' => $this->produto->id,
            'setor_id' => $this->setorDistribuidor->id,
            'lote' => 'LOTE-TESTE-2',
            'quantidade_disponivel' => 25,
            'data_vencimento' => now()->addYear()->toDateString()
        ]);
    }

    public function test_rejeicao_quantidade_zero_ou_negativa()
    {
        $response = $this->actingAs($this->user)->postJson("/api/movimentacao/{$this->movimentacao->id}/devolver", [
            'item_movimentacao_id' => $this->itemMovimentacao->id,
            'lote' => 'LOTE-TESTE-1',
            'quantidade' => 0,
            'motivo' => 'Devolução'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['quantidade']);
    }

    public function test_rejeicao_quantidade_maior_que_atendida_no_lote()
    {
        $response = $this->actingAs($this->user)->postJson("/api/movimentacao/{$this->movimentacao->id}/devolver", [
            'item_movimentacao_id' => $this->itemMovimentacao->id,
            'lote' => 'LOTE-TESTE-1',
            'quantidade' => 6, // Atendido neste lote foi 5
            'motivo' => 'Devolução excessiva'
        ]);

        $response->assertStatus(422)
                 ->assertJsonPath('status', false)
                 ->assertJsonPath('message', 'Quantidade a devolver excede a quantidade atendida no pedido para este lote.');
    }

    public function test_sucesso_registro_devolucao_e_estorno_no_lote_e_estoque_geral()
    {
        $response = $this->actingAs($this->user)->postJson("/api/movimentacao/{$this->movimentacao->id}/devolver", [
            'item_movimentacao_id' => $this->itemMovimentacao->id,
            'lote' => 'LOTE-TESTE-1',
            'quantidade' => 3,
            'motivo' => 'Sobra'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', true);

        // Confirma se o lote do distribuidor teve saldo incrementado (+3)
        $this->assertDatabaseHas('estoque_lote', [
            'produto_id' => $this->produto->id,
            'setor_id' => $this->setorDistribuidor->id,
            'lote' => 'LOTE-TESTE-1',
            'quantidade_disponivel' => 28 // era 25
        ]);

        // Confirma se o estoque geral do distribuidor teve saldo incrementado (+3)
        $this->assertDatabaseHas('estoque', [
            'produto_id' => $this->produto->id,
            'setor_id' => $this->setorDistribuidor->id,
            'quantidade_atual' => 53 // era 50
        ]);

        // Confirma criação do registro de devolução
        $this->assertDatabaseHas('devolucoes', [
            'movimentacao_id' => $this->movimentacao->id,
            'item_movimentacao_id' => $this->itemMovimentacao->id,
            'lote' => 'LOTE-TESTE-1',
            'quantidade' => 3,
            'usuario_id' => $this->user->id
        ]);
    }

    public function test_busca_movimentacao_por_lote_na_listagem()
    {
        $this->actingAs($this->user)->postJson("/api/movimentacao/{$this->movimentacao->id}/devolver", [
            'item_movimentacao_id' => $this->itemMovimentacao->id,
            'lote' => 'LOTE-TESTE-1',
            'quantidade' => 1,
            'motivo' => ''
        ]);

        // Lista do setor distribuidor buscando por um lote devolvido / atendido
        $response = $this->actingAs($this->user)->postJson("/api/movimentacao/listBySetor", [
            'setor_id' => $this->setorDistribuidor->id,
            'lote' => 'LOTE-TESTE-1'
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertTrue($data[0]['tem_devolucao']);
        $this->assertEquals($this->movimentacao->id, $data[0]['id']);

        // Busca por um lote que não existe no item_movimentacao nem em devolucoes
        $responseNaoExiste = $this->actingAs($this->user)->postJson("/api/movimentacao/listBySetor", [
            'setor_id' => $this->setorDistribuidor->id,
            'lote' => 'LOTE-INEXISTENTE'
        ]);

        $responseNaoExiste->assertStatus(200);
        $dataNaoExiste = $responseNaoExiste->json('data');
        $this->assertCount(0, $dataNaoExiste);
    }
}
