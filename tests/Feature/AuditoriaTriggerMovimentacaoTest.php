<?php

namespace Tests\Feature;

use App\Models\Movimentacao;
use App\Models\Setores;
use App\Models\User;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class AuditoriaTriggerMovimentacaoTest extends TestCase
{
    private $user;
    private $aprovador;
    private $setorOrigem;
    private $setorDestino;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->aprovador = User::factory()->create();
        $this->setorOrigem = Setores::factory()->create(['estoque' => true]);
        $this->setorDestino = Setores::factory()->create(['estoque' => false]);
    }

    public function test_permite_movimentacao_pendente_sem_aprovador()
    {
        $mov = Movimentacao::create([
            'usuario_id' => $this->user->id,
            'setor_origem_id' => $this->setorOrigem->id,
            'setor_destino_id' => $this->setorDestino->id,
            'tipo' => 'S',
            'status_solicitacao' => 'P',
            'data_hora' => now(),
        ]);

        $this->assertDatabaseHas('movimentacao', [
            'id' => $mov->id,
            'status_solicitacao' => 'P',
            'aprovador_usuario_id' => null,
        ]);
    }

    public function test_bloqueia_insercao_de_movimentacao_aprovada_sem_aprovador()
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('Erro de Auditoria: Aprovador não pode ser nulo para movimentações aprovadas ou reprovadas.');

        Movimentacao::create([
            'usuario_id' => $this->user->id,
            'setor_origem_id' => $this->setorOrigem->id,
            'setor_destino_id' => $this->setorDestino->id,
            'tipo' => 'S',
            'status_solicitacao' => 'A',
            'data_hora' => now(),
        ]);
    }

    public function test_bloqueia_insercao_de_movimentacao_reprovada_sem_aprovador()
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('Erro de Auditoria: Aprovador não pode ser nulo para movimentações aprovadas ou reprovadas.');

        Movimentacao::create([
            'usuario_id' => $this->user->id,
            'setor_origem_id' => $this->setorOrigem->id,
            'setor_destino_id' => $this->setorDestino->id,
            'tipo' => 'S',
            'status_solicitacao' => 'R',
            'data_hora' => now(),
        ]);
    }

    public function test_permite_insercao_de_movimentacao_aprovada_com_aprovador()
    {
        $mov = Movimentacao::create([
            'usuario_id' => $this->user->id,
            'aprovador_usuario_id' => $this->aprovador->id,
            'setor_origem_id' => $this->setorOrigem->id,
            'setor_destino_id' => $this->setorDestino->id,
            'tipo' => 'S',
            'status_solicitacao' => 'A',
            'data_hora' => now(),
        ]);

        $this->assertDatabaseHas('movimentacao', [
            'id' => $mov->id,
            'status_solicitacao' => 'A',
            'aprovador_usuario_id' => $this->aprovador->id,
        ]);
    }

    public function test_bloqueia_atualizacao_para_aprovado_sem_aprovador()
    {
        $mov = Movimentacao::create([
            'usuario_id' => $this->user->id,
            'setor_origem_id' => $this->setorOrigem->id,
            'setor_destino_id' => $this->setorDestino->id,
            'tipo' => 'S',
            'status_solicitacao' => 'P',
            'data_hora' => now(),
        ]);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('Erro de Auditoria: Aprovador não pode ser nulo para movimentações aprovadas ou reprovadas.');

        $mov->status_solicitacao = 'A';
        $mov->save();
    }

    public function test_permite_atualizacao_para_aprovado_com_aprovador()
    {
        $mov = Movimentacao::create([
            'usuario_id' => $this->user->id,
            'setor_origem_id' => $this->setorOrigem->id,
            'setor_destino_id' => $this->setorDestino->id,
            'tipo' => 'S',
            'status_solicitacao' => 'P',
            'data_hora' => now(),
        ]);

        $mov->status_solicitacao = 'A';
        $mov->aprovador_usuario_id = $this->aprovador->id;
        $mov->save();

        $this->assertDatabaseHas('movimentacao', [
            'id' => $mov->id,
            'status_solicitacao' => 'A',
            'aprovador_usuario_id' => $this->aprovador->id,
        ]);
    }
}
