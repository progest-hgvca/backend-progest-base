<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated Usar o Model \App\Models\SetorDistribuidor.
 *             A tabela 'setor_fornecedor' foi renomeada para 'setor_distribuidor' (migration 2026_05_25).
 *             A coluna 'setor_fornecedor_id' foi renomeada para 'setor_distribuidor_id'.
 */
class SetorFornecedor extends Model
{
    use HasFactory;

    protected $table = 'setor_distribuidor';

    protected $fillable = [
        'setor_solicitante_id',
        'setor_distribuidor_id'
    ];

    public function solicitante()
    {
        return $this->belongsTo(Setores::class, 'setor_solicitante_id');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Setores::class, 'setor_distribuidor_id');
    }
}
