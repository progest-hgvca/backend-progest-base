<?php

$file = 'app/Http/Controllers/MovimentacaoController.php';
$content = file_get_contents($file);

// Replace DB::beginTransaction()
$content = str_replace('DB::beginTransaction();', 'return DB::transaction(function () use ($action, $id, $mov, $itens) {', $content);

// Replace DB::rollBack(); return response()->json(...) with throw HttpResponseException
$content = preg_replace(
    '/DB::rollBack\(\);\s*return\s+(response\(\)->json\(.*?\));/s',
    'throw new \Illuminate\Http\Exceptions\HttpResponseException($1);',
    $content
);

// Replace the end of transaction
$end_replacement = '            $mov->save();
            Log::info("Movimentação salva com sucesso.");
            
            Log::info("Transação commitada.");

            return response()->json([\'status\' => true, \'data\' => $mov]);
        }, 5);';
        
$search = '            $mov->save();
            Log::info("Movimentação salva com sucesso.");
            
            DB::commit();
            Log::info("Transação commitada.");

            return response()->json([\'status\' => true, \'data\' => $mov]);';

$content = str_replace($search, $end_replacement, $content);

file_put_contents($file, $content);
echo "Done\n";
