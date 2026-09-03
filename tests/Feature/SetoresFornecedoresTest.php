<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Setores;
use App\Models\Polo;
use App\Models\SetorDistribuidor;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class SetoresFornecedoresTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::where('email', 'adminti@gmail.com')->first() 
            ?? User::factory()->create(['email' => 'adminti@gmail.com']);
        Sanctum::actingAs($admin);
    }

    public function test_create_setor_with_fornecedor_and_uniqueness()
    {
        // Limpar dados de testes anteriores
        Setores::where('nome', 'LIKE', 'FORNECEDOR A%')->delete();
        Setores::where('nome', 'LIKE', 'SOLICITANTE%')->delete();
        
        // Criar unidade
        $unidade = Polo::factory()->create();

        // Criar setor fornecedor existente
        $fornecedor = Setores::create([
            'polo_id' => $unidade->id,
            'nome' => 'FORNECEDOR A',
            'tipo' => 'Medicamento',
            'estoque' => false,
            'status' => 'A'
        ]);

        // Payload para criar setor solicitante com fornecedor
        $payload = [
            'Setores' => [
                'polo_id' => $unidade->id,
                'nome' => 'SOLICITANTE X',
                'tipo' => 'Medicamento',
                'estoque' => false
            ],
            'distribuidor' => [
                'setor_distribuidor_id' => $fornecedor->id
            ]
        ];

        $response = $this->postJson('/api/setores/add', $payload);
        $response->assertStatus(200)->assertJson(['status' => true]);

        $solicitante = Setores::where('nome', 'SOLICITANTE X')->first();
        $this->assertNotNull($solicitante);

        $rel = SetorDistribuidor::where('setor_solicitante_id', $solicitante->id)
            ->where('setor_distribuidor_id', $fornecedor->id)
            ->first();

        $this->assertNotNull($rel);

        // Criar outro solicitante com o mesmo fornecedor (deve funcionar)
        $payload2 = [
            'Setores' => [
                'polo_id' => $unidade->id,
                'nome' => 'SOLICITANTE Y',
                'tipo' => 'Medicamento',
                'estoque' => false
            ],
            'distribuidor' => [
                'setor_distribuidor_id' => $fornecedor->id
            ]
        ];

        $response2 = $this->postJson('/api/setores/add', $payload2);
        $response2->assertStatus(200)->assertJson(['status' => true]);
    }
}
