<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Regra "setores desta categoria podem solicitar produtos deste grupo".
 */
class CategoriaGrupoProduto extends Model
{
    use HasFactory;

    protected $table = 'categoria_grupo_produto';

    protected $fillable = ['categoria', 'grupo_produto_id'];

    public const CATEGORIAS = ['Farmacia', 'Internamento', 'Administrativo'];

    public function grupoProduto()
    {
        return $this->belongsTo(GrupoProduto::class, 'grupo_produto_id');
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }
}
