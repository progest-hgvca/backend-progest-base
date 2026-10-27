<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimentacao extends Model
{
    use HasFactory;

    protected $table = 'movimentacao';
    protected $fillable = [
        'usuario_id', 'setor_origem_id', 'setor_destino_id', 'tipo', 'data_hora',
        'observacao', 'status_solicitacao', 'aprovador_usuario_id',
        'atendente_usuario_id', 'atendido_em',
        'recebedor_usuario_id', 'recebido_em',
        'motivo_rejeicao',
    ];
    protected $appends = ['data_formatada', 'data', 'numero_pedido', 'pedido_origem_id', 'movimentacao_origem_id', 'respondido_por', 'avaliado_por'];

    protected $casts = [
        'data_hora'   => 'datetime',
        'atendido_em' => 'datetime',
        'recebido_em' => 'datetime',
    ];

    /**
     * Vocabulário dos fluxos. Fonte única no backend — espelha
     * `src/utils/nomenclaturaFluxo.js` no frontend.
     *
     * 'T' e 'S' descrevem a MESMA operação de negócio (uma requisição); a
     * diferença é só por qual tela ela foi criada. Mantidos separados por
     * compatibilidade com o histórico, mas sempre tratados juntos nos relatórios.
     */
    public const TIPO_LABELS = [
        'T' => 'Requisição',
        'S' => 'Requisição',
        'D' => 'Devolução',
        'C' => 'Consumo interno',
    ];

    public const TIPOS_REQUISICAO = ['S', 'T'];

    public const STATUS_LABELS = [
        'C' => 'Rascunho',
        'P' => 'Pendente',
        'A' => 'Aprovado',
        'R' => 'Reprovado',
        'X' => 'Cancelado',
    ];

    /**
     * Rótulo do fluxo relativo a um setor. O mesmo registro tem nomes
     * diferentes para quem envia e para quem recebe — é justamente isso que
     * distingue uma devolução de saída de uma de entrada.
     */
    public function rotuloFluxoPara($setorId): string
    {
        $setorId = (int) $setorId;
        $souOrigem  = (int) $this->setor_origem_id === $setorId;
        $souDestino = (int) $this->setor_destino_id === $setorId;

        if ($this->tipo === 'C') {
            return 'Consumo interno';
        }

        if ($this->tipo === 'D') {
            if ($souOrigem)  return 'Devolução enviada';
            if ($souDestino) return 'Devolução recebida';
            return 'Devolução';
        }

        if ($souDestino) return 'Requisição enviada';
        if ($souOrigem)  return 'Requisição a atender';
        return 'Requisição';
    }

    public function getDataFormatadaAttribute()
    {
        $dt = $this->data_hora ?? $this->created_at;
        return $dt ? \Carbon\Carbon::parse($dt)->format('d/m/Y') : null;
    }

    public function getDataAttribute()
    {
        return $this->getDataFormatadaAttribute();
    }

    public function getNumeroPedidoAttribute()
    {
        return $this->id;
    }

    public function getPedidoOrigemIdAttribute()
    {
        if ($this->tipo === 'D' && !empty($this->observacao)) {
            if (preg_match('/pedido #?(\d+)/i', $this->observacao, $matches)) {
                return (int) $matches[1];
            }
            if (preg_match('/(?:movimentac(?:ao|ão)|origem)\s*#?\s*(\d+)/i', $this->observacao, $matches)) {
                return (int) $matches[1];
            }
        }
        return null;
    }

    public function getMovimentacaoOrigemIdAttribute()
    {
        return $this->getPedidoOrigemIdAttribute();
    }

    public function getRespondidoPorAttribute()
    {
        return $this->aprovador;
    }

    public function getAvaliadoPorAttribute()
    {
        return $this->aprovador;
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function setorOrigem()
    {
        return $this->belongsTo(Setores::class, 'setor_origem_id');
    }

    public function setorDestino()
    {
        return $this->belongsTo(Setores::class, 'setor_destino_id');
    }

    public function itens()
    {
        return $this->hasMany(ItemMovimentacao::class);
    }

    public function aprovador()
    {
        return $this->belongsTo(User::class, 'aprovador_usuario_id');
    }

    /** Quem separou/liberou fisicamente os itens. */
    public function atendente()
    {
        return $this->belongsTo(User::class, 'atendente_usuario_id');
    }

    /** Quem confirmou o recebimento no setor de destino. */
    public function recebedor()
    {
        return $this->belongsTo(User::class, 'recebedor_usuario_id');
    }
    public function devolucoes()
    {
        return $this->hasMany(Devolucao::class);
    }
}
