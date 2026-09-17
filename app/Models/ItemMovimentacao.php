<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemMovimentacao extends Model
{
    protected $table = 'item_movimentacao';
    protected $fillable = ['movimentacao_id', 'produto_id', 'quantidade_solicitada', 'quantidade_liberada', 'quantidade_devolvendo', 'lote'];
    protected $appends = ['codigo_simpass', 'codigo_simpas', 'data_formatada'];

    public function getCodigoSimpassAttribute()
    {
        return $this->produto ? ($this->produto->codigo_simpas ?? $this->produto->codigo_simpass) : null;
    }

    public function getCodigoSimpasAttribute()
    {
        return $this->produto ? ($this->produto->codigo_simpas ?? $this->produto->codigo_simpass) : null;
    }

    public function getDataFormatadaAttribute()
    {
        if ($this->relationLoaded('movimentacao') && $this->movimentacao) {
            return $this->movimentacao->data_formatada;
        }
        return null;
    }

    public function movimentacao()
    {
        return $this->belongsTo(Movimentacao::class);
    }
    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function estoqueLote()
    {
        return $this->belongsTo(EstoqueLote::class, 'lote');
    }
    public function devolucoes()
    {
        return $this->hasMany(Devolucao::class);
    }
}
