<?php

namespace Database\Seeders;

use App\Models\Fornecedor;
use Illuminate\Database\Seeder;

class FornecedoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fornecedores = [
            [
                'tipo_pessoa' => 'J',
                'razao_social_nome' => 'Distribuidora Pharma LTDA',
                'cnpj' => '11222333000144',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'J',
                'razao_social_nome' => 'Medic Supplies Brasil S.A.',
                'cnpj' => '04567891000102',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'J',
                'razao_social_nome' => 'Brasil Hospitalar LTDA',
                'cnpj' => '33444555000166',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'J',
                'razao_social_nome' => 'Saúde Total Distribuidora',
                'cnpj' => '44555666000177',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'J',
                'razao_social_nome' => 'Equipamentos Médicos do Nordeste',
                'cnpj' => '55666777000188',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'F',
                'razao_social_nome' => 'Maria de Lourdes Silva',
                'cpf' => '12345678901',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'F',
                'razao_social_nome' => 'João Antônio Pereira',
                'cpf' => '98765432100',
                'status' => 'I',
            ],
        ];

        foreach ($fornecedores as $dados) {
            $criterios = $dados['tipo_pessoa'] === 'F'
                ? ['cpf' => $dados['cpf']]
                : ['cnpj' => $dados['cnpj']];

            Fornecedor::updateOrCreate(
                $criterios,
                [
                    'tipo_pessoa' => $dados['tipo_pessoa'],
                    'razao_social_nome' => $dados['razao_social_nome'],
                    'cpf' => $dados['cpf'] ?? null,
                    'cnpj' => $dados['cnpj'] ?? null,
                    'status' => $dados['status'] ?? 'A',
                ]
            );
        }
    }
}
