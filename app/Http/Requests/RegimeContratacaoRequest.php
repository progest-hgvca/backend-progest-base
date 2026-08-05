<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class RegimeContratacaoRequest extends BaseFormRequest
{
    public function rules()
    {
        $data = $this->input('regimeContratacao', $this->all());
        $id = $data['id'] ?? null;

        $rules = [
            'regimeContratacao.nome' => 'required|string|min:3|max:191|unique:regime_contratacao,nome,' . $id,
            'regimeContratacao.descricao' => 'nullable|string|max:191',
            'regimeContratacao.status' => 'required|in:A,I',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'regimeContratacao.nome.required' => 'O nome do Regime de Contratação é obrigatório.',
            'regimeContratacao.status.required' => 'O status do regime é obrigatório.',

            'regimeContratacao.nome.min' => 'O nome do Regime de Contratação deve ter no mínimo 3 caracteres.',
            'regimeContratacao.nome.max' => 'O nome do Regime de Contratação deve ter no máximo 191 caracteres.',
            'regimeContratacao.descricao.max' => 'A descrição do Regime de Contratação deve ter no máximo 191 caracteres.',

            'regimeContratacao.status.in' => 'Status deve ser A (Ativo) ou I (Inativo)',
            'regimeContratacao.nome.unique' => 'Este Regime de Contratação já está cadastrado.',
        ];
    }

    public function attributes()
    {
        return [
            'regimeContratacao.nome' => 'Nome',
            'regimeContratacao.descricao' => 'Descrição',
            'regimeContratacao.status' => 'Status'
        ];
    }
}
