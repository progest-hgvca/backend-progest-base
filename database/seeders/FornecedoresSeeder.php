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
            // Principais Indústrias e Distribuidoras Farmacêuticas e Hospitalares
            [
                'tipo_pessoa' => 'J',
                'razao_social_nome' => 'Cristália Produtos Químicos Farmacêuticos Ltda',
                'cnpj' => '44734671000151',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'J',
                'razao_social_nome' => 'Eurofarma Laboratórios S.A.',
                'cnpj' => '61190096000192',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'J',
                'razao_social_nome' => 'Cremer S.A. (Materiais Cirúrgicos e Curativos)',
                'cnpj' => '82641325000118',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'J',
                'razao_social_nome' => 'Fresenius Kabi Brasil Ltda (Soluções e Nutrição)',
                'cnpj' => '49324221000104',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'J',
                'razao_social_nome' => 'Becton Dickinson (BD) Indústrias Cirúrgicas Ltda',
                'cnpj' => '21551379000106',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'J',
                'razao_social_nome' => 'Halex Istar Indústria Farmacêutica Ltda',
                'cnpj' => '01571702000198',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'J',
                'razao_social_nome' => 'Elfa Medicamentos S.A. (Distribuição Especializada)',
                'cnpj' => '09053134000145',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'J',
                'razao_social_nome' => 'Santa Cruz Distribuidora de Medicamentos Ltda',
                'cnpj' => '61940292000137',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'J',
                'razao_social_nome' => 'Medix Brasil Produtos Hospitalares (Descartáveis)',
                'cnpj' => '10268780000109',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'J',
                'razao_social_nome' => 'CleanMed Higiene e Saneantes Hospitalares Ltda',
                'cnpj' => '14882315000120',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'J',
                'razao_social_nome' => 'Suprimentos & Papelaria Bahia Ltda',
                'cnpj' => '18291442000163',
                'status' => 'A',
            ],
            // Fornecedores de compatibilidade legada
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
            // Pessoas Físicas (Consultorias Técnicas e Perícias)
            [
                'tipo_pessoa' => 'F',
                'razao_social_nome' => 'Dr. Roberto Antunes (Médico Consultor Farmacovigilância)',
                'cpf' => '55432198700',
                'status' => 'A',
            ],
            [
                'tipo_pessoa' => 'F',
                'razao_social_nome' => 'Dra. Mariana Silveira (Farmacêutica Perita Técnica)',
                'cpf' => '77889911233',
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
