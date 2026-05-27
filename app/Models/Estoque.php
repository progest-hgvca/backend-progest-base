<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estoque extends Model
{
    use HasFactory;

    protected $table = 'estoque';

    protected $fillable = [
        'produto_id',
        'setor_id',
        'quantidade_atual',
        'quantidade_minima',
        'localizacao',
        'status_disponibilidade',
    ];

    protected $casts = [
        'quantidade_atual' => 'integer',
        'quantidade_minima' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacionamentos
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function setor()
    {
        return $this->belongsTo(Setores::class, 'setor_id');
    }

    public function lotes()
    {
        return $this->hasMany(EstoqueLote::class, 'setor_id', 'setor_id')
            ->where('produto_id', $this->produto_id);
    }

    // Scopes
    public function scopeDisponivel($query)
    {
        return $query->where('status_disponibilidade', 'D');
    }

    public function scopeIndisponivel($query)
    {
        return $query->where('status_disponibilidade', 'I');
    }

    public function scopePorSetor($query, $setorId)
    {
        return $query->where('setor_id', $setorId);
    }

    // Métodos auxiliares
    public function isDisponivel()
    {
        return $this->status_disponibilidade === 'D';
    }

    public function isAbaixoMinimo()
    {
        return $this->quantidade_atual < $this->quantidade_minima;
    }

    /**
     * Cria estoque inicial para todos os produtos compatíveis com um setor.
     */
    public static function criarEstoqueInicialParaSetor($setorId)
    {
        $setor = Setores::find($setorId);

        if (!$setor || !$setor->estoque) {
            return;
        }

        $produtos = Produto::whereHas('grupoProduto', function ($query) use ($setor) {
            $query->where('tipo', $setor->tipo)->where('status', 'A');
        })->where('status', 'A')->get();

        foreach ($produtos as $produto) {
            $estoqueExistente = self::where('produto_id', $produto->id)
                ->where('setor_id', $setorId)
                ->first();

            if (!$estoqueExistente) {
                self::create([
                    'produto_id'             => $produto->id,
                    'setor_id'               => $setorId,
                    'quantidade_atual'        => 0,
                    'quantidade_minima'       => 0,
                    'status_disponibilidade' => 'D',
                ]);
            }
        }
    }

    /**
     * Cria estoque em todos os setores compatíveis quando um produto é criado.
     */
    public static function criarEstoqueParaNovoProduto($produtoId)
    {
        $produto = Produto::with('grupoProduto')->find($produtoId);

        if (!$produto || !$produto->grupoProduto) {
            return;
        }

        $setores = Setores::where('estoque', true)
            ->where('tipo', $produto->grupoProduto->tipo)
            ->where('status', 'A')
            ->get();

        foreach ($setores as $setor) {
            $estoqueExistente = self::where('produto_id', $produtoId)
                ->where('setor_id', $setor->id)
                ->first();

            if (!$estoqueExistente) {
                self::create([
                    'produto_id'             => $produtoId,
                    'setor_id'               => $setor->id,
                    'quantidade_atual'        => 0,
                    'quantidade_minima'       => 0,
                    'status_disponibilidade' => 'D',
                ]);
            }
        }
    }
}
