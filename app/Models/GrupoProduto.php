<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoProduto extends Model
{
    use HasFactory;

    protected $table = 'grupo_produto';

    protected $fillable = [
        'nome',
        'status',
        'tipo',
        'controlado'
    ];

    protected $casts = [
        'controlado' => 'boolean',
    ];

    public function produtos()
    {
        return $this->hasMany(Produto::class, 'grupo_produto_id');
    }

    /** Apenas grupos de medicamentos controlados (Portaria 344/98) */
    public function scopeControlado($query)
    {
        return $query->where('controlado', true);
    }
}
