<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setores extends Model
{
    use HasFactory;

    protected $table = 'setores';

    protected $fillable = [
        'polo_id',
        'nome',
        'descricao',
        'status',
        'estoque',
        'tipo'
    ];

    protected $casts = [
        'estoque' => 'boolean',
    ];

    /**
     * Relacionamento com polo
     */
    public function polo()
    {
        return $this->belongsTo(Polo::class, 'polo_id');
    }

    /**
     * Compatibilidade legada: alias para `polo()`.
     */
    public function unidade()
    {
        return $this->polo();
    }

    /**
     * Obter produtos disponíveis para este setor baseado no tipo
     */
    public function produtosDisponiveis()
    {
        return Produto::whereHas('grupoProduto', function ($query) {
            $query->where('tipo', $this->tipo);
        });
    }

    /**
     * Obter grupos de produtos compatíveis com este setor
     */
    public function gruposCompatíveis()
    {
        return GrupoProduto::where('tipo', $this->tipo)->where('status', 'A');
    }

    /**
     * Relacionamento com estoque
     */
    public function estoques()
    {
        return $this->hasMany(Estoque::class, 'polo_id');
    }

    /**
     * Relacionamento com usuários
     */
    public function usuarios()
    {
        // Relacionamento many-to-many via tabela pivot 'usuario_setor' (contém 'perfil')
        return $this->belongsToMany(User::class, 'usuario_setor', 'setor_id', 'usuario_id')
            ->withPivot('perfil')
            ->withTimestamps();
    }

    /**
     * Distribuidores relacionados a este setor (como solicitante)
     */
    public function distribuidoresRelacionados()
    {
        return $this->hasMany(SetorDistribuidor::class, 'setor_solicitante_id');
    }
}
