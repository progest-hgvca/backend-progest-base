<?php
$file = 'app/Http/Controllers/MovimentacaoController.php';
$content = file_get_contents($file);

// Only replace the specific DB::beginTransaction() in process() around line 280
// process() function starts around line 220
$content = preg_replace(
    '/(public function process\(Request \$request, \$id\).*?try {\s*)DB::beginTransaction\(\);/s',
    '$1return DB::transaction(function () use ($action, $id, $mov, $itens) {',
    $content,
    1
);

// We need to replace the DB::rollBack() inside that specific transaction.
// A safer way is just to leave DB::rollBack() alone, and only change the transaction wrapping.
// Wait, if we use DB::transaction, we MUST throw an exception to rollback, otherwise DB::transaction will commit!
// If we just do DB::rollBack(); return response(), it will actually rollback BUT DB::transaction might catch it or not.
// Wait, DB::transaction only rolls back if an EXCEPTION is thrown. If we do DB::rollBack(); return response(), the transaction closure returns a response, DB::transaction thinks it succeeded, but we manually rolled back. Then DB::transaction tries to commit, but the transaction was rolled back! This throws an error!
// So we MUST replace DB::rollBack(); return response(...) with throw new \Illuminate\Http\Exceptions\HttpResponseException(response(...))
// But only inside process().

// Let's do it carefully: extract process() method body, replace inside it, then put it back.
$pattern = '/(public function process\(Request \$request, \$id\).*?)(?=\n    public function updateStatus)/s';
if (preg_match($pattern, $content, $matches)) {
    $processBody = $matches[1];
    
    // Replace DB::beginTransaction()
    $processBody = preg_replace('/DB::beginTransaction\(\);/', 'return DB::transaction(function () use ($action, $id, $mov, $itens) {', $processBody, 1);
    
    // Replace rollbacks
    $processBody = preg_replace(
        '/DB::rollBack\(\);\s*return\s+(response\(\)->json\(.*?\));/s',
        'throw new \Illuminate\Http\Exceptions\HttpResponseException($1);',
        $processBody
    );
    
    // Replace commit
    $search = '            $mov->save();
            Log::info("Movimentação salva com sucesso.");
            
            DB::commit();
            Log::info("Transação commitada.");

            return response()->json([\'status\' => true, \'data\' => $mov]);';
    $replacement = '            $mov->save();
            Log::info("Movimentação salva com sucesso.");
            
            Log::info("Transação commitada.");

            return response()->json([\'status\' => true, \'data\' => $mov]);
        }, 5);';
    $processBody = str_replace($search, $replacement, $processBody);
    
    $content = str_replace($matches[1], $processBody, $content);
    file_put_contents($file, $content);
    echo "Done Refactor Process\n";
} else {
    echo "Could not find process method\n";
}
