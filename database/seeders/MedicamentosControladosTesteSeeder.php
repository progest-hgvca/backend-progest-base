<?php

namespace Database\Seeders;

use App\Http\Controllers\EntradaController;
use App\Http\Controllers\MovimentacaoController;
use App\Models\GrupoProduto;
use App\Models\Produto;
use App\Models\Setores;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Massa de teste para validar o controle de medicamentos controlados.
 *
 * Cadastra medicamentos no grupo controlado e executa, pelos MESMOS controllers
 * usados pela aplicação, entradas por nota fiscal, uma transferência e uma
 * solicitação/saída — para que os efeitos apareçam no relatório.
 *
 * Uso: php artisan db:seed --class=MedicamentosControladosTesteSeeder
 *
 * É idempotente: remove a massa anterior (prefixo de NF "NFC-TESTE") antes de recriar.
 */
class MedicamentosControladosTesteSeeder extends Seeder
{
    /** Prefixo usado para identificar e limpar a massa de teste */
    private const PREFIXO_NF = 'NFC-TESTE-';
    private const MARCA_TESTE = 'TESTE CONTROLADOS';

    /** Medicamentos controlados criados para teste */
    private const MEDICAMENTOS = [
        ['nome' => 'Clonazepam 2mg',            'lista' => 'B1', 'simpas' => 'MC-B1-001', 'unidade' => 'Comprimido'],
        ['nome' => 'Diazepam 10mg',             'lista' => 'B1', 'simpas' => 'MC-B1-002', 'unidade' => 'Comprimido'],
        ['nome' => 'Midazolam 5mg/ml',          'lista' => 'B1', 'simpas' => 'MC-B1-003', 'unidade' => 'Ampola'],
        ['nome' => 'Morfina 10mg/ml',           'lista' => 'A1', 'simpas' => 'MC-A1-001', 'unidade' => 'Ampola'],
        ['nome' => 'Fentanila 50mcg/ml',        'lista' => 'A1', 'simpas' => 'MC-A1-002', 'unidade' => 'Ampola'],
        ['nome' => 'Metilfenidato 10mg',        'lista' => 'A3', 'simpas' => 'MC-A3-001', 'unidade' => 'Comprimido'],
        ['nome' => 'Sibutramina 15mg',          'lista' => 'B2', 'simpas' => 'MC-B2-001', 'unidade' => 'Comprimido'],
        ['nome' => 'Fenobarbital 100mg',        'lista' => 'C1', 'simpas' => 'MC-C1-001', 'unidade' => 'Comprimido'],
    ];

    public function run()
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        if (!$admin) {
            $this->command->error('Usuário admin@admin.com não encontrado. Rode o AdminInicialSeeder antes.');
            return;
        }
        auth()->setUser($admin);

        $grupo = GrupoProduto::where('controlado', true)->orderBy('id')->first();
        if (!$grupo) {
            $this->command->warn('Nenhum grupo controlado encontrado — criando via MedicamentosControladosSeeder.');
            $this->call(MedicamentosControladosSeeder::class);
            $grupo = GrupoProduto::where('controlado', true)->orderBy('id')->firstOrFail();
        }

        // Setores usados no teste (todos do tipo Medicamento)
        $origem = Setores::where('estoque', 1)->where('tipo', 'Medicamento')->orderBy('id')->first();
        $destinoTransferencia = Setores::where('estoque', 1)
            ->where('tipo', 'Medicamento')
            ->where('id', '!=', $origem->id)
            ->orderBy('id')
            ->first();
        $destinoSaida = Setores::where('tipo', 'Medicamento')
            ->whereNotIn('id', [$origem->id, $destinoTransferencia->id])
            ->orderBy('id')
            ->first();

        $fornecedor = DB::table('fornecedores')->where('status', 'A')->first();
        if (!$fornecedor) {
            $this->command->error('Nenhum fornecedor ativo encontrado.');
            return;
        }

        $this->limparMassaAnterior();
        $this->garantirAcessoAosSetores($admin, [$origem, $destinoTransferencia, $destinoSaida]);

