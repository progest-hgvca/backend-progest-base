<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\TipoVinculo;
use Laravel\Sanctum\Sanctum;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Cria um tipo de vínculo de teste
        $this->tipoVinculo = TipoVinculo::firstOrCreate(
            ['nome' => 'Efetivo Teste'],
            ['descricao' => 'Efetivo', 'status' => 'A']
        );
    }

    private function generateValidCpf()
    {
        return '88852395105'; // CPF válido para teste
    }

    public function test_create_user_successfully()
    {
        $admin = User::factory()->create();
        Sanctum::actingAs($admin);

        $payload = [
            'user' => [
                'name' => 'John Doe',
                'email' => 'johndoe@example.com',
                'cpf' => $this->generateValidCpf(),
                'data_nascimento' => '1990-01-01',
                'telefone' => '11999999999',
                'tipo_vinculo' => $this->tipoVinculo->id,
                'status' => 'A',
                'password' => 'Password123'
            ]
        ];

        $response = $this->postJson('/api/user/add', $payload);

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);

        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
            'name' => 'JOHN DOE',
            'status' => 'A'
        ]);
    }

    public function test_update_user_successfully()
    {
        $admin = User::factory()->create();
        Sanctum::actingAs($admin);

        $user = User::factory()->create([
            'cpf' => '06893242867'
        ]);

        $payload = [
            'user' => [
                'id' => $user->id,
                'name' => 'John Doe Updated',
                'email' => 'updated@example.com',
                'cpf' => '06893242867',
                'data_nascimento' => '1990-01-01',
                'telefone' => '11999999999',
                'tipo_vinculo' => $this->tipoVinculo->id,
                'status' => 'A'
            ]
        ];

        $response = $this->postJson('/api/user/update', $payload);

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'updated@example.com',
            'name' => 'JOHN DOE UPDATED'
        ]);
    }

    public function test_list_users()
    {
        $admin = User::factory()->create();
        Sanctum::actingAs($admin);

        User::factory()->count(3)->create();

        $response = $this->postJson('/api/user/list');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'data']);
    }

    public function test_delete_user_toggles_status()
    {
        $admin = User::factory()->create();
        Sanctum::actingAs($admin);

        $user = User::factory()->create(['status' => 'A', 'email' => 'someuser@example.com']);

        $response = $this->postJson('/api/user/delete/' . $user->id);

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'I'
        ]);
    }
}
