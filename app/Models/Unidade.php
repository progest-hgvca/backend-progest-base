<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated Usar o Model \App\Models\Polo.
 *             A tabela 'unidades' foi renomeada para 'polos' (migration 2026_05_25).
 */
class Unidade extends Model
{
    use HasFactory;

    protected $table = 'polos';

    protected $fillable = [
        'nome',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacionamentos
    public function setores()
    {
        return $this->hasMany(Setores::class, 'polo_id');
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

    // Accessors
    public function isAtivo()
    {
        return $this->status === 'A';
    }
}
