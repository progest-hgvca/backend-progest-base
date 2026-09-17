<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Setores;
use App\Models\Polo;
use App\Models\Produto;
use App\Models\GrupoProduto;
use App\Models\UnidadeMedida;
use App\Models\Movimentacao;
use App\Models\Fornecedor;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

class RegrasNegocioMovimentacaoESeederTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $polo;
    protected $caf;
    protected $farmaciaSatelite;
    protected $uti;
    protected $produto;
    protected $fornecedor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->polo = Polo::factory()->create(['nome' => 'Polo Regras Negocio']);

        // Setor Distribuidor Central com Estoque
        $this->caf = Setores::create([
            'polo_id' => $this->polo->id,
            'nome'    => 'CAF - Central de Abastecimento Farmacêutico',
            'estoque' => true,
            'tipo'    => 'Medicamento',
            'status'  => 'A',
        ]);

        $this->admin = User::factory()->create(['email' => 'admin_regras_' . uniqid() . '@example.com']);
        DB::table('usuario_setor')->insert([
            'usuario_id' => $this->admin->id,
            'setor_id'   => $this->caf->id,
            'perfil'     => 'admin',
        ]);
        Sanctum::actingAs($this->admin);

        // Farmácia Satélite com Estoque
        $this->farmaciaSatelite = Setores::create([
            'polo_id' => $this->polo->id,
            'nome'    => 'Farmácia Satélite Teste',
            'estoque' => true,
            'tipo'    => 'Medicamento',
            'status'  => 'A',
        ]);

        // UTI sem Estoque (solicitante assistencial)
        $this->uti = Setores::create([
            'polo_id' => $this->polo->id,
            'nome'    => 'UTI Adulto Teste Regras',
            'estoque' => false,
            'tipo'    => 'Medicamento',
            'status'  => 'A',
        ]);

        // Vínculo oficial de distribuição: CAF distribui para a Farmácia Satélite e para a UTI
        DB::table('setor_distribuidor')->insert([
            ['setor_solicitante_id' => $this->uti->id, 'setor_distribuidor_id' => $this->caf->id],
            ['setor_solicitante_id' => $this->farmaciaSatelite->id, 'setor_distribuidor_id' => $this->caf->id],
        ]);

        $grupo = GrupoProduto::firstOrCreate(
            ['nome' => 'Medicamentos Teste Regra', 'tipo' => 'Medicamento'],
            ['status' => 'A']
        );
        $unidade = UnidadeMedida::firstOrCreate(
            ['nome' => 'Ampola Teste Regra'],
            ['status' => 'A']
        );
        $this->produto = Produto::create([
            'nome'              => 'Dipirona 500mg Teste Regra',
            'marca'             => 'Marca Regra',
            'grupo_produto_id'  => $grupo->id,
            'unidade_medida_id' => $unidade->id,
            'status'            => 'A',
        ]);

        $this->fornecedor = Fornecedor::create([
            'razao_social_nome' => 'Distribuidora Farmaceutica Regra Teste',
            'tipo_pessoa'       => 'J',
            'cnpj'              => '88722158000199',
            'status'            => 'A',
        ]);
    }

    /**
     * 1. Bloqueia criação de movimentação se o setor de origem não possui controle de estoque.
     */
    public function test_bloqueia_solicitacao_com_origem_sem_estoque()
    {
        $payload = [
            'usuario_id'         => $this->admin->id,
            'setor_origem_id'    => $this->uti->id, // UTI NÃO tem estoque!
            'setor_destino_id'   => $this->farmaciaSatelite->id,
            'tipo'               => 'S',
            'status_solicitacao' => 'P',
            'itens'              => [
                [
                    'produto_id'            => $this->produto->id,
                    'quantidade_solicitada' => 10,
                ],
            ],
        ];

        $response = $this->postJson('/api/movimentacao/add', $payload);
        $response->assertStatus(422)
                 ->assertJson(['status' => false]);
    }

    /**
     * 2. Bloqueia criação de movimentação se a origem não é distribuidor autorizado do destino.
     */
    public function test_bloqueia_solicitacao_de_distribuidor_nao_autorizado()
    {
        // Criar outro setor com estoque que NÃO é distribuidor da UTI
        $outroEstoque = Setores::create([
            'polo_id' => $this->polo->id,
            'nome'    => 'Almoxarifado Geral de Outro Polo',
            'estoque' => true,
            'tipo'    => 'Medicamento',
            'status'  => 'A',
        ]);

        $payload = [
            'usuario_id'         => $this->admin->id,
            'setor_origem_id'    => $outroEstoque->id,
            'setor_destino_id'   => $this->uti->id,
            'tipo'               => 'S',
            'status_solicitacao' => 'P',
            'itens'              => [
                [
                    'produto_id'            => $this->produto->id,
                    'quantidade_solicitada' => 10,
                ],
            ],
        ];

        $response = $this->postJson('/api/movimentacao/add', $payload);
        $response->assertStatus(422)
                 ->assertJson(['status' => false]);
    }

    /**
     * 3. Bloqueia auto-solicitação (origem igual ao destino) para tipos 'S' e 'T'.
     */
    public function test_bloqueia_auto_solicitacao_mesmo_setor()
    {
        $payload = [
            'usuario_id'         => $this->admin->id,
            'setor_origem_id'    => $this->caf->id,
            'setor_destino_id'   => $this->caf->id,
            'tipo'               => 'S',
            'status_solicitacao' => 'P',
            'itens'              => [
                [
                    'produto_id'            => $this->produto->id,
                    'quantidade_solicitada' => 5,
                ],
            ],
        ];

        $response = $this->postJson('/api/movimentacao/add', $payload);
        $response->assertStatus(422)
                 ->assertJson(['status' => false]);
    }

    /**
     * 4. Bloqueia auto-solicitação em updateRascunho.
     */
    public function test_bloqueia_auto_solicitacao_no_update_rascunho()
    {
        $mov = Movimentacao::create([
            'usuario_id'         => $this->admin->id,
            'setor_origem_id'    => $this->caf->id,
            'setor_destino_id'   => $this->uti->id,
            'tipo'               => 'S',
            'status_solicitacao' => 'C',
            'data_hora'          => now(),
        ]);

        $payload = [
            'setor_origem_id' => $this->uti->id, // Tentar mudar origem para ser igual ao destino!
            'itens'           => [
                [
                    'produto_id'            => $this->produto->id,
                    'quantidade_solicitada' => 5,
                ],
            ],
        ];

        $response = $this->postJson("/api/movimentacao/{$mov->id}/update-rascunho", $payload);
        $response->assertStatus(422)
                 ->assertJson(['status' => false]);
    }

    /**
     * 5. Bloqueia lançamento de Nota Fiscal de fornecedor em setor sem estoque ou não distribuidor.
     */
    public function test_bloqueia_entrada_nf_em_setor_assistencial_sem_estoque()
    {
        $payload = [
            'nota_fiscal'   => 'NF-TEST-BLOQ-01',
            'setor_id'      => $this->uti->id, // UTI assistencial
            'fornecedor_id' => $this->fornecedor->id,
            'itens'         => [
                [
                    'produto_id'      => $this->produto->id,
                    'quantidade'      => 100,
                    'lote'            => 'LOTE-BLOQ',
                    'data_vencimento' => now()->addYear()->format('Y-m-d'),
                ],
            ],
        ];

        $response = $this->postJson('/api/entrada/add', $payload);
        $response->assertStatus(400); // rejeita por não ter estoque
    }
}
