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

    protected $appends = ['nome_exibicao'];

    /**
     * Acessor para retornar o nome do setor com a sigla do polo
     */
    public function getNomeExibicaoAttribute()
    {
        // Precisamos verificar se o relacionamento já está carregado para evitar N+1 
        // ou garantir que seja resolvido caso necessário
        if ($this->relationLoaded('polo') && $this->polo) {
            return $this->nome . ' (' . $this->polo->sigla . ')';
        }
        
        // Retorno de fallback caso polo_id exista mas não tenha sido carregado na query,
        // carrega on-demand
        if ($this->polo_id) {
            // Usa o relacionamento polo diretamente, o que pode engatilhar lazy loading
            $polo = $this->polo;
            if ($polo) {
                return $this->nome . ' (' . $polo->sigla . ')';
            }
        }

        return $this->nome;
    }

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
    public function gruposCompativeis()
    {
        return GrupoProduto::where('tipo', $this->tipo)->where('status', 'A');
    }

    /**
     * Relacionamento com estoque
     */
    public function estoques()
    {
        // FK na tabela estoque é 'setor_id' que referencia setores.id
        return $this->hasMany(Estoque::class, 'setor_id');
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

    /**
     * Verifica se o setor é a CAF (Central de Abastecimento Farmacêutico).
     * Identificado pelo nome contendo 'CAF' ou 'CENTRAL DE ABASTECIMENTO',
     * ou por ser distribuidor central com estoque sem fornecedores a montante.
     */
    public function isCAF(): bool
    {
        $nomeUpper = mb_strtoupper($this->nome ?? '', 'UTF-8');
        if (str_contains($nomeUpper, 'CAF') || str_contains($nomeUpper, 'CENTRAL DE ABASTECIMENTO')) {
            return true;
        }

        if ($this->estoque && !$this->distribuidoresRelacionados()->exists()) {
            return true;
        }

        return false;
    }
}
