<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Setores;
use App\Models\Polo;
use App\Models\Produto;
use App\Models\Entrada;
use App\Models\Movimentacao;
use App\Models\Fornecedor;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

class RelatorioFinanceiroIntegridadeTest extends TestCase
{
    use RefreshDatabase;

    protected $polo;
    protected $setorOrigem;
    protected $setorDestino;
    protected $fornecedor;
    protected $admin;
    protected $produto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->polo = Polo::create(['nome' => 'Polo Central Teste', 'status' => 'A']);

        $this->setorOrigem = Setores::create([
            'nome' => 'CAF Almoxarifado Central',
            'tipo' => 'Ambos',
            'estoque' => true,
            'polo_id' => $this->polo->id,
            'status' => 'A'
        ]);

        $this->setorDestino = Setores::create([
            'nome' => 'Enfermaria Central',
            'tipo' => 'Ambos',
            'estoque' => false,
            'polo_id' => $this->polo->id,
            'status' => 'A'
        ]);

        $this->fornecedor = Fornecedor::create([
            'razao_social_nome' => 'Fornecedor Distribuidora S/A',
            'tipo_pessoa' => 'J',
            'cnpj' => '11222333000144',
            'status' => 'A'
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Administrador Teste',
            'email' => 'admin_integridade@gmail.com',
        ]);

