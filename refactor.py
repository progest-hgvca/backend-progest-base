import re

with open('app/Http/Controllers/MovimentacaoController.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace DB::beginTransaction()
content = content.replace('DB::beginTransaction();', 'return DB::transaction(function () use ($action, $id, $mov, $itens) {')

# Replace DB::rollBack(); return response()->json(...) with throw HttpResponseException
content = re.sub(
    r'DB::rollBack\(\);\s*return\s+(response\(\)->json\(.*?\));',
    r'throw new \\Illuminate\\Http\\Exceptions\\HttpResponseException(\1);',
    content,
    flags=re.DOTALL
)

# Replace the end of transaction
end_replacement = """            $mov->save();
            Log::info("Movimentação salva com sucesso.");
            
            Log::info("Transação commitada.");

            return response()->json(['status' => true, 'data' => $mov]);
        }, 5);"""
content = content.replace("""            $mov->save();
            Log::info("Movimentação salva com sucesso.");
            
            DB::commit();
            Log::info("Transação commitada.");

            return response()->json(['status' => true, 'data' => $mov]);""", end_replacement)

with open('app/Http/Controllers/MovimentacaoController.php', 'w', encoding='utf-8') as f:
    f.write(content)
