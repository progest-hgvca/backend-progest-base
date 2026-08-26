<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class ProdutoRequest extends BaseFormRequest
{
    /** Listas da Portaria SVS/MS 344/98 */
    public const LISTAS_PORTARIA = [
        'A1', 'A2', 'A3', 'B1', 'B2',
        'C1', 'C2', 'C3', 'C4', 'C5',
        'D1', 'D2', 'E', 'F',
    ];

    public function rules()
    {
        $produto = $this->input('produto', []);
        $id = $produto['id'] ?? null;
        $isUpdate = !empty($id);

        return [
            'produto.id' => $isUpdate ? 'required|integer|exists:produtos,id' : 'nullable',
            'produto.nome' => [
                'required',
                'string',
                'min:3',
                'max:191',
                // Impedir produtos duplicados: nome + marca devem ser únicos
                Rule::unique('produtos', 'nome')->where(function ($query) use ($produto) {
                    $marca = $produto['marca'] ?? null;
                    if ($marca) {
                        $query->where('marca', $marca);
                    } else {
                        $query->whereNull('marca');
                    }
                })->ignore($id),
            ],
            'produto.marca' => 'nullable|string|max:191',
            'produto.codigo_simpas' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9\-\.]+$/',
                'unique:produtos,codigo_simpas,' . $id,
            ],
            'produto.codigo_barras' => [
                'nullable',
                'string',
                'max:13',
                'regex:/^\d+$/',
                'unique:produtos,codigo_barras,' . $id,
            ],
            'produto.grupo_produto_id' => 'required|exists:grupo_produto,id',
            'produto.lista_portaria' => 'nullable|in:' . implode(',', self::LISTAS_PORTARIA),
            'produto.unidade_medida_id' => 'required|exists:unidade_medida,id',
            'produto.status' => 'nullable|in:A,I'
        ];
    }

    /**
     * A lista da Portaria 344/98 é obrigatória quando o grupo do produto
     * está marcado como grupo de medicamentos controlados.
     *
     * Fica em withValidator porque a regra `nullable` interrompe as demais
     * regras do campo quando ele vem vazio.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $produto = $this->input('produto', []);
            $grupoId = $produto['grupo_produto_id'] ?? null;

            if (!$grupoId || !empty($produto['lista_portaria'])) {
                return;
            }

            if (\App\Models\GrupoProduto::where('id', $grupoId)->value('controlado')) {
                $validator->errors()->add(
                    'produto.lista_portaria',
                    'Informe a lista da Portaria 344/98 para medicamentos controlados.'
                );
            }
        });
    }

    public function messages()
    {
        return [
            'produto.nome.required' => 'O nome do produto é obrigatório.',
            'produto.nome.min' => 'O nome deve ter no mínimo 3 caracteres.',
            'produto.nome.unique' => 'Já existe um produto com este nome e marca cadastrado.',
            'produto.grupo_produto_id.required' => 'O grupo do produto é obrigatório.',
            'produto.unidade_medida_id.required' => 'A unidade de medida é obrigatória.',

            'produto.nome.max' => 'O nome não pode ter mais de 191 caracteres.',
            'produto.marca.max' => 'A marca não pode ter mais de 191 caracteres.',

            'produto.codigo_simpas.unique' => 'Este código SIMPAS já está cadastrado.',
            'produto.codigo_simpas.max' => 'O código SIMPAS deve ter no máximo 20 caracteres.',
            'produto.codigo_simpas.regex' => 'O código SIMPAS deve conter apenas letras, números, hífens e pontos.',
            'produto.codigo_barras.unique' => 'Este código de barras já está cadastrado.',
            'produto.codigo_barras.max' => 'O código de barras deve ter no máximo 13 dígitos.',
            'produto.codigo_barras.regex' => 'O código de barras deve conter apenas números.',
            'produto.grupo_produto_id.exists' => 'Grupo de produto não encontrado.',
            'produto.unidade_medida_id.exists' => 'Unidade de medida não encontrada.',
            'produto.status.in' => 'Status deve ser A (Ativo) ou I (Inativo).',
            'produto.lista_portaria.in' => 'Lista da Portaria 344/98 invalida.'
        ];
    }

    public function attributes()
    {
        return [
            'produto.nome' => 'Nome do Produto',
            'produto.marca' => 'Marca',
            'produto.codigo_simpas' => 'Código SIMPAS',
            'produto.codigo_barras' => 'Código de Barras',
            'produto.grupo_produto_id' => 'Grupo',
            'produto.unidade_medida_id' => 'Unidade de Medida',
            'produto.status' => 'Status',
            'produto.lista_portaria' => 'Lista da Portaria 344/98'
        ];
    }
}
