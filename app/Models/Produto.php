<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $table = 'produtos';

    protected $fillable = [
        'nome',
        'marca',
        'codigo_simpas',
        'codigo_barras',
        'grupo_produto_id',
        'lista_portaria',
        'unidade_medida_id',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacionamentos
    public function grupoProduto()
    {
        return $this->belongsTo(GrupoProduto::class, 'grupo_produto_id');
    }

    public function unidadeMedida()
    {
        return $this->belongsTo(UnidadeMedida::class, 'unidade_medida_id');
    }

    // Relacionamentos inversos
    public function itensEntrada()
    {
        return $this->hasMany(ItensEntrada::class);
    }

    public function itensMovimentacao()
    {
        return $this->hasMany(ItemMovimentacao::class);
    }

    public function estoques()
    {
        return $this->hasMany(Estoque::class);
    }

    // Scopes para filtros
    public function scopeAtivo($query)
    {
        return $query->where('status', 'A');
    }

    public function scopeInativo($query)
    {
        return $query->where('status', 'I');
    }

    /** Somente produtos pertencentes a grupos de medicamentos controlados */
    public function scopeControlado($query)
    {
        return $query->whereHas('grupoProduto', function ($q) {
            $q->where('controlado', true);
        });
    }

    public function scopePorGrupo($query, $grupoId)
    {
        return $query->where('grupo_produto_id', $grupoId);
    }

    public function scopePorUnidade($query, $unidadeId)
    {
        return $query->where('unidade_medida_id', $unidadeId);
    }

    // Accessors
    public function isAtivo()
    {
        return $this->status === 'A';
    }

    public function isInativo()
    {
        return $this->status === 'I';
    }

    /**
     * Um produto e controlado quando pertence a um grupo marcado como controlado.
     */
    public function getControladoAttribute()
    {
        return (bool) optional($this->grupoProduto)->controlado;
    }

    // Accessor para nome completo (nome + marca)
    public function getNomeCompletoAttribute()
    {
        return $this->marca ? $this->nome . ' - ' . $this->marca : $this->nome;
    }
}
