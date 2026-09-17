<?php

namespace Tests\Feature;

use App\Models\GrupoProduto;
use App\Models\Polo;
use App\Models\Produto;
use App\Models\Setores;
use App\Models\UnidadeMedida;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminPermissoesFluxoTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $solicitante;
    protected $almoxarife;
    protected $setor;
    protected $setorComEstoque;
    protected $grupo;
    protected $unidade;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Polos e Setores base para o teste
        $polo = Polo::factory()->create([
            'nome' => 'POLO CENTRAL',
            'status' => 'A'
        ]);

        $this->setor = Setores::factory()->create([
            'polo_id' => $polo->id,
            'nome' => 'ENFERMARIA GERAL',
            'estoque' => false,
            'status' => 'A',
        ]);

        $this->setorComEstoque = Setores::factory()->create([
            'polo_id' => $polo->id,
            'nome' => 'CENTRAL DE ABASTECIMENTO FARMACEUTICO (CAF)',
            'estoque' => true,
            'status' => 'A',
        ]);

        // 2. Administrador Comum / Setorial (pabloadmin@gmail.com)
        $this->admin = User::factory()->create([
            'name' => 'Pablo Admin',
            'email' => 'pabloadmin@gmail.com',
        ]);

        // Vincula admin com perfil 'admin' em ambos os setores
        DB::table('usuario_setor')->insert([
            [
                'usuario_id' => $this->admin->id,
                'setor_id' => $this->setor->id,
                'perfil' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => $this->admin->id,
                'setor_id' => $this->setorComEstoque->id,
                'perfil' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 3. Usuário Solicitante
        $this->solicitante = User::factory()->create([
            'name' => 'Jean Solicitante',
            'email' => 'solicitante@teste.com',
        ]);
        DB::table('usuario_setor')->insert([
            'usuario_id' => $this->solicitante->id,
            'setor_id' => $this->setor->id,
            'perfil' => 'solicitante',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Usuário Almoxarife
        $this->almoxarife = User::factory()->create([
            'name' => 'Arthur Almoxarife',
            'email' => 'almoxarife@teste.com',
        ]);
        DB::table('usuario_setor')->insert([
            'usuario_id' => $this->almoxarife->id,
            'setor_id' => $this->setorComEstoque->id,
            'perfil' => 'almoxarife',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Dependências para cadastro de produtos
        $this->grupo = GrupoProduto::factory()->create([
            'nome' => 'MEDICAMENTOS GERAIS',
            'tipo' => 'Medicamento',
            'controlado' => false,
        ]);

        $this->unidade = UnidadeMedida::create([
            'nome' => 'AMPOLA',
            'quantidade_unidade_minima' => 1,
            'status' => 'A',
        ]);
    }

    /**
     * 1. Admin consegue vincular usuário a setor sem bloqueio.
     */
    public function test_admin_consegue_vincular_usuario_a_setor_sem_bloqueio()
    {
        $novoUsuario = User::factory()->create(['name' => 'Novo Funcionario']);

        $payload = [
            'usuario_id' => $novoUsuario->id,
            'setor_id' => $this->setor->id,
            'perfil' => 'solicitante',
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/usuarioSetor/add', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('usuario_setor', [
            'usuario_id' => $novoUsuario->id,
            'setor_id' => $this->setor->id,
            'perfil' => 'solicitante',
        ]);
    }

    /**
     * 2. Solicitante e Almoxarife são bloqueados ao tentar vincular usuários (HTTP 403 Forbidden).
     */
    public function test_solicitante_e_almoxarife_sao_bloqueados_ao_tentar_vincular_usuarios()
    {
        $alvo = User::factory()->create(['name' => 'Usuario Alvo']);

        // Tentativa 1: Solicitante tenta vincular usuário
        $payloadSolicitante = [
            'usuario_id' => $alvo->id,
            'setor_id' => $this->setor->id,
            'perfil' => 'solicitante',
        ];
        $responseSolicitante = $this->actingAs($this->solicitante)->postJson('/api/usuarioSetor/add', $payloadSolicitante);
        $responseSolicitante->assertStatus(403);

        // Tentativa 2: Almoxarife tenta vincular usuário
        $payloadAlmoxarife = [
            'usuario_id' => $alvo->id,
            'setor_id' => $this->setorComEstoque->id,
            'perfil' => 'almoxarife',
        ];
        $responseAlmoxarife = $this->actingAs($this->almoxarife)->postJson('/api/usuarioSetor/add', $payloadAlmoxarife);
        $responseAlmoxarife->assertStatus(403);

        // Garantir que nenhum vínculo indevido foi persistido no banco
        $this->assertDatabaseMissing('usuario_setor', [
            'usuario_id' => $alvo->id,
        ]);
    }

    /**
     * 3. Apenas admin consegue criar, editar e excluir produtos:
     *    - Admin envia POST /api/produtos com sucesso (HTTP 201).
     *    - Almoxarife envia o mesmo payload e recebe HTTP 403.
     */
    public function test_apenas_admin_consegue_criar_editar_e_excluir_produtos()
    {
        $payloadCriacao = [
            'nome' => 'Dipirona Gotas 500mg/ml',
            'marca' => 'Medley',
            'grupo_produto_id' => $this->grupo->id,
            'unidade_medida_id' => $this->unidade->id,
            'status' => 'A',
        ];

        // 1. Almoxarife tenta criar produto -> HTTP 403 Forbidden
        $responseAlmox = $this->actingAs($this->almoxarife)->postJson('/api/produtos', $payloadCriacao);
        $responseAlmox->assertStatus(403);

        // 2. Admin cria produto com sucesso -> HTTP 201 Created
        $responseAdmin = $this->actingAs($this->admin)->postJson('/api/produtos', $payloadCriacao);
        $responseAdmin->assertStatus(201);
        $produtoId = $responseAdmin->json('data.id');
        $this->assertNotNull($produtoId);
        $this->assertDatabaseHas('produtos', [
            'id' => $produtoId,
            'nome' => 'Dipirona Gotas 500mg/ml',
        ]);

        // 3. Almoxarife tenta editar produto -> HTTP 403 Forbidden
        $payloadEdicao = [
            'produto' => [
                'id' => $produtoId,
                'nome' => 'Dipirona Gotas 500mg/ml Alterada',
                'marca' => 'EMS',
                'grupo_produto_id' => $this->grupo->id,
                'unidade_medida_id' => $this->unidade->id,
            ]
        ];
        $responseUpdateAlmox = $this->actingAs($this->almoxarife)->postJson('/api/produtos/update', $payloadEdicao);
        $responseUpdateAlmox->assertStatus(403);

        // 4. Admin edita produto com sucesso -> HTTP 200 OK
        $responseUpdateAdmin = $this->actingAs($this->admin)->postJson('/api/produtos/update', $payloadEdicao);
        $responseUpdateAdmin->assertStatus(200);
        $this->assertDatabaseHas('produtos', [
            'id' => $produtoId,
            'nome' => 'Dipirona Gotas 500mg/ml Alterada',
        ]);

        // 5. Almoxarife tenta excluir produto -> HTTP 403 Forbidden
        $responseDeleteAlmox = $this->actingAs($this->almoxarife)->deleteJson("/api/produtos/delete/{$produtoId}");
        $responseDeleteAlmox->assertStatus(403);

        // 6. Admin exclui (inativa) produto com sucesso -> HTTP 200 OK
        $responseDeleteAdmin = $this->actingAs($this->admin)->deleteJson("/api/produtos/delete/{$produtoId}");
        $responseDeleteAdmin->assertStatus(200);
        $this->assertDatabaseHas('produtos', [
            'id' => $produtoId,
            'status' => 'I',
        ]);
    }

    /**
     * 4. Admin visualiza todos os polos e setores sem restrições de tenant único.
     */
    public function test_admin_visualiza_todos_os_polos_e_setores()
    {
        // Cria múltiplos polos e setores em tenants/polos diferentes
        $poloA = Polo::factory()->create(['nome' => 'POLO REGIONAL LESTE', 'status' => 'A']);
        $poloB = Polo::factory()->create(['nome' => 'POLO REGIONAL OESTE', 'status' => 'A']);

        $setorA = Setores::factory()->create([
            'polo_id' => $poloA->id,
            'nome' => 'POSTO DE SAUDE LESTE',
            'status' => 'A',
        ]);

        $setorB = Setores::factory()->create([
            'polo_id' => $poloB->id,
            'nome' => 'POSTO DE SAUDE OESTE',
            'status' => 'A',
        ]);

        // Admin Geral / Super Admin com visão irrestrita
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin TI',
            'email' => 'adminti@gmail.com',
        ]);

        // 1. Consulta de Setores via endpoint autenticado com acesso global
        $responseSetores = $this->actingAs($superAdmin)->postJson('/api/setores/listWithAccess');
        $responseSetores->assertStatus(200);

        $setorIds = collect($responseSetores->json('data'))->pluck('id');
        $this->assertTrue($setorIds->contains($this->setor->id), 'Admin deve visualizar setor padrão');
        $this->assertTrue($setorIds->contains($setorA->id), 'Admin deve visualizar setor do Polo A');
        $this->assertTrue($setorIds->contains($setorB->id), 'Admin deve visualizar setor do Polo B');

        // 2. Consulta de Polos via endpoint de catálogo/polos
        $responsePolos = $this->actingAs($superAdmin)->postJson('/api/polo/list');
        $responsePolos->assertStatus(200);

        $polosData = $responsePolos->json('data.data') ?? $responsePolos->json('data');
        $poloIds = collect($polosData)->pluck('id');

        $this->assertTrue($poloIds->contains($poloA->id), 'Admin deve visualizar Polo A');
        $this->assertTrue($poloIds->contains($poloB->id), 'Admin deve visualizar Polo B');
    }
}
