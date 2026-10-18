<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Exceção pontual que libera ou bloqueia um produto para um setor específico,
 * por cima da regra da categoria.
 */
class SetorProdutoExcecao extends Model
{
    use HasFactory;

    protected $table = 'setor_produto_excecao';

    protected $fillable = ['setor_id', 'produto_id', 'permitido'];

    protected $casts = [
        'permitido' => 'boolean',
    ];

    public function setor()
    {
        return $this->belongsTo(Setores::class, 'setor_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function scopeLiberacoes($query)
    {
        return $query->where('permitido', true);
    }

    public function scopeBloqueios($query)
    {
        return $query->where('permitido', false);
    }
}
