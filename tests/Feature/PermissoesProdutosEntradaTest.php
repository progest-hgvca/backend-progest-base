<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Setores;
use App\Models\Polo;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

class PermissoesProdutosEntradaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar o ambiente inicial de polo e setor CAF
        $this->polo = Polo::create(['nome' => 'Polo Teste', 'status' => 'A']);
        $this->caf = Setores::create([
            'nome' => 'CAF',
            'tipo' => 'Ambos',
            'estoque' => true,
            'polo_id' => $this->polo->id,
            'status' => 'A'
        ]);
        
        $this->clinica = Setores::create([
            'nome' => 'Clinica',
            'tipo' => 'Ambos',
            'estoque' => false,
            'polo_id' => $this->polo->id,
            'status' => 'A'
        ]);

        // Usuário superadmin
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password')
        ]);

        // Usuário admin de setor
        $this->adminSetor = User::create([
            'name' => 'Admin Setor',
            'email' => 'adminsetor@teste.com',
            'password' => bcrypt('password')
        ]);
        DB::table('usuario_setor')->insert([
            'usuario_id' => $this->adminSetor->id,
            'setor_id' => $this->caf->id,
            'perfil' => 'admin'
        ]);

        // Usuário almoxarife de setor (não admin)
        $this->almoxarife = User::create([
            'name' => 'Almoxarife',
            'email' => 'almoxarife@teste.com',
            'password' => bcrypt('password')
        ]);
        DB::table('usuario_setor')->insert([
            'usuario_id' => $this->almoxarife->id,
            'setor_id' => $this->caf->id,
            'perfil' => 'almoxarife'
        ]);

        // Produto base
        $this->produto = Produto::create([
            'nome' => 'Produto Teste',
            'status' => 'A'
        ]);
    }

    public function test_superadmin_can_manage_products()
    {
        Sanctum::actingAs($this->superAdmin, ['*']);

        $response = $this->postJson('/api/produtos/add', [
            'produto' => [
                'nome' => 'Novo Produto',
                'status' => 'A'
            ]
        ]);
        $response->assertStatus(200);

        $responseDelete = $this->deleteJson('/api/produtos/delete/' . $this->produto->id);
        $responseDelete->assertStatus(200);
    }

    public function test_admin_setor_caf_can_manage_products()
    {
        Sanctum::actingAs($this->adminSetor, ['*']);

        $response = $this->postJson('/api/produtos/add', [
            'produto' => [
                'nome' => 'Novo Produto',
                'status' => 'A'
            ]
        ]);
        $response->assertStatus(200);
    }

    public function test_almoxarife_cannot_manage_products()
    {
        Sanctum::actingAs($this->almoxarife, ['*']);

        $response = $this->postJson('/api/produtos/add', [
            'produto' => [
                'nome' => 'Novo Produto',
                'status' => 'A'
            ]
        ]);
        // Alterado de acordo com a trava feita que agora retorna 403
        $response->assertStatus(403);
    }

    public function test_almoxarife_can_register_entrada()
    {
        Sanctum::actingAs($this->almoxarife, ['*']);

        // Teste de permissão de acesso ao endpoint de entrada
        $response = $this->postJson('/api/entrada/add', [
            'unidade_id' => $this->caf->id,
            'data_entrada' => '2023-10-10',
            'origem' => 'Fornecedor',
            'numero_nota' => '123',
            'itens' => [
                [
                    'produto_id' => $this->produto->id,
                    'quantidade' => 10,
                    'lote' => 'LOTE1',
                    'validade' => '2025-10-10'
                ]
            ]
        ]);
        
        // Se a validação passar, o teste está garantindo que não deu erro de autorização.
        // O código de retorno vai depender se faltam outros dados no mock, mas não deve ser 403.
        $this->assertNotEquals(403, $response->status());
    }

    public function test_admin_setor_can_list_users()
    {
        Sanctum::actingAs($this->adminSetor, ['*']);

        $response = $this->getJson('/api/usuario-setor/listar-por-usuario?usuario_id=' . $this->almoxarife->id);
        
        // Deve conseguir listar os setores do almoxarife pois o adminSetor é admin de um setor
        $response->assertStatus(200);
    }
    
    public function test_almoxarife_cannot_list_users()
    {
        Sanctum::actingAs($this->almoxarife, ['*']);

        $response = $this->getJson('/api/usuario-setor/listar-por-usuario?usuario_id=' . $this->adminSetor->id);
        
        // Um almoxarife não pode listar as vinculações de outro usuário
        $response->assertStatus(403);
    }
}
