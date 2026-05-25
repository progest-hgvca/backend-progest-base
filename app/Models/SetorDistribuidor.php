<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetorDistribuidor extends Model
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

    public function distribuidor()
    {
        return $this->belongsTo(Setores::class, 'setor_distribuidor_id');
    }
}
