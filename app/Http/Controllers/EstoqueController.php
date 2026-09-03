<?php

namespace App\Http\Controllers;

use App\Models\Estoque;
use App\Models\EstoqueLote;
use App\Models\Setores;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EstoqueController extends Controller
{
    /**
     * Listar estoque por setor com informações detalhadas do produto
     *
     * @param int $setorId
     * @return JsonResponse
     */
    public function listarPorSetor($setorId): JsonResponse
    {
        try {
            // Verificar se o setor existe e possui estoque
            $setor = Setores::find($setorId);

            if (!$setor) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Setor não encontrado.'
                ], 404);
            }

            if (!$setor->estoque) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Este setor não possui controle de estoque.'
                ], 200);
            }

            $user = auth()->user();
            $podeVerValores = $user && $user->podeVerValoresFinanceiros($setor);

            // Buscar estoque do setor com informações do produto
            $estoqueQuery = Estoque::with([
                'produto' => function ($query) {
                    $query->select('id', 'nome', 'marca', 'codigo_simpas', 'codigo_barras', 'grupo_produto_id', 'unidade_medida_id', 'status');
                },
                'produto.grupoProduto' => function ($query) {
                    $query->select('id', 'nome', 'tipo', 'status');
                },
                'produto.unidadeMedida' => function ($query) {
                    $query->select('id', 'nome');
                }
            ]);

            $lotesDoSetor = [];
            if ($podeVerValores) {
                $lotesDoSetor = EstoqueLote::where('setor_id', $setorId)
                    ->where('quantidade_disponivel', '>', 0)
                    ->get()
                    ->groupBy('produto_id');
            }

            $estoque = $estoqueQuery
                ->where('setor_id', $setorId)
                ->get()
                ->map(function ($item) use ($podeVerValores, $lotesDoSetor) {
                    $valorTotalProduto = null;
                    $precoMedio = null;

                    if ($podeVerValores && isset($lotesDoSetor[$item->produto_id])) {
                        $somaValor = 0;
                        $somaQtd = 0;
                        foreach ($lotesDoSetor[$item->produto_id] as $lote) {
                            if ($lote->valor_unitario !== null) {
                                $somaValor += ((float) $lote->valor_unitario * (float) $lote->quantidade_disponivel);
                                $somaQtd += (float) $lote->quantidade_disponivel;
                            }
                        }
                        $valorTotalProduto = round($somaValor, 2);
                        $precoMedio = $somaQtd > 0 ? round($somaValor / $somaQtd, 4) : null;
                    }

                    return [
                        'estoque_id' => $item->id,
                        'quantidade_atual' => $item->quantidade_atual,
                        'quantidade_minima' => $item->quantidade_minima,
                        'status_disponibilidade' => $item->status_disponibilidade,
                        'status_disponibilidade_texto' => $item->status_disponibilidade === 'D' ? 'Disponível' : 'Indisponível',
                        'abaixo_minimo' => $item->isAbaixoMinimo(),
                        'valor_total' => $valorTotalProduto,
                        'preco_medio' => $precoMedio,
                        'produto' => [
                            'id' => $item->produto->id,
                            'nome' => $item->produto->nome,
                            'nome_completo' => $item->produto->nome_completo,
                            'marca' => $item->produto->marca,
                            'codigo_simpas' => $item->produto->codigo_simpas,
                            'codigo_barras' => $item->produto->codigo_barras,
                            'status' => $item->produto->status,
                            'grupo_produto' => [
                                'id' => $item->produto->grupoProduto->id ?? null,
                                'nome' => $item->produto->grupoProduto->nome ?? null,
                                'tipo' => $item->produto->grupoProduto->tipo ?? null,
                            ],
                            'unidade_medida' => [
                                'id' => $item->produto->unidadeMedida->id ?? null,
                                'nome' => $item->produto->unidadeMedida->nome ?? null,
                            ]
                        ],
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                });

            $valorTotalPatrimonio = $podeVerValores 
                ? round($estoque->sum('valor_total'), 2)
                : null;

            return response()->json([
                'status' => true,
                'data'   => [
                    'setor'   => [
                        'id'   => $setor->id,
                        'nome' => $setor->nome,
                        'tipo' => $setor->tipo,
                    ],
                    'estoque' => $estoque,
                    'resumo'  => [
                        'total_produtos'         => $estoque->count(),
                        'produtos_disponiveis'   => $estoque->where('status_disponibilidade', 'D')->count(),
                        'produtos_abaixo_minimo' => $estoque->where('abaixo_minimo', true)->count(),
                        'pode_ver_valores'       => $podeVerValores,
                        'valor_total_patrimonio' => $valorTotalPatrimonio,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Erro ao buscar estoque.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibir um item específico do estoque
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $estoque = Estoque::with([
                'produto.grupoProduto',
                'produto.unidadeMedida',
                'setor'
            ])->find($id);

            if (!$estoque) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Item de estoque não encontrado.'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data'   => [
                    'id'                          => $estoque->id,
                    'quantidade_atual'            => $estoque->quantidade_atual,
                    'quantidade_minima'           => $estoque->quantidade_minima,
                    'status_disponibilidade'      => $estoque->status_disponibilidade,
                    'status_disponibilidade_texto'=> $estoque->status_disponibilidade === 'D' ? 'Disponível' : 'Indisponível',
                    'abaixo_minimo'               => $estoque->isAbaixoMinimo(),
                    'produto'                     => $estoque->produto,
                    'setor'                       => $estoque->setor,
                    'created_at'                  => $estoque->created_at,
                    'updated_at'                  => $estoque->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Erro ao buscar item do estoque.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar quantidade mínima do estoque
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function atualizarQuantidadeMinima(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'quantidade_minima' => 'required|integer|min:0'
            ]);

            $estoque = Estoque::find($id);

            if (!$estoque) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Item de estoque não encontrado.'
                ], 404);
            }

            $estoque->update([
                'quantidade_minima' => $request->quantidade_minima
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Quantidade mínima atualizada com sucesso.',
                'data'    => $estoque
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Erro ao atualizar quantidade mínima.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar status de disponibilidade do estoque
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function atualizarStatus(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'status_disponibilidade' => 'required|in:D,I'
            ]);

            $estoque = Estoque::find($id);

            if (!$estoque) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Item de estoque não encontrado.'
                ], 404);
            }

            $estoque->update([
                'status_disponibilidade' => $request->status_disponibilidade
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Status atualizado com sucesso.',
                'data'    => $estoque
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Erro ao atualizar status.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
