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
        'quantidade_solicitada',
        'quantidade_aprovada',
        'quantidade',
        'motivo',
        'usuario_id'
    ];

    protected $appends = ['numero_pedido', 'pedido_origem_id'];

    protected $casts = [
        'quantidade' => 'integer',
        'quantidade_solicitada' => 'integer',
        'quantidade_aprovada' => 'integer',
    ];

    public function getNumeroPedidoAttribute()
    {
        return $this->movimentacao_id;
    }

    public function getPedidoOrigemIdAttribute()
    {
        return $this->movimentacao_id;
    }

    public function movimentacao()
    {
        return $this->belongsTo(Movimentacao::class, 'movimentacao_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Movimentacao::class, 'movimentacao_id');
    }

    public function movimentacaoOrigem()
    {
        return $this->belongsTo(Movimentacao::class, 'movimentacao_id');
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
