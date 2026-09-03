<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegimeContratacaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Cadastra todos os regimes de contratação aplicáveis à área hospitalar e à saúde pública/privada.
     * Preserva os IDs 1 a 6 originais para garantir integridade referencial com usuários existentes.
     *
     * @return void
     */
    public function run()
    {
        $regimes = [
            [
                'id' => 1,
                'nome' => 'Efetivo',
                'descricao' => 'Servidor público concursado regido pelo Regime Jurídico Único (Estatutário)',
                'status' => 'A',
            ],
            [
                'id' => 2,
                'nome' => 'Contrato',
                'descricao' => 'Contratação temporária por Processo Seletivo Simplificado (PSS)',
                'status' => 'A',
            ],
            [
                'id' => 3,
                'nome' => 'Temporário',
                'descricao' => 'Contrato de trabalho temporário por prazo determinado',
                'status' => 'A',
            ],
            [
                'id' => 4,
                'nome' => 'Estagiário',
                'descricao' => 'Estudante em estágio curricular ou extracurricular (Lei nº 11.788/2008)',
                'status' => 'A',
            ],
            [
                'id' => 5,
                'nome' => 'Terceirizado',
                'descricao' => 'Colaborador contratado por empresa prestadora de serviços continuados',
                'status' => 'A',
            ],
            [
                'id' => 6,
                'nome' => 'Residente',
                'descricao' => 'Profissional em programa de Residência Médica ou Multiprofissional em Saúde',
                'status' => 'A',
            ],
            [
                'id' => 7,
                'nome' => 'CLT',
                'descricao' => 'Contrato individual de trabalho regido pela CLT (Fundações, OS, EBSERH, Filantrópicos)',
                'status' => 'A',
            ],
            [
                'id' => 8,
                'nome' => 'Comissionado',
                'descricao' => 'Cargo em comissão de livre nomeação e exoneração (Direção/Chefia/Coordenação)',
                'status' => 'A',
            ],
            [
                'id' => 9,
                'nome' => 'Pessoa Jurídica (PJ)',
                'descricao' => 'Prestação de serviços médicos, multiprofissionais ou técnicos via PJ',
                'status' => 'A',
            ],
            [
                'id' => 10,
                'nome' => 'Cooperado',
                'descricao' => 'Profissional associado a cooperativa de trabalho médico ou de enfermagem',
                'status' => 'A',
            ],
            [
                'id' => 11,
                'nome' => 'Cedido / Disposição',
                'descricao' => 'Servidor público efetivo cedido por outro ente governamental ou órgão',
                'status' => 'A',
            ],
            [
                'id' => 12,
                'nome' => 'Autônomo (RPA)',
                'descricao' => 'Profissional autônomo prestador de serviços com Recibo de Pagamento a Autônomo',
                'status' => 'A',
            ],
            [
                'id' => 13,
                'nome' => 'Voluntário',
                'descricao' => 'Prestação de serviço voluntário em saúde e apoio comunitário (Lei nº 9.608/1998)',
                'status' => 'A',
            ],
        ];

        foreach ($regimes as $regime) {
            DB::table('regime_contratacao')->updateOrInsert(
                ['id' => $regime['id']],
                [
                    'nome' => $regime['nome'],
                    'descricao' => $regime['descricao'],
                    'status' => $regime['status'],
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('Regimes de contratação cadastrados com sucesso: ' . count($regimes) . ' regimes ativos.');
    }
}
