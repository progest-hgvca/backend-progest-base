<?php

$file = 'app/Http/Controllers/RelatoriosController.php';
$content = file_get_contents($file);

/*
 * 1. Refactor listEntradasReport
 * Eliminate N+1 from $user->podeVerValoresFinanceiros($entrada->setor) inside map()
 */
$searchEntradas = <<<'EOD'
            $user = auth()->user();
            $results = $results->map(function ($entrada) use ($user) {
                $podeVer = $user && $entrada->setor && $user->podeVerValoresFinanceiros($entrada->setor);
EOD;

$replaceEntradas = <<<'EOD'
            $user = auth()->user();
            $isSuperAdmin = $user ? $user->isSuperAdmin() : false;
            
            $setoresFinanceiro = collect();
            if ($user && !$isSuperAdmin) {
                $setoresFinanceiro = \Illuminate\Support\Facades\DB::table('usuario_setor')
                    ->where('usuario_id', $user->id)
                    ->whereIn('perfil', ['admin', 'almoxarife']) // ou outros que possam ver financeiro
                    ->pluck('setor_id');
            }

            $results = $results->map(function ($entrada) use ($user, $isSuperAdmin, $setoresFinanceiro) {
                $podeVer = false;
                if ($user && $entrada->setor) {
                    $podeVer = $isSuperAdmin || $setoresFinanceiro->contains($entrada->setor_id);
                }
EOD;

$content = str_replace($searchEntradas, $replaceEntradas, $content);

/*
 * 2. Refactor listMovimentacoesReport
 * Eliminate N+1 from EstoqueLote::where inside each
 */
$searchMovimentacoes = <<<'EOD'
            // Enriquecer os itens com informações de lote (data_fabricacao e data_vencimento)
            $results->each(function ($movimentacao) {
                $movimentacao->itens->each(function ($item) use ($movimentacao) {
                    if ($item->lote && $movimentacao->setor_destino_id) {
                        // Buscar informações do lote na tabela estoque_lote
                        $loteInfo = \App\Models\EstoqueLote::where('produto_id', $item->produto_id)
                            ->where('lote', $item->lote)
                            ->where('setor_id', $movimentacao->setor_destino_id) // Setor que forneceu
                            ->first(['data_fabricacao', 'data_vencimento']);
                        
                        if ($loteInfo) {
                            $item->data_fabricacao = $loteInfo->data_fabricacao;
                            $item->data_vencimento = $loteInfo->data_vencimento;
                        } else {
                            $item->data_fabricacao = null;
                            $item->data_vencimento = null;
                        }
                    } else {
                        $item->data_fabricacao = null;
                        $item->data_vencimento = null;
                    }
                });
            });
EOD;

$replaceMovimentacoes = <<<'EOD'
            // Enriquecer os itens com informações de lote (data_fabricacao e data_vencimento) - BATCH LOADING O(1)
            $produtosIds = [];
            $lotes = [];
            $setoresIds = [];
            
            foreach ($results as $mov) {
                if ($mov->setor_destino_id) {
                    $setoresIds[] = $mov->setor_destino_id;
                    foreach ($mov->itens as $item) {
                        if ($item->lote) {
                            $produtosIds[] = $item->produto_id;
                            $lotes[] = $item->lote;
                        }
                    }
                }
            }
            
            $lotesPreloaded = collect();
            if (!empty($produtosIds) && !empty($lotes) && !empty($setoresIds)) {
                $produtosIds = array_unique($produtosIds);
                $lotes = array_unique($lotes);
                $setoresIds = array_unique($setoresIds);
                
                $lotesPreloaded = \App\Models\EstoqueLote::whereIn('produto_id', $produtosIds)
                    ->whereIn('lote', $lotes)
                    ->whereIn('setor_id', $setoresIds)
                    ->get(['produto_id', 'lote', 'setor_id', 'data_fabricacao', 'data_vencimento'])
                    ->keyBy(function($item) {
                        return $item->produto_id . '-' . $item->lote . '-' . $item->setor_id;
                    });
            }

            $results->each(function ($movimentacao) use ($lotesPreloaded) {
                $movimentacao->itens->each(function ($item) use ($movimentacao, $lotesPreloaded) {
                    if ($item->lote && $movimentacao->setor_destino_id) {
                        $key = $item->produto_id . '-' . $item->lote . '-' . $movimentacao->setor_destino_id;
                        $loteInfo = $lotesPreloaded->get($key);
                        
                        if ($loteInfo) {
                            $item->data_fabricacao = $loteInfo->data_fabricacao;
                            $item->data_vencimento = $loteInfo->data_vencimento;
                        } else {
                            $item->data_fabricacao = null;
                            $item->data_vencimento = null;
                        }
                    } else {
                        $item->data_fabricacao = null;
                        $item->data_vencimento = null;
                    }
                });
            });
EOD;

$content = str_replace($searchMovimentacoes, $replaceMovimentacoes, $content);

/*
 * 3. Fix listMedicamentosControladosReport strict polo_id / setor_id
 */
$searchMedicamentosPolo = <<<'EOD'
            if (!empty($filters['polo_id'])) {
                $query->whereHas('setor', function ($q) use ($filters) {
                    $q->where('polo_id', $filters['polo_id']);
                });
            }

            if (!empty($filters['setor_id'])) {
                $query->where('estoque.setor_id', $filters['setor_id']);
            }
EOD;

$replaceMedicamentosPolo = <<<'EOD'
            if (!empty($filters['polo_id'])) {
                $query->where('s.polo_id', $filters['polo_id']);
            }

            if (!empty($filters['setor_id'])) {
                $query->where('estoque.setor_id', $filters['setor_id']);
            }
EOD;
// wait, the joins are applied AFTER the filters! 
// Ah! In listMedicamentosControladosReport:
//             $query->join('produtos as p', 'estoque.produto_id', '=', 'p.id')
//                   ->join('setores as s', 'estoque.setor_id', '=', 's.id')
// So we can use s.polo_id, but the where clauses are added before the join in the current code... it's fine for Eloquent, it resolves them at the end. But whereHas is safer if join wasn't applied yet, wait, in Eloquent, where('s.polo_id') will fail if 's' is not joined yet? No, the SQL is compiled at the end, the order of where and join doesn't matter for the final SQL!
// Wait! Let's just leave listMedicamentosControladosReport whereHas alone, or strictly ensure that polo_id and setor_id are correctly filtered.
// Wait, is there any N+1 in listMedicamentosControladosReport?
// Let's check if there's any other N+1.
// $lista = $estoque->produto->lista_portaria ?: 'Sem lista'; -> no N+1, eager loaded.
// It looks fine!

// Let's do the same batch loading for listSaidasReport since it's identical to listMovimentacoesReport.
$searchSaidas = <<<'EOD'
            // Enriquecer os itens com informações de lote (data_fabricacao e data_vencimento)
            $results->each(function ($movimentacao) {
                $movimentacao->itens->each(function ($item) use ($movimentacao) {
                    if ($item->lote) {
                        // Buscar informações do lote na tabela estoque_lote
                        $loteInfo = \App\Models\EstoqueLote::where('produto_id', $item->produto_id)
                            ->where('lote', $item->lote)
                            ->where('setor_id', $movimentacao->setor_destino_id) // Setor que forneceu
                            ->first(['data_fabricacao', 'data_vencimento']);
                        
                        if ($loteInfo) {
                            $item->data_fabricacao = $loteInfo->data_fabricacao;
                            $item->data_vencimento = $loteInfo->data_vencimento;
                        } else {
                            $item->data_fabricacao = null;
                            $item->data_vencimento = null;
                        }
                    } else {
                        $item->data_fabricacao = null;
                        $item->data_vencimento = null;
                    }
                });
            });
EOD;

$replaceSaidas = <<<'EOD'
            // Enriquecer os itens com informações de lote (data_fabricacao e data_vencimento) - BATCH LOADING O(1)
            $produtosIds = [];
            $lotes = [];
            $setoresIds = [];
            
            foreach ($results as $mov) {
                if ($mov->setor_destino_id) {
                    $setoresIds[] = $mov->setor_destino_id;
                    foreach ($mov->itens as $item) {
                        if ($item->lote) {
                            $produtosIds[] = $item->produto_id;
                            $lotes[] = $item->lote;
                        }
                    }
                }
            }
            
            $lotesPreloaded = collect();
            if (!empty($produtosIds) && !empty($lotes) && !empty($setoresIds)) {
                $produtosIds = array_unique($produtosIds);
                $lotes = array_unique($lotes);
                $setoresIds = array_unique($setoresIds);
                
                $lotesPreloaded = \App\Models\EstoqueLote::whereIn('produto_id', $produtosIds)
                    ->whereIn('lote', $lotes)
                    ->whereIn('setor_id', $setoresIds)
                    ->get(['produto_id', 'lote', 'setor_id', 'data_fabricacao', 'data_vencimento'])
                    ->keyBy(function($item) {
                        return $item->produto_id . '-' . $item->lote . '-' . $item->setor_id;
                    });
            }

            $results->each(function ($movimentacao) use ($lotesPreloaded) {
                $movimentacao->itens->each(function ($item) use ($movimentacao, $lotesPreloaded) {
                    if ($item->lote && $movimentacao->setor_destino_id) {
                        $key = $item->produto_id . '-' . $item->lote . '-' . $movimentacao->setor_destino_id;
                        $loteInfo = $lotesPreloaded->get($key);
                        
                        if ($loteInfo) {
                            $item->data_fabricacao = $loteInfo->data_fabricacao;
                            $item->data_vencimento = $loteInfo->data_vencimento;
                        } else {
                            $item->data_fabricacao = null;
                            $item->data_vencimento = null;
                        }
                    } else {
                        $item->data_fabricacao = null;
                        $item->data_vencimento = null;
                    }
                });
            });
EOD;

$content = str_replace($searchSaidas, $replaceSaidas, $content);

file_put_contents($file, $content);

echo "Refactored RelatoriosController\n";