        $this->produto = Produto::factory()->create([
            'nome' => 'Dipirona Sódica 500mg/ml',
            'codigo_simpas' => 'SIMP-DIP500',
            'status' => 'A'
        ]);
    }

    /**
     * Cenário 1:
     * - Criar lote de entrada com valor unitário R$ 10,00 e atender saída de 5 unidades.
     * - Criar segundo lote do mesmo produto com valor unitário R$ 12,00 e atender saída de 3 unidades.
     * - Consultar endpoint financeiro de saídas e garantir que o valor total reflita exatamente
     *   R$ 86,00 (5x10 + 3x12), respeitando a rastreabilidade do lote.
     */
    public function test_consulta_financeira_saidas_usa_custo_do_lote_e_agrupa_corretamente()
    {
        Sanctum::actingAs($this->admin, ['*']);

        // 1. Criar lote de entrada 1 com valor unitário R$ 10,00
        $entrada1 = Entrada::create([
            'nota_fiscal' => 'NF-001',
            'setor_id' => $this->setorOrigem->id,
            'fornecedor_id' => $this->fornecedor->id,
            'data_entrada' => now(),
            'status' => 'A'
        ]);

        DB::table('itens_entrada')->insert([
            'entrada_id' => $entrada1->id,
            'produto_id' => $this->produto->id,
            'quantidade' => 100,
            'lote' => 'LOTE-VALOR-10',
            'data_vencimento' => now()->addYear(),
            'valor_unitario' => 10.00
        ]);

        // 2. Criar lote de entrada 2 do mesmo produto com valor unitário R$ 12,00
        $entrada2 = Entrada::create([
            'nota_fiscal' => 'NF-002',
            'setor_id' => $this->setorOrigem->id,
            'fornecedor_id' => $this->fornecedor->id,
            'data_entrada' => now(),
            'status' => 'A'
        ]);

        DB::table('itens_entrada')->insert([
            'entrada_id' => $entrada2->id,
            'produto_id' => $this->produto->id,
            'quantidade' => 100,
            'lote' => 'LOTE-VALOR-12',
            'data_vencimento' => now()->addYear(),
            'valor_unitario' => 12.00
        ]);

        // 3. Atender saídas correspondentes:
        // Aprovador obrigatório devido ao trigger de auditoria de movimentações aprovadas ('A')
        $movimentacaoSaida = Movimentacao::create([
            'setor_origem_id' => $this->setorOrigem->id,
            'setor_destino_id' => $this->setorDestino->id,
            'usuario_id' => $this->admin->id,
            'aprovador_usuario_id' => $this->admin->id,
            'tipo' => 'S',
            'status_solicitacao' => 'A', // Aprovada / Atendida
            'data_hora' => now()
        ]);

        // Atende 5 unidades do lote de R$ 10,00 (subtotal esperado: R$ 50,00)
        DB::table('item_movimentacao')->insert([
            'movimentacao_id' => $movimentacaoSaida->id,
            'produto_id' => $this->produto->id,
            'quantidade_solicitada' => 5,
            'quantidade_liberada' => 5,
            'lote' => 'LOTE-VALOR-10'
        ]);

        // Atende 3 unidades do lote de R$ 12,00 (subtotal esperado: R$ 36,00)
        DB::table('item_movimentacao')->insert([
            'movimentacao_id' => $movimentacaoSaida->id,
            'produto_id' => $this->produto->id,
            'quantidade_solicitada' => 3,
            'quantidade_liberada' => 3,
            'lote' => 'LOTE-VALOR-12'
        ]);

        // 4. Consultar endpoint financeiro de saídas
        $response = $this->postJson('/api/relatorios/financeiro/saidas', []);

        $response->assertStatus(200);

        $dados = $response->json('data.data');
        $this->assertNotEmpty($dados, 'A lista de saídas financeiras não pode estar vazia');

        // Validar item do Lote 1 (R$ 10,00 * 5 = R$ 50,00)
        $itemLote10 = collect($dados)->firstWhere('lote', 'LOTE-VALOR-10');
        $this->assertNotNull($itemLote10, 'Lote LOTE-VALOR-10 não encontrado no relatório');
        $this->assertEquals(10.00, (float)$itemLote10['valor_unitario']);
        $this->assertEquals(5, (float)$itemLote10['quantidade']);
        $this->assertEquals(50.00, (float)$itemLote10['valor_total']);

        // Validar item do Lote 2 (R$ 12,00 * 3 = R$ 36,00)
        $itemLote12 = collect($dados)->firstWhere('lote', 'LOTE-VALOR-12');
        $this->assertNotNull($itemLote12, 'Lote LOTE-VALOR-12 não encontrado no relatório');
        $this->assertEquals(12.00, (float)$itemLote12['valor_unitario']);
        $this->assertEquals(3, (float)$itemLote12['quantidade']);
        $this->assertEquals(36.00, (float)$itemLote12['valor_total']);

        // 5. Garantir que o valor total reflita exatamente R$ 86,00 (5x10 + 3x12)
        $somaTotalCalculada = collect($dados)->sum('valor_total');
        $this->assertEquals(86.00, (float)$somaTotalCalculada, 'O valor total das saídas deve ser exatamente 86.00');

        // Se retornado total_valor / valor_total_geral
        if ($response->json('total_valor') !== null) {
            $this->assertEquals(86.00, (float)$response->json('total_valor'));
        }
    }

    /**
     * Cenário 2:
     * - Consultar endpoint que alimenta a impressão do pedido (/api/movimentacao/{id}).
     * - Garantir que o payload JSON retorne codigo_simpass no objeto do item e data formatada sem horas.
     */
    public function test_endpoint_movimentacao_impressao_inclui_codigo_simpass_e_omite_horas()
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Data de teste com horas explícitas
        $dataHoraBanco = '2026-09-17 16:45:30';

        $movimentacao = Movimentacao::create([
            'setor_origem_id' => $this->setorOrigem->id,
            'setor_destino_id' => $this->setorDestino->id,
            'usuario_id' => $this->admin->id,
            'aprovador_usuario_id' => $this->admin->id,
            'tipo' => 'S',
            'status_solicitacao' => 'A',
            'data_hora' => $dataHoraBanco,
            'observacao' => 'Requisição para Impressão com SIMPASS e Data Sem Horas'
        ]);

        DB::table('item_movimentacao')->insert([
            'movimentacao_id' => $movimentacao->id,
            'produto_id' => $this->produto->id,
            'quantidade_solicitada' => 8,
            'quantidade_liberada' => 8,
            'lote' => 'LOTE-SIMPASS-TEST'
        ]);

        // Consultar endpoint que alimenta a visualização e impressão do pedido
        $response = $this->getJson('/api/movimentacao/' . $movimentacao->id);

        $response->assertStatus(200);

        $payload = $response->json('data');
        $this->assertNotEmpty($payload, 'O payload da movimentação não pode estar vazio');

        // 1. Garantir que o payload JSON retorne codigo_simpass no objeto do item
        $this->assertNotEmpty($payload['itens'], 'A movimentação deve possuir itens');
        $item = $payload['itens'][0];

        $codigoSimpassItem = $item['codigo_simpass'] ?? $item['produto']['codigo_simpass'] ?? null;
        $this->assertEquals('SIMP-DIP500', $codigoSimpassItem, 'O código SIMPASS deve corresponder ao cadastrado no produto');

        // Confirmar que o atributo codigo_simpass existe no item ou produto
        $this->assertTrue(
            isset($item['codigo_simpass']) || isset($item['produto']['codigo_simpass']),
            'codigo_simpass deve estar presente no item retornado'
        );

        // 2. Garantir data formatada sem horas
        $dataFormatada = $payload['data_formatada'] ?? $payload['data'] ?? null;
        $this->assertNotNull($dataFormatada, 'data_formatada não pode ser nula');

        // Omite componente de horas (ex: não contém ":" e nem os dígitos 16:45)
        $this->assertStringNotContainsString(':', $dataFormatada, 'A data formatada não deve incluir horas');
        $this->assertStringNotContainsString('16:45', $dataFormatada);

        // Deve obedecer ao formato de data brasileira (dd/mm/yyyy) ou ISO (yyyy-mm-dd)
        $this->assertMatchesRegularExpression(
            '/^(\d{2}\/\d{2}\/\d{4}|\d{4}-\d{2}-\d{2})$/',
            $dataFormatada,
            'A data formatada deve ser sem horas no formato dd/mm/yyyy ou yyyy-mm-dd'
        );
    }
}
