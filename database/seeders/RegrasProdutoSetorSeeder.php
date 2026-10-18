<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Conjunto padrão de regras "que grupos de produto cada categoria de setor
 * pode solicitar".
 *
 * As regras são DERIVADAS da classificação do grupo (tipo + controlado + nome),
 * não de uma lista fixa de nomes. O catálogo do ProGest já mudou de vocabulário
 * uma vez (5 grupos antigos → 9 no catálogo oficial), e uma lista fixa
 * silenciosamente deixaria setores sem nenhum produto após a troca.
 *
 * Reflete a prática hospitalar:
 *   - Farmácia       : sem restrição — é quem abastece todo mundo.
 *   - Internamento   : tudo que é clínico e de uso na assistência. Fora:
 *                      medicamentos controlados (Portaria 344/98, dispensados
 *                      pela farmácia por paciente, não por requisição de
 *                      estoque) e material de expediente.
 *   - Administrativo : só material não-clínico — expediente, limpeza e gerais.
 *
 * Casos isolados — uma UTI que precise manter controlados em estoque, por
 * exemplo — se resolvem por exceção em `setor_produto_excecao`, sem afrouxar a
 * regra da categoria inteira.
 *
 * Idempotente: reescreve por completo as regras das três categorias.
 */
class RegrasProdutoSetorSeeder extends Seeder
{
    /** Grupos de material que não têm uso assistencial. */
    private const MATERIAL_ADMINISTRATIVO = [
        'MATERIAL DE EXPEDIENTE',
    ];

    /** Grupos de material exclusivos da assistência. */
    private const MATERIAL_CLINICO = [
        'MATERIAL MÉDICO HOSPITALAR',
        'MATERIAL MEDICO HOSPITALAR',
    ];

    public function run()
    {
        $grupos = DB::table('grupo_produto')->get(['id', 'nome', 'tipo', 'controlado']);

        if ($grupos->isEmpty()) {
            $this->command->warn('Nenhum grupo de produto cadastrado — regras não aplicadas.');
            return;
        }

        $regras = [
            'Farmacia'       => [],
            'Internamento'   => [],
            'Administrativo' => [],
        ];

        foreach ($grupos as $grupo) {
            $nome        = mb_strtoupper($grupo->nome, 'UTF-8');
            $ehMedicamento = $grupo->tipo === 'Medicamento';
            $ehControlado  = (bool) $grupo->controlado;

            // Farmácia abastece o hospital inteiro: recebe todos os grupos.
            $regras['Farmacia'][] = $grupo->id;

            if ($ehMedicamento) {
                // Assistência recebe medicamentos, menos os da Portaria 344/98.
                if (!$ehControlado) {
                    $regras['Internamento'][] = $grupo->id;
                }
                continue;
            }

            // Material: expediente é só do administrativo, material médico é só
            // da assistência, e o restante (limpeza, geral) serve aos dois.
            $ehExpediente = in_array($nome, self::MATERIAL_ADMINISTRATIVO, true);
            $ehClinico    = in_array($nome, self::MATERIAL_CLINICO, true);

            if (!$ehExpediente) {
                $regras['Internamento'][] = $grupo->id;
            }
            if (!$ehClinico) {
                $regras['Administrativo'][] = $grupo->id;
            }
        }

        DB::transaction(function () use ($regras) {
            foreach ($regras as $categoria => $grupoIds) {
                DB::table('categoria_grupo_produto')->where('categoria', $categoria)->delete();

                foreach (array_unique($grupoIds) as $grupoId) {
                    DB::table('categoria_grupo_produto')->insert([
                        'categoria'        => $categoria,
                        'grupo_produto_id' => $grupoId,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }
        });

        foreach ($regras as $categoria => $grupoIds) {
            $this->command->info(sprintf(
                '%-15s %d grupo(s) liberado(s).',
                $categoria . ':',
                count(array_unique($grupoIds))
            ));
        }
    }
}
