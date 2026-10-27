<?php

namespace App\Http\Requests\Movimentacao;

use App\Http\Requests\BaseFormRequest;

class ConfirmarRecebimentoRequest extends BaseFormRequest
{
    /**
     * Regras de validação para a confirmação de recebimento.
     *
     * A autorização (quem pode confirmar) é resolvida no controller, que
     * precisa da movimentação carregada para saber se o setor do usuário é o
     * destino — ou a origem, no caso de devolução.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'observacao' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'observacao.max' => 'A observação do recebimento deve ter no máximo 500 caracteres.',
        ];
    }
}
