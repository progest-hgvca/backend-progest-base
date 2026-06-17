<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Estoque;
use App\Models\Setores;
use App\Models\Produto;
use App\Models\GrupoProduto;
use App\Models\UnidadeMedida;
use App\Models\Polo;

class EstoqueControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $setor;
    protected $produto;
    protected $estoque;

    protected function setUp(): void
    {
        parent::setUp();

        $polo = Polo::factory()->create();

        $this->setor = Setores::create([
            'polo_id' => $polo->id,
            'nome' => 'Setor Estoque Teste',
            'estoque' => true,
            'tipo' => 'Medicamento',
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
            'nome' => 'Produto Estoque Teste',
            'marca' => 'Marca Teste',
            'grupo_produto_id' => $grupo->id,
            'unidade_medida_id' => $unidadeMedida->id,
            'status' => 'A'
        ]);

        $this->estoque = Estoque::firstOrCreate(
            [
                'produto_id' => $this->produto->id,
                'setor_id' => $this->setor->id
            ],
            [
                'quantidade_atual' => 50,
                'quantidade_minima' => 10,
                'status_disponibilidade' => 'D'
            ]
        );
    }

    public function test_listar_por_setor()
    {
        $response = $this->getJson('/api/estoque/setor/' . $this->setor->id);

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'data']);
    }

    public function test_show_estoque()
    {
        $response = $this->getJson('/api/estoque/' . $this->estoque->id);

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);
    }

    public function test_atualizar_quantidade_minima()
    {
        $payload = [
            'quantidade_minima' => 25
        ];

        $response = $this->putJson('/api/estoque/' . $this->estoque->id . '/quantidade-minima', $payload);

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);

        $this->assertDatabaseHas('estoque', [
            'id' => $this->estoque->id,
            'quantidade_minima' => 25
        ]);
    }

    public function test_atualizar_status_estoque()
    {
        $payload = [
            'status_disponibilidade' => 'I'
        ];

        $response = $this->putJson('/api/estoque/' . $this->estoque->id . '/status', $payload);

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);

        $this->assertDatabaseHas('estoque', [
            'id' => $this->estoque->id,
            'status_disponibilidade' => 'I'
        ]);
    }
}
