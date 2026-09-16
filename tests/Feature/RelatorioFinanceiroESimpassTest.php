<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Setores;
use App\Models\Polo;
use App\Models\Produto;
use App\Models\Entrada;
use App\Models\Movimentacao;
use App\Models\Fornecedores;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

class RelatorioFinanceiroESimpassTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->polo = Polo::create(['nome' => 'Polo Teste', 'status' => 'A']);
        $this->setorFornecedor = Setores::create([
            'nome' => 'CAF',
            'tipo' => 'Ambos',
            'estoque' => true,
            'polo_id' => $this->polo->id,
            'status' => 'A'
        ]);
        
        $this->setorDestino = Setores::create([
            'nome' => 'UTI',
            'tipo' => 'Ambos',
            'estoque' => false,
            'polo_id' => $this->polo->id,
            'status' => 'A'
        ]);

        $this->fornecedor = Fornecedores::create([
            'razao_social_nome' => 'Fornecedor A',
            'status' => 'A'
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password')
        ]);

        $this->produto = Produto::create([
            'nome' => 'Produto Teste',
            'codigo_simpas' => 'SIMPAS123',
            'status' => 'A'
        ]);

        // Criar Entrada e Itens Entrada com Valor Unitário
        $this->entrada = Entrada::create([
            'setor_id' => $this->setorFornecedor->id,
            'fornecedor_id' => $this->fornecedor->id,
            'data_entrada' => now(),
            'status' => 'A'
        ]);

        DB::table('itens_entrada')->insert([
            'entrada_id' => $this->entrada->id,
            'produto_id' => $this->produto->id,
            'quantidade' => 100,
            'lote' => 'LOTE-X',
            'data_vencimento' => now()->addYear(),
            'valor_unitario' => 15.50
        ]);

        // Criar Movimentação de Saída aprovada com o lote correspondente
        $this->movimentacao = Movimentacao::create([
            'setor_origem_id' => $this->setorFornecedor->id,
            'setor_destino_id' => $this->setorDestino->id,
            'usuario_id' => $this->superAdmin->id,
            'tipo' => 'S',
            'status_solicitacao' => 'A', // Aprovado
            'data_hora' => now()
        ]);

        DB::table('item_movimentacao')->insert([
            'movimentacao_id' => $this->movimentacao->id,
            'produto_id' => $this->produto->id,
            'quantidade_solicitada' => 10,
            'quantidade_liberada' => 10,
            'lote' => 'LOTE-X'
        ]);
    }

    public function test_imprimir_detalhes_movimentacao_contem_codigo_simpas()
    {
        Sanctum::actingAs($this->superAdmin, ['*']);

        $response = $this->getJson('/api/movimentacao/' . $this->movimentacao->id);
        
        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Verifica se itens, produto e codigo_simpas estão presentes (simula comportamento da Impressão)
        $this->assertNotEmpty($data['itens']);
        $this->assertEquals('SIMPAS123', $data['itens'][0]['produto']['codigo_simpas']);
    }

    public function test_relatorio_financeiro_entradas_calcula_subtotal()
    {
        Sanctum::actingAs($this->superAdmin, ['*']);

        $response = $this->postJson('/api/relatorios/financeiro/entradas', []);
        
        $response->assertStatus(200);
        
        $dados = $response->json('data.data');
        $this->assertCount(1, $dados);
        $this->assertEquals(15.50, $dados[0]['valor_unitario']);
        $this->assertEquals(1550.00, $dados[0]['subtotal']); // 100 * 15.5
    }

    public function test_relatorio_financeiro_saidas_calcula_subtotal_baseado_lote()
    {
        Sanctum::actingAs($this->superAdmin, ['*']);

        $response = $this->postJson('/api/relatorios/financeiro/saidas', []);
        
        $response->assertStatus(200);
        
        $dados = $response->json('data.data');
        $this->assertCount(1, $dados);
        
        $itemReport = $dados[0];
        
        $this->assertEquals('LOTE-X', $itemReport['lote']);
        $this->assertEquals(15.50, $itemReport['valor_unitario']); // Capturou corretamente da entrada correspondente
        $this->assertEquals(155.00, $itemReport['valor_total']); // 10 * 15.5
    }
}
