<?php

$file = 'app/Http/Controllers/RelatoriosController.php';
$content = file_get_contents($file);

// Replace the N+1 inside listEstoqueReport
$search = '            // Buscar todos os resultados
            $results = $query->get();

            $user = auth()->user();

            // Buscar lotes para cada item do estoque
            $items = collect($results)->map(function ($estoque) use ($filters, $user) {
                $podeVerValores = $user && $estoque->setor && $user->podeVerValoresFinanceiros($estoque->setor);

                // Buscar lotes deste produto neste setor
                $lotesQuery = \App\Models\EstoqueLote::where(\'setor_id\', $estoque->setor_id)
                    ->where(\'produto_id\', $estoque->produto_id)
                    ->where(\'quantidade_disponivel\', \'>\', 0)
                    ->orderBy(\'data_vencimento\', \'asc\');

                // Filtro por dias de vencimento se fornecido
                if (!empty($filters[\'dias_vencimento\'])) {
                    $dataLimite = now()->addDays($filters[\'dias_vencimento\']);
                    $lotesQuery->whereDate(\'data_vencimento\', \'<=\', $dataLimite);
                }

                $lotes = $lotesQuery->get();';

$replacement = '            // Buscar todos os resultados
            $results = $query->get();

            $user = auth()->user();
            
            // PRELOAD ALL LOTES to avoid N+1
            $setorIds = $results->pluck(\'setor_id\')->unique()->toArray();
            $produtoIds = $results->pluck(\'produto_id\')->unique()->toArray();
            
            $allLotesQuery = \App\Models\EstoqueLote::whereIn(\'setor_id\', $setorIds)
                ->whereIn(\'produto_id\', $produtoIds)
                ->where(\'quantidade_disponivel\', \'>\', 0)
                ->orderBy(\'data_vencimento\', \'asc\');
                
            if (!empty($filters[\'dias_vencimento\'])) {
                $dataLimite = now()->addDays($filters[\'dias_vencimento\']);
                $allLotesQuery->whereDate(\'data_vencimento\', \'<=\', $dataLimite);
            }
            
            $allLotesGrouped = $allLotesQuery->get()->groupBy(function($l) {
                return $l->setor_id . \'-\' . $l->produto_id;
            });

            // Buscar lotes para cada item do estoque
            $items = collect($results)->map(function ($estoque) use ($filters, $user, $allLotesGrouped) {
                $podeVerValores = $user && $estoque->setor && $user->podeVerValoresFinanceiros($estoque->setor);

                $lotes = $allLotesGrouped->get($estoque->setor_id . \'-\' . $estoque->produto_id, collect());';

$content = str_replace($search, $replacement, $content);
file_put_contents($file, $content);
echo "Done Relatorios\n";
