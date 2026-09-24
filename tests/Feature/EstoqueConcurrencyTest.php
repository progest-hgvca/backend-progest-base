<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Produto;
use App\Models\Setores;
use App\Models\Estoque;
use App\Models\EstoqueLote;
use App\Models\Movimentacao;
use App\Models\ItemMovimentacao;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;

class EstoqueConcurrencyTest extends BaseTestCase
{
    use CreatesApplication;
    
    // NO RefreshDatabase or DatabaseTransactions here because we need separate connections to see the data.
    
    private $testId;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->testId = Str::random(10);
    }
    
    protected function tearDown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ItemMovimentacao::whereHas('movimentacao', function($q) {
            $q->where('observacao', 'like', '%'.$this->testId.'%');
        })->delete();
        DB::table('movimentacao')->where('observacao', 'like', '%'.$this->testId.'%')->delete();
        DB::table('estoque_lote')->where('produto_id', function($query) {
            $query->select('id')->from('produtos')->where('nome', 'PROD-TEST-'.$this->testId);
        })->delete();
        DB::table('estoque')->where('produto_id', function($query) {
            $query->select('id')->from('produtos')->where('nome', 'PROD-TEST-'.$this->testId);
        })->delete();
        DB::table('produtos')->where('nome', 'PROD-TEST-'.$this->testId)->delete();
        DB::table('setores')->where('nome', 'like', 'SETOR-TEST-%'.$this->testId)->delete();
        DB::table('usuario_setor')->where('setor_id', function($q) {
            $q->select('id')->from('setores')->where('nome', 'like', 'SETOR-TEST-%'.$this->testId);
        })->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        parent::tearDown();
    }

    public function test_concurrency_prevents_negative_stock()
    {
        // 1. Create permanent data (committed to DB)
        $polo = DB::table('polos')->first();
        if (!$polo) {
            $poloId = DB::table('polos')->insertGetId(['nome' => 'Polo Teste', 'status' => 'A']);
            $polo = DB::table('polos')->where('id', $poloId)->first();
        }

        $grupo = DB::table('grupo_produto')->first();
        if (!$grupo) {
            $grupoId = DB::table('grupo_produto')->insertGetId(['nome' => 'Grupo Teste', 'tipo' => 'Medicamento']);
            $grupo = DB::table('grupo_produto')->where('id', $grupoId)->first();
        }

        $unidade = DB::table('unidade_medida')->first();
        if (!$unidade) {
            $unidadeId = DB::table('unidade_medida')->insertGetId(['nome' => 'Unidade Teste']);
            $unidade = DB::table('unidade_medida')->where('id', $unidadeId)->first();
        }

        $origem = Setores::create(['nome' => 'SETOR-TEST-ORIGEM-'.$this->testId, 'estoque' => 1, 'status' => 'A', 'polo_id' => $polo->id]);
        $destino = Setores::create(['nome' => 'SETOR-TEST-DESTINO-'.$this->testId, 'estoque' => 1, 'status' => 'A', 'polo_id' => $polo->id]);
        $produto = Produto::create([
            'nome' => 'PROD-TEST-'.$this->testId,
            'status' => 'A',
            'grupo_produto_id' => $grupo->id,
            'unidade_medida_id' => $unidade->id,
            'codigo_barras' => '123'.$this->testId,
            'codigo_simpas' => '321'.$this->testId
        ]);
        
        $user = User::first() ?? User::factory()->create();
        
        DB::table('usuario_setor')->insert([
            'usuario_id' => $user->id,
            'setor_id' => $origem->id,
            'perfil' => 'admin'
        ]);

        Estoque::create([
            'produto_id' => $produto->id,
            'setor_id' => $origem->id,
            'quantidade_atual' => 10,
            'quantidade_minima' => 0,
            'localizacao' => 'TEST-'.$this->testId,
            'status_disponibilidade' => 'D'
        ]);

        EstoqueLote::create([
            'produto_id' => $produto->id,
            'setor_id' => $origem->id,
            'lote' => 'LOTE-TEST-'.$this->testId,
            'quantidade_disponivel' => 10,
            'data_vencimento' => now()->addDays(30)
        ]);
        
        // 2. Prepare 2 Movimentacoes of 6 items each
        $mov1 = Movimentacao::create([
            'usuario_id' => $user->id,
            'setor_origem_id' => $origem->id,
            'setor_destino_id' => $destino->id,
            'tipo' => 'T',
            'status_solicitacao' => 'P',
            'data_hora' => now(),
            'observacao' => 'MOV1-'.$this->testId
        ]);
        ItemMovimentacao::create(['movimentacao_id' => $mov1->id, 'produto_id' => $produto->id, 'quantidade_solicitada' => 6, 'quantidade_liberada' => 0]);

        $mov2 = Movimentacao::create([
            'usuario_id' => $user->id,
            'setor_origem_id' => $origem->id,
            'setor_destino_id' => $destino->id,
            'tipo' => 'T',
            'status_solicitacao' => 'P',
            'data_hora' => now(),
            'observacao' => 'MOV2-'.$this->testId
        ]);
        ItemMovimentacao::create(['movimentacao_id' => $mov2->id, 'produto_id' => $produto->id, 'quantidade_solicitada' => 6, 'quantidade_liberada' => 0]);

        // 3. We can just use the controller directly in different connections.
        // Wait, since we are in PHP, we can simulate concurrency by having a transaction pause,
        // BUT it's easier to just use `exec()` to spawn two artisan commands or simple curl requests.
        // Since we are running inside docker and we want it to be reliable, let's create a temporary artisan command.
        
        // Actually, we can just run HTTP requests using Laravel\'s HTTP client to our own API if it\'s running.
        // But the API might not be running on a port accessible from inside the test container if it\'s the same container without a web server (PHPUnit runs in CLI).
        
        // Let's use pcntl_fork!
        $pid1 = pcntl_fork();
        if ($pid1 == 0) {
            // Child 1
            DB::reconnect();
            \Illuminate\Support\Facades\Auth::login($user);
            $controller = app(\App\Http\Controllers\MovimentacaoController::class);
            $request = new \Illuminate\Http\Request();
            $request->merge(['action' => 'approve', 'usuario_id' => $user->id]);
            $request->setUserResolver(function () use ($user) { return $user; });
            try {
                $controller->process($request, $mov1->id);
            } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
                // Expected if stock is insufficient
            }
            exit(0);
        }

        $pid2 = pcntl_fork();
        if ($pid2 == 0) {
            // Child 2
            DB::reconnect();
            \Illuminate\Support\Facades\Auth::login($user);
            $controller = app(\App\Http\Controllers\MovimentacaoController::class);
            $request = new \Illuminate\Http\Request();
            $request->merge(['action' => 'approve', 'usuario_id' => $user->id]);
            $request->setUserResolver(function () use ($user) { return $user; });
            try {
                $controller->process($request, $mov2->id);
            } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
                // Expected if stock is insufficient
            }
            exit(0);
        }

        // Parent waits for both
        pcntl_waitpid($pid1, $status1);
        pcntl_waitpid($pid2, $status2);
        
        DB::reconnect(); // Reconnect parent to get fresh data
        
        $estoque = Estoque::where('produto_id', $produto->id)->where('setor_id', $origem->id)->first();
        
        // One should have succeeded (deducting 6), the other should have failed due to lock/insufficient stock.
        // The remaining stock MUST be 4 (10 - 6). It should NEVER be -2.
        $this->assertEquals(4, $estoque->quantidade_atual, 'O estoque não deve ficar negativo, prevenindo lost updates.');
        
        $mov1Final = Movimentacao::find($mov1->id);
        $mov2Final = Movimentacao::find($mov2->id);
        
        $aprovados = 0;
        if ($mov1Final->status_solicitacao === 'A') $aprovados++;
        if ($mov2Final->status_solicitacao === 'A') $aprovados++;
        
        $this->assertEquals(1, $aprovados, 'Apenas uma movimentação deve ser aprovada devido à concorrência.');
    }
}
