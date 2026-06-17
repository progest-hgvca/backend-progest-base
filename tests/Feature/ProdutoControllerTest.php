<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Produto;
use App\Models\GrupoProduto;
use App\Models\UnidadeMedida;

class ProdutoControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $grupo;
    protected $unidadeMedida;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->grupo = GrupoProduto::firstOrCreate(
            ['nome' => 'Medicamentos Teste'],
            ['status' => 'A']
        );

        $this->unidadeMedida = UnidadeMedida::firstOrCreate(
            ['nome' => 'Comprimido Teste'],
            ['status' => 'A']
        );
    }

    public function test_create_produto_successfully()
    {
        $payload = [
            'produto' => [
                'nome' => 'Paracetamol Teste',
                'marca' => 'Generico',
                'codigo_simpas' => 'SIMP-123',
                'codigo_barras' => '1234567890123',
                'grupo_produto_id' => $this->grupo->id,
                'unidade_medida_id' => $this->unidadeMedida->id,
                'status' => 'A'
            ]
        ];

        $response = $this->postJson('/api/produtos/add', $payload);

        $response->assertStatus(201)
                 ->assertJson(['status' => true]);

        $this->assertDatabaseHas('produtos', [
            'nome' => 'PARACETAMOL TESTE',
            'marca' => 'GENERICO',
            'codigo_simpas' => 'SIMP-123'
        ]);
    }

    public function test_update_produto_successfully()
    {
        $produto = Produto::create([
            'nome' => 'DIPIRONA ANTIGA',
            'marca' => 'MARCA ANTIGA',
            'codigo_simpas' => 'SIMP-OLD',
            'grupo_produto_id' => $this->grupo->id,
            'unidade_medida_id' => $this->unidadeMedida->id,
            'status' => 'A'
        ]);

        $payload = [
            'produto' => [
                'id' => $produto->id,
                'nome' => 'Dipirona Atualizada',
                'marca' => 'Marca A',
                'codigo_simpas' => 'SIMP-456',
                'grupo_produto_id' => $this->grupo->id,
                'unidade_medida_id' => $this->unidadeMedida->id,
                'status' => 'A'
            ]
        ];

        $response = $this->postJson('/api/produtos/update', $payload);

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);

        $this->assertDatabaseHas('produtos', [
            'id' => $produto->id,
            'nome' => 'DIPIRONA ATUALIZADA'
        ]);
    }

    public function test_list_produtos()
    {
        Produto::create([
            'nome' => 'LIST PRODUTO',
            'marca' => 'TESTE',
            'grupo_produto_id' => $this->grupo->id,
            'unidade_medida_id' => $this->unidadeMedida->id,
            'status' => 'A'
        ]);

        $response = $this->postJson('/api/produtos/list');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'data']);
    }

    public function test_toggle_status_produto()
    {
        $produto = Produto::create([
            'nome' => 'STATUS PRODUTO',
            'marca' => 'TESTE',
            'status' => 'A',
            'grupo_produto_id' => $this->grupo->id,
            'unidade_medida_id' => $this->unidadeMedida->id
        ]);

        $response = $this->postJson('/api/produtos/toggleStatus', ['id' => $produto->id]);

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);

        $this->assertDatabaseHas('produtos', [
            'id' => $produto->id,
            'status' => 'I'
        ]);
    }
}
