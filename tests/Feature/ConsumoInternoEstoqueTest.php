<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Produto;
use App\Models\Setores;
use App\Models\Estoque;
use App\Models\EstoqueLote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConsumoInternoEstoqueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Configuração básica pode ser adicionada aqui se houver seeders padrões.
    }

    public function test_rejects_consumption_from_different_tenant_sector()
    {
        $user = User::factory()->create();
        $setorLogado = Setores::factory()->create();
        $setorOutro = Setores::factory()->create();
        $produto = Produto::factory()->create();
        
        // Vínculo do usuário
        DB::table('usuario_setor')->insert([
            'usuario_id' => $user->id,
            'setor_id' => $setorLogado->id,
            'perfil' => 'admin'
        ]);

        // Lote criado em OUTRO setor
        $lote = EstoqueLote::factory()->create([
            'setor_id' => $setorOutro->id,
            'produto_id' => $produto->id,
            'lote' => 'LOTE-X',
            'quantidade_disponivel' => 10,
        ]);

        $response = $this->actingAs($user)->postJson('/api/movimentacao/consumo-interno', [
            'produto_id' => $produto->id,
            'lote' => 'LOTE-X',
            'setor_id' => $setorLogado->id, // Usuário tenta consumir com seu setor ativo, mas lote é do outro
            'quantidade' => 5
        ]);

        $response->assertStatus(403)
                 ->assertJsonFragment([
                     'status' => false,
                     'message' => 'Lote não encontrado para este setor.'
                 ]);
    }

    public function test_rejects_consumption_with_negative_or_zero_quantity()
    {
        $user = User::factory()->create();
        $setor = Setores::factory()->create();
        $produto = Produto::factory()->create();
        
        DB::table('usuario_setor')->insert([
            'usuario_id' => $user->id,
            'setor_id' => $setor->id,
            'perfil' => 'admin'
        ]);

        $lote = EstoqueLote::factory()->create([
            'setor_id' => $setor->id,
            'produto_id' => $produto->id,
            'lote' => 'LOTE-Y',
            'quantidade_disponivel' => 10,
        ]);

        $response = $this->actingAs($user)->postJson('/api/movimentacao/consumo-interno', [
            'produto_id' => $produto->id,
            'lote' => 'LOTE-Y',
            'setor_id' => $setor->id,
            'quantidade' => 0
        ]);

        $response->assertStatus(422);
    }

    public function test_rejects_consumption_exceeding_available_stock()
    {
        $user = User::factory()->create();
        $setor = Setores::factory()->create();
        $produto = Produto::factory()->create();
        
        DB::table('usuario_setor')->insert([
            'usuario_id' => $user->id,
            'setor_id' => $setor->id,
            'perfil' => 'admin'
        ]);

        $lote = EstoqueLote::factory()->create([
            'setor_id' => $setor->id,
            'produto_id' => $produto->id,
            'lote' => 'LOTE-Z',
            'quantidade_disponivel' => 5,
        ]);

        $response = $this->actingAs($user)->postJson('/api/movimentacao/consumo-interno', [
            'produto_id' => $produto->id,
            'lote' => 'LOTE-Z',
            'setor_id' => $setor->id,
            'quantidade' => 10 // Maior que saldo 5
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'status' => false,
                     'message' => 'Saldo insuficiente no lote selecionado.'
                 ]);
    }

    public function test_successfully_consumes_internal_stock()
    {
        $user = User::factory()->create();
        $setor = Setores::factory()->create();
        $produto = Produto::factory()->create();
        
        DB::table('usuario_setor')->insert([
            'usuario_id' => $user->id,
            'setor_id' => $setor->id,
            'perfil' => 'admin'
        ]);

        $lote = EstoqueLote::factory()->create([
            'setor_id' => $setor->id,
            'produto_id' => $produto->id,
            'lote' => 'LOTE-OK',
            'quantidade_disponivel' => 10,
        ]);

        $estoque = Estoque::factory()->create([
            'setor_id' => $setor->id,
            'produto_id' => $produto->id,
            'quantidade_atual' => 10,
            'status_disponibilidade' => 'D'
        ]);

        $response = $this->actingAs($user)->postJson('/api/movimentacao/consumo-interno', [
            'produto_id' => $produto->id,
            'lote' => 'LOTE-OK',
            'setor_id' => $setor->id,
            'quantidade' => 3,
            'observacao' => 'Teste consumo'
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'status' => true
                 ]);

        // Assert Lote decreased
        $this->assertDatabaseHas('estoque_lote', [
            'id' => $lote->id,
            'quantidade_disponivel' => 7
        ]);

        // Assert Estoque decreased
        $this->assertDatabaseHas('estoque', [
            'id' => $estoque->id,
            'quantidade_atual' => 7
        ]);

        // Assert Movimentacao saved
        $this->assertDatabaseHas('movimentacao', [
            'tipo' => 'C',
            'setor_origem_id' => $setor->id,
            'setor_destino_id' => $setor->id,
            'usuario_id' => $user->id,
            'status_solicitacao' => 'A'
        ]);
        
        // Assert ItemMovimentacao saved
        $this->assertDatabaseHas('item_movimentacao', [
            'produto_id' => $produto->id,
            'quantidade_liberada' => 3
        ]);
    }
}
