<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadeMedida extends Model
{
    use HasFactory;

    protected $table = 'unidade_medida';
    protected $fillable = ['nome', 'quantidade_unidade_minima', 'status'];
}
