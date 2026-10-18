<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Models\CategoriaGrupoProduto;
use App\Models\GrupoProduto;
use App\Models\Produto;
use App\Models\Setores;
use App\Models\SetorProdutoExcecao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Administra quais produtos cada categoria de setor pode solicitar.
 *
 * Duas camadas:
 *   - regras por categoria (categoria_grupo_produto), que valem para todos os
 *     setores daquela categoria;
 *   - exceções por setor (setor_produto_excecao), para casos isolados que não
 *     justificam afrouxar a regra da categoria inteira.
 */
class RegrasProdutoController extends Controller
{
    /**
     * Só quem administra a CAF mexe nas regras de catálogo — a mesma barreira
     * que já protege o cadastro de produtos e a entrada de notas fiscais.
     */
    private function autorizado(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        if ($user->isSuperAdmin() || $user->isAdminCaf()) {
            return true;
        }

        return DB::table('usuario_setor')
            ->join('setores', 'setores.id', '=', 'usuario_setor.setor_id')
            ->where('usuario_setor.usuario_id', $user->id)
            ->whereIn('usuario_setor.perfil', ['admin', 'almoxarife'])
            ->where('setores.estoque', true)
            ->exists();
    }

    private function negarAcesso()
    {
        return response()->json([
            'status'  => false,
            'message' => 'Apenas administradores da CAF podem alterar as regras de produtos por setor.'
        ], 403);
    }

    /**
     * Retorna o quadro completo de regras: categorias, grupos disponíveis,
     * grupos liberados por categoria e as exceções cadastradas.
     *
     * POST /regras-produto/list
     */
    public function list(Request $request)
    {
        try {
            $grupos = GrupoProduto::where('status', 'A')
                ->orderBy('tipo')
                ->orderBy('nome')
                ->get(['id', 'nome', 'tipo', 'controlado']);

            $regras = [];
            foreach (Setores::CATEGORIAS as $categoria) {
                $regras[$categoria] = CategoriaGrupoProduto::porCategoria($categoria)
                    ->pluck('grupo_produto_id')
                    ->values();
            }

            $excecoesQuery = SetorProdutoExcecao::with([
                'setor:id,nome,polo_id,categoria',
                'setor.polo:id,sigla',
                'produto:id,nome,codigo_simpas,grupo_produto_id',
            ]);

            if ($request->filled('setor_id')) {
                $excecoesQuery->where('setor_id', $request->input('setor_id'));
            }

            $contagemPorCategoria = Setores::select('categoria', DB::raw('COUNT(*) as total'))
                ->groupBy('categoria')
                ->pluck('total', 'categoria');

            return response()->json([
                'status' => true,
                'data'   => [
                    'categorias'            => Setores::CATEGORIAS,
                    'categoria_labels'      => Setores::CATEGORIA_LABELS,
                    'grupos'                => $grupos,
                    'regras'                => $regras,
                    'excecoes'              => $excecoesQuery->get(),
                    'setores_por_categoria' => $contagemPorCategoria,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar regras de produto por setor: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Erro ao carregar as regras de produtos.'], 500);
        }
    }

    /**
     * Substitui por completo a lista de grupos liberados para uma categoria.
     *
     * POST /regras-produto/sync
     * { categoria: 'Internamento', grupo_produto_ids: [1, 4, 7] }
     */
    public function sync(Request $request)
    {
        if (!$this->autorizado()) {
            return $this->negarAcesso();
        }

        $data = $request->validate([
            'categoria'           => 'required|in:' . implode(',', Setores::CATEGORIAS),
            'grupo_produto_ids'   => 'present|array',
            'grupo_produto_ids.*' => 'integer|exists:grupo_produto,id',
        ], [
            'categoria.in' => 'Categoria inválida. Use Farmacia, Internamento ou Administrativo.',
        ]);

        try {
            DB::transaction(function () use ($data) {
                CategoriaGrupoProduto::porCategoria($data['categoria'])->delete();

                foreach (array_unique($data['grupo_produto_ids']) as $grupoId) {
                    CategoriaGrupoProduto::create([
                        'categoria'        => $data['categoria'],
                        'grupo_produto_id' => $grupoId,
                    ]);
                }
            });

            return response()->json([
                'status'  => true,
                'message' => 'Regras da categoria atualizadas com sucesso.',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar regras de categoria: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Erro ao salvar as regras da categoria.'], 500);
        }
    }

    /**
     * Cria, atualiza ou remove uma exceção de produto para um setor.
     *
     * POST /regras-produto/excecao
     * { setor_id: 12, produto_id: 340, permitido: true }   -> cria/atualiza
     * { setor_id: 12, produto_id: 340, remover: true }     -> remove
     */
    public function excecao(Request $request)
    {
        if (!$this->autorizado()) {
            return $this->negarAcesso();
        }

        $data = $request->validate([
            'setor_id'   => 'required|integer|exists:setores,id',
            'produto_id' => 'required|integer|exists:produtos,id',
            'permitido'  => 'nullable|boolean',
            'remover'    => 'nullable|boolean',
        ]);

        try {
            if (!empty($data['remover'])) {
                SetorProdutoExcecao::where('setor_id', $data['setor_id'])
                    ->where('produto_id', $data['produto_id'])
                    ->delete();

                return response()->json(['status' => true, 'message' => 'Exceção removida.']);
            }

            $excecao = SetorProdutoExcecao::updateOrCreate(
                ['setor_id' => $data['setor_id'], 'produto_id' => $data['produto_id']],
                ['permitido' => $data['permitido'] ?? true]
            );

            $excecao->load(['setor:id,nome', 'produto:id,nome,codigo_simpas']);

            return response()->json([
                'status'  => true,
                'message' => 'Exceção salva com sucesso.',
                'data'    => $excecao,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao salvar exceção de produto: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Erro ao salvar a exceção.'], 500);
        }
    }

    /**
     * Prévia do catálogo de um setor — quantos e quais produtos ele enxerga
     * com as regras atuais. Usado para conferir uma configuração antes de salvar.
     *
     * POST /regras-produto/preview
     */
    public function preview(Request $request)
    {
        $data = $request->validate([
            'setor_id' => 'required|integer|exists:setores,id',
            'limite'   => 'nullable|integer|min:1|max:200',
        ]);

        try {
            $setor = Setores::find($data['setor_id']);
            $query = $setor->produtosDisponiveis();

            $total = (clone $query)->count();
            $amostra = $query->with('grupoProduto:id,nome,tipo')
                ->orderBy('produtos.nome')
                ->limit($data['limite'] ?? 50)
                ->get(['produtos.id', 'produtos.nome', 'produtos.codigo_simpas', 'produtos.grupo_produto_id']);

            return response()->json([
                'status' => true,
                'data'   => [
                    'setor'     => $setor->only(['id', 'nome', 'tipo', 'categoria']),
                    'total'     => $total,
                    'amostra'   => $amostra,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar prévia de catálogo do setor: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Erro ao gerar a prévia do catálogo.'], 500);
        }
    }
}