        $produtos = $this->criarMedicamentos($grupo);
        $this->command->info('Medicamentos controlados cadastrados: ' . count($produtos));

        $this->registrarEntradas($produtos, $origem, $fornecedor->id);
        $this->simularLoteVencido($produtos, $origem);
        $this->registrarTransferencia($produtos, $origem, $destinoTransferencia, $admin);
        $this->registrarSaida($produtos, $origem, $destinoSaida, $admin);
        $this->registrarPendenteEReprovada($produtos, $origem, $destinoSaida, $admin);

        $this->command->info('');
        $this->command->info('Massa de teste criada:');
        $this->command->info('  Setor de origem .......... ' . $origem->nome . ' (id ' . $origem->id . ')');
        $this->command->info('  Destino da transferência .. ' . $destinoTransferencia->nome . ' (id ' . $destinoTransferencia->id . ')');
        $this->command->info('  Destino da saída .......... ' . $destinoSaida->nome . ' (id ' . $destinoSaida->id . ')');
    }

    /**
     * Remove a massa de teste anterior para o seeder poder rodar várias vezes.
     * A ordem respeita as FKs (itens antes dos cabeçalhos).
     */
    private function limparMassaAnterior()
    {
        $produtoIds = Produto::where('marca', self::MARCA_TESTE)->pluck('id');
        if ($produtoIds->isEmpty()) {
            return;
        }

        $entradaIds = DB::table('itens_entrada')->whereIn('produto_id', $produtoIds)->pluck('entrada_id')->unique();
        DB::table('itens_entrada')->whereIn('produto_id', $produtoIds)->delete();
        DB::table('entrada')->whereIn('id', $entradaIds)->where('nota_fiscal', 'LIKE', self::PREFIXO_NF . '%')->delete();

        $movIds = DB::table('item_movimentacao')->whereIn('produto_id', $produtoIds)->pluck('movimentacao_id')->unique();
        DB::table('item_movimentacao')->whereIn('produto_id', $produtoIds)->delete();
        DB::table('movimentacao')->whereIn('id', $movIds)->delete();

        DB::table('estoque_lote')->whereIn('produto_id', $produtoIds)->delete();
        DB::table('estoque_auditoria')->whereIn('produto_id', $produtoIds)->delete();
        DB::table('estoque')->whereIn('produto_id', $produtoIds)->delete();

        $this->command->warn('Massa de teste anterior removida (' . $produtoIds->count() . ' produtos).');
    }

    /**
     * Os relatórios só mostram setores aos quais o usuário está vinculado.
     * Sem isso, os setores de destino da transferência/saída ficariam invisíveis
     * e não daria para conferir o outro lado do movimento.
     */
    private function garantirAcessoAosSetores(User $usuario, array $setores)
    {
        foreach ($setores as $setor) {
            $jaVinculado = DB::table('usuario_setor')
                ->where('usuario_id', $usuario->id)
                ->where('setor_id', $setor->id)
                ->exists();

            if ($jaVinculado) {
                continue;
            }

            DB::table('usuario_setor')->insert([
                'usuario_id' => $usuario->id,
                'setor_id' => $setor->id,
                'perfil' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->warn('Vínculo criado: ' . $usuario->email . ' como admin em "' . $setor->nome . '".');
        }
    }

    private function criarMedicamentos(GrupoProduto $grupo)
    {
        $unidades = DB::table('unidade_medida')->pluck('id', 'nome');
        $produtos = [];

        foreach (self::MEDICAMENTOS as $med) {
            $produtos[] = Produto::updateOrCreate(
                ['nome' => $med['nome'], 'marca' => self::MARCA_TESTE],
                [
                    'codigo_simpas' => $med['simpas'],
                    'grupo_produto_id' => $grupo->id,
                    'lista_portaria' => $med['lista'],
                    'unidade_medida_id' => $unidades[$med['unidade']] ?? $unidades->first(),
                    'status' => 'A',
                ]
            );
        }

        return $produtos;
    }

    /**
     * Duas notas fiscais de entrada, com lotes e validades distintas —
     * uma delas com lote vencendo em 20 dias para acionar o alerta do relatório.
     */
    private function registrarEntradas(array $produtos, Setores $setor, $fornecedorId)
    {
        $controller = new EntradaController();

        $notas = [
            [
                'sufixo' => '001',
                'dias_atras' => 20,
                'itens' => [
                    ['produto' => 0, 'qtd' => 500, 'lote' => 'LOTE-CLN-A', 'vence_em' => 400],
                    ['produto' => 1, 'qtd' => 300, 'lote' => 'LOTE-DZP-A', 'vence_em' => 300],
                    ['produto' => 3, 'qtd' => 120, 'lote' => 'LOTE-MOR-A', 'vence_em' => 200],
                    ['produto' => 5, 'qtd' => 200, 'lote' => 'LOTE-MET-A', 'vence_em' => 20],
                ],
            ],
            [
                'sufixo' => '002',
                'dias_atras' => 8,
                'itens' => [
                    ['produto' => 0, 'qtd' => 250, 'lote' => 'LOTE-CLN-B', 'vence_em' => 500],
                    ['produto' => 2, 'qtd' => 180, 'lote' => 'LOTE-MDZ-A', 'vence_em' => 250],
                    ['produto' => 4, 'qtd' => 90,  'lote' => 'LOTE-FEN-A', 'vence_em' => 150],
                    ['produto' => 6, 'qtd' => 160, 'lote' => 'LOTE-SIB-A', 'vence_em' => 25],
                    ['produto' => 7, 'qtd' => 400, 'lote' => 'LOTE-FNB-A', 'vence_em' => 600],
                ],
            ],
        ];

        foreach ($notas as $nota) {
            $itens = [];
            foreach ($nota['itens'] as $item) {
                $itens[] = [
                    'produto_id' => $produtos[$item['produto']]->id,
                    'quantidade' => $item['qtd'],
                    'valor_unitario' => 1.50,
                    'lote' => $item['lote'],
                    'data_fabricacao' => now()->subDays(60)->toDateString(),
                    'data_vencimento' => now()->addDays($item['vence_em'])->toDateString(),
                ];
            }

            $request = Request::create('/api/entradas/add', 'POST', [
                'nota_fiscal' => self::PREFIXO_NF . $nota['sufixo'],
                'setor_id' => $setor->id,
                'fornecedor_id' => $fornecedorId,
                'itens' => $itens,
            ]);

            $response = $controller->add($request);
            $body = json_decode($response->getContent(), true);

            if (!($body['status'] ?? false)) {
                $this->command->error('Falha na entrada ' . $nota['sufixo'] . ': ' . json_encode($body, JSON_UNESCAPED_UNICODE));
                continue;
            }

            // Datar a entrada no passado para exercitar o filtro de período do relatório
            $entradaId = $body['data']['id'];
            $data = now()->subDays($nota['dias_atras']);
            DB::table('entrada')->where('id', $entradaId)->update(['created_at' => $data, 'updated_at' => $data]);
            DB::table('itens_entrada')->where('entrada_id', $entradaId)->update(['created_at' => $data, 'updated_at' => $data]);

            $this->command->info('Entrada ' . self::PREFIXO_NF . $nota['sufixo'] . ' registrada (' . count($itens) . ' itens).');
        }
    }

    /**
     * Simula a passagem do tempo: um lote que entrou válido e hoje está vencido.
     * (A entrada não aceita data de vencimento passada, então ajustamos depois.)
     */
    private function simularLoteVencido(array $produtos, Setores $setor)
    {
        $atualizados = DB::table('estoque_lote')
            ->where('setor_id', $setor->id)
            ->where('produto_id', $produtos[7]->id)
            ->where('lote', 'LOTE-FNB-A')
            ->update(['data_vencimento' => now()->subDays(10)->toDateString()]);

        if ($atualizados) {
            $this->command->info('Lote LOTE-FNB-A marcado como vencido (Fenobarbital).');
        }
    }

    /** Transferência aprovada entre setores com estoque */
    private function registrarTransferencia(array $produtos, Setores $origem, Setores $destino, User $admin)
    {
        $this->criarEProcessar(
            tipo: 'T',
            origem: $origem,
            destino: $destino,
            admin: $admin,
            itens: [
                ['produto_id' => $produtos[0]->id, 'quantidade_solicitada' => 120],
                ['produto_id' => $produtos[1]->id, 'quantidade_solicitada' => 80],
                ['produto_id' => $produtos[3]->id, 'quantidade_solicitada' => 30],
            ],
            observacao: 'Transferência de teste — medicamentos controlados',
            acao: 'approve',
            diasAtras: 5
        );

        $this->command->info('Transferência aprovada: ' . $origem->nome . ' -> ' . $destino->nome);
    }

    /** Solicitação/saída aprovada (tipo S) */
    private function registrarSaida(array $produtos, Setores $origem, Setores $destino, User $admin)
    {
        $this->criarEProcessar(
            tipo: 'S',
            origem: $origem,
            destino: $destino,
            admin: $admin,
            itens: [
                ['produto_id' => $produtos[2]->id, 'quantidade_solicitada' => 40],
                ['produto_id' => $produtos[4]->id, 'quantidade_solicitada' => 25],
                ['produto_id' => $produtos[5]->id, 'quantidade_solicitada' => 60],
            ],
            observacao: 'Dispensação de teste — medicamentos controlados',
            acao: 'approve',
            diasAtras: 2
        );

        $this->command->info('Saída aprovada: ' . $origem->nome . ' -> ' . $destino->nome);
    }

    /** Uma solicitação pendente e uma reprovada — não alteram o estoque */
    private function registrarPendenteEReprovada(array $produtos, Setores $origem, Setores $destino, User $admin)
    {
        $this->criarEProcessar(
            tipo: 'S',
            origem: $origem,
            destino: $destino,
            admin: $admin,
            itens: [['produto_id' => $produtos[6]->id, 'quantidade_solicitada' => 30]],
            observacao: 'Solicitação de teste aguardando aprovação',
            acao: null,
            diasAtras: 1
        );

        $this->criarEProcessar(
            tipo: 'S',
            origem: $origem,
            destino: $destino,
            admin: $admin,
            itens: [['produto_id' => $produtos[7]->id, 'quantidade_solicitada' => 999]],
            observacao: 'Solicitação de teste reprovada',
            acao: 'reject',
            diasAtras: 1
        );

        $this->command->info('Solicitações pendente e reprovada registradas.');
    }

    /**
     * Cria a movimentação e, opcionalmente, processa (aprova/reprova) usando o
     * MovimentacaoController — o mesmo caminho da aplicação.
     */
    private function criarEProcessar($tipo, Setores $origem, Setores $destino, User $admin, array $itens, $observacao, $acao, $diasAtras)
    {
        $controller = new MovimentacaoController();

        $request = Request::create('/api/movimentacoes', 'POST', [
            'usuario_id' => $admin->id,
            'setor_origem_id' => $origem->id,
            'setor_destino_id' => $destino->id,
            'tipo' => $tipo,
            'status_solicitacao' => 'P',
            'observacao' => $observacao,
            'itens' => $itens,
        ]);

        $body = json_decode($controller->store($request)->getContent(), true);
        if (!($body['status'] ?? false)) {
            $this->command->error('Falha ao criar movimentação: ' . json_encode($body, JSON_UNESCAPED_UNICODE));
            return;
        }

        $movId = $body['data']['id'];

        if ($acao) {
            $processRequest = Request::create('/api/movimentacoes/' . $movId . '/process', 'POST', [
                'action' => $acao,
                'aprovador_usuario_id' => $admin->id,
            ]);

            $resultado = json_decode($controller->process($processRequest, $movId)->getContent(), true);
            if (!($resultado['status'] ?? false)) {
                $this->command->error('Falha ao processar movimentação ' . $movId . ': ' . json_encode($resultado, JSON_UNESCAPED_UNICODE));
            }
        }

        // Datar no passado para exercitar o filtro de período do relatório
        $data = now()->subDays($diasAtras);
        DB::table('movimentacao')->where('id', $movId)->update([
            'data_hora' => $data,
            'created_at' => $data,
            'updated_at' => $data,
        ]);
    }
}
