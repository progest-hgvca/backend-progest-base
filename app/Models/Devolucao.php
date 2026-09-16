<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devolucao extends Model
{
    use HasFactory;

    protected $table = 'devolucoes';

    protected $fillable = [
        'movimentacao_id',
        'item_movimentacao_id',
        'lote',
        'quantidade',
        'motivo',
        'usuario_id'
    ];

    public function movimentacao()
    {
        return $this->belongsTo(Movimentacao::class);
    }

    public function itemMovimentacao()
    {
        return $this->belongsTo(ItemMovimentacao::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
