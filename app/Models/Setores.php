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
        'tipo',
        'categoria'
    ];

    /**
     * Classificação funcional do setor, usada para decidir o que ele pode solicitar.
     */
    public const CATEGORIAS = ['Farmacia', 'Internamento', 'Administrativo'];

    public const CATEGORIA_LABELS = [
        'Farmacia'       => 'Farmácia',
        'Internamento'   => 'Internamento',
        'Administrativo' => 'Administrativo',
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
     * Tipos de grupo de produto que este setor movimenta.
     * Setor 'Ambos' aceita as duas naturezas.
     */
    public function tiposCompativeis(): array
    {
        return $this->tipo === 'Ambos' ? ['Medicamento', 'Material'] : [$this->tipo];
    }

    /**
     * IDs dos grupos de produto liberados para a categoria deste setor.
     *
     * Retorna null quando não há nenhuma regra cadastrada para a categoria —
     * tratado pelos chamadores como "sem restrição", para que a ausência do
     * seeder de regras não bloqueie o sistema inteiro.
     */
    public function gruposPermitidosIds(): ?array
    {
        if (empty($this->categoria)) {
            return null;
        }

        $ids = CategoriaGrupoProduto::porCategoria($this->categoria)
            ->pluck('grupo_produto_id')
            ->all();

        return empty($ids) ? null : $ids;
    }

    /**
     * Query dos produtos que este setor pode solicitar.
     *
     * A cadeia de decisão, nesta ordem:
     *   1. compatibilidade de natureza (setores.tipo vs grupo_produto.tipo);
     *   2. lista branca de grupos da categoria do setor;
     *   3. exceções por setor, que liberam ou bloqueiam produtos individuais.
     */
    public function produtosDisponiveis()
    {
        $tipos              = $this->tiposCompativeis();
        $gruposPermitidos   = $this->gruposPermitidosIds();
        $excecoesLiberadas  = SetorProdutoExcecao::where('setor_id', $this->id)
            ->liberacoes()->pluck('produto_id')->all();
        $excecoesBloqueadas = SetorProdutoExcecao::where('setor_id', $this->id)
            ->bloqueios()->pluck('produto_id')->all();

        $query = Produto::query();

        $query->where(function ($q) use ($tipos, $gruposPermitidos, $excecoesLiberadas) {
            // Caminho normal: passa pelo tipo e pela lista branca da categoria.
            $q->where(function ($regra) use ($tipos, $gruposPermitidos) {
                $regra->whereHas('grupoProduto', function ($gq) use ($tipos) {
                    $gq->whereIn('tipo', $tipos);
                });

                if ($gruposPermitidos !== null) {
                    $regra->whereIn('grupo_produto_id', $gruposPermitidos);
                }
            });

            // Liberações pontuais furam a regra da categoria.
            if (!empty($excecoesLiberadas)) {
                $q->orWhereIn('produtos.id', $excecoesLiberadas);
            }
        });

        // Bloqueios pontuais vencem qualquer liberação acima.
        if (!empty($excecoesBloqueadas)) {
            $query->whereNotIn('produtos.id', $excecoesBloqueadas);
        }

        return $query;
    }

    /**
     * Grupos de produto que este setor pode solicitar.
     */
    public function gruposCompativeis()
    {
        $query = GrupoProduto::whereIn('tipo', $this->tiposCompativeis())
            ->where('status', 'A');

        $gruposPermitidos = $this->gruposPermitidosIds();
        if ($gruposPermitidos !== null) {
            $query->whereIn('id', $gruposPermitidos);
        }

        return $query;
    }

    /**
     * Este setor pode solicitar o produto informado?
     * Usado como trava de servidor na criação de movimentações.
     */
    public function podeSolicitarProduto($produtoId): bool
    {
        return $this->produtosDisponiveis()->where('produtos.id', $produtoId)->exists();
    }

    /**
     * Regras de categoria e exceções cadastradas para este setor.
     */
    public function excecoesProduto()
    {
        return $this->hasMany(SetorProdutoExcecao::class, 'setor_id');
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
