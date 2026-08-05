<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegimeContratacao extends Model
{
    use HasFactory;

    protected $table = 'regime_contratacao';
    protected $fillable = ['nome', 'descricao', 'status'];
}
