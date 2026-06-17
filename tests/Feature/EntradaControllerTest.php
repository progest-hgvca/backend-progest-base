<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Entrada;
use App\Models\Setores;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\GrupoProduto;
use App\Models\UnidadeMedida;
use App\Models\Polo;
use Carbon\Carbon;

class EntradaControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $setor;
    protected $fornecedor;
    protected $produto;

    protected function setUp(): void
    {
        parent::setUp();

        $polo = Polo::factory()->create();

        $this->setor = Setores::create([
            'polo_id' => $polo->id,
            'nome' => 'Setor Entrada Teste',
            'estoque' => true,
            'tipo' => 'Medicamento',
            'status' => 'A'
        ]);

        $this->fornecedor = Fornecedor::create([
            'razao_social_nome' => 'Fornecedor Teste Entrada',
            'tipo_pessoa' => 'J',
            'cnpj' => '45722158000185',
            'status' => 'A'
        ]);

        $grupo = GrupoProduto::firstOrCreate(
            ['nome' => 'Medicamentos Teste', 'tipo' => 'Medicamento'],
            ['status' => 'A']
        );

        $unidadeMedida = UnidadeMedida::firstOrCreate(
            ['nome' => 'Comprimido Teste'],
            ['status' => 'A']
        );

        $this->produto = Produto::create([
            'nome' => 'Produto Teste Entrada',
            'marca' => 'Marca Teste',
            'grupo_produto_id' => $grupo->id,
            'unidade_medida_id' => $unidadeMedida->id,
            'status' => 'A'
        ]);
    }

    public function test_create_entrada_successfully()
    {
        $payload = [
            'nota_fiscal' => 'NF-12345',
            'setor_id' => $this->setor->id,
            'fornecedor_id' => $this->fornecedor->id,
            'itens' => [
                [
                    'produto_id' => $this->produto->id,
                    'quantidade' => 100,
                    'lote' => 'LOTE-A',
                    'data_vencimento' => Carbon::now()->addYear()->format('Y-m-d')
                ]
            ]
        ];

        $response = $this->postJson('/api/entrada/add', $payload);

        $response->assertStatus(201)
                 ->assertJson(['status' => true]);

        $this->assertDatabaseHas('entrada', [
            'nota_fiscal' => 'NF-12345',
            'setor_id' => $this->setor->id,
            'fornecedor_id' => $this->fornecedor->id
        ]);

        $this->assertDatabaseHas('itens_entrada', [
            'produto_id' => $this->produto->id,
            'quantidade' => 100,
            'lote' => 'LOTE-A'
        ]);

        $this->assertDatabaseHas('estoque', [
            'setor_id' => $this->setor->id,
            'produto_id' => $this->produto->id,
            'quantidade_atual' => 100
        ]);
    }

    public function test_create_entrada_validation_fails()
    {
        $payload = [
            'nota_fiscal' => '',
            'setor_id' => 999, // Inexistente
        ];

        $response = $this->postJson('/api/entrada/add', $payload);

        $response->assertStatus(422)
                 ->assertJson(['status' => false, 'validacao' => true]);
    }

    public function test_list_entradas()
    {
        $payload = [
            'nota_fiscal' => 'NF-LIST-TEST',
            'setor_id' => $this->setor->id,
            'fornecedor_id' => $this->fornecedor->id,
            'itens' => [
                [
                    'produto_id' => $this->produto->id,
                    'quantidade' => 10,
                    'lote' => 'LOTE-B',
                    'data_vencimento' => Carbon::now()->addYear()->format('Y-m-d')
                ]
            ]
        ];
        $this->postJson('/api/entrada/add', $payload);

        $response = $this->postJson('/api/entrada/list');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'data' => ['data']]);
    }

    public function test_delete_entrada_successfully()
    {
        $payload = [
            'nota_fiscal' => 'NF-DEL-TEST',
            'setor_id' => $this->setor->id,
            'fornecedor_id' => $this->fornecedor->id,
            'itens' => [
                [
                    'produto_id' => $this->produto->id,
                    'quantidade' => 50,
                    'lote' => 'LOTE-DEL',
                    'data_vencimento' => Carbon::now()->addYear()->format('Y-m-d')
                ]
            ]
        ];
        $createResponse = $this->postJson('/api/entrada/add', $payload);
        $entradaId = $createResponse->json('data.id');

        $response = $this->postJson('/api/entrada/delete', ['id' => $entradaId]);

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);

        $this->assertDatabaseMissing('entrada', ['id' => $entradaId]);
        
        $this->assertDatabaseHas('estoque', [
            'setor_id' => $this->setor->id,
            'produto_id' => $this->produto->id,
            'quantidade_atual' => 0
        ]);
    }
}
