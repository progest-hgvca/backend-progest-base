<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PolosESetoresDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Estrutura completa da rede hospitalar conforme documento resumo_setores (atualizado 2026-08-24).
     * Polos: HGVC, HAP, HCS, UPA
     * Total de setores: 63 (7 com estoque, 56 sem estoque)
     *
     * Pendências abertas (não implementadas intencionalmente):
     *   P1 — Salas de Emergência solicitam SOROS à CAF — definir mecanismo de implementação.
     *   P2 — UTIs do HGVC podem precisar de estoque para soluções padrão de infusão contínua.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        // =====================================================================
        // 1. Polos
        // =====================================================================
        $polos = [
            ['nome' => 'Hospital Geral',               'sigla' => 'HGVC', 'status' => 'A'],
            ['nome' => 'Hospital Afrânio Peixoto',     'sigla' => 'HAP',  'status' => 'A'],
            ['nome' => 'Hospital Crescêncio Silveira', 'sigla' => 'HCS',  'status' => 'A'],
            ['nome' => 'Unidade de Pronto Atendimento','sigla' => 'UPA',  'status' => 'A'],
        ];

        foreach ($polos as $polo) {
            DB::table('polos')->updateOrInsert(
                ['nome' => $polo['nome']],
                [
                    'sigla'      => $polo['sigla'],
                    'status'     => $polo['status'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // =====================================================================
        // 2. Setores
        // =====================================================================
        $hgvc = DB::table('polos')->where('nome', 'Hospital Geral')->first();
        $hap  = DB::table('polos')->where('nome', 'Hospital Afrânio Peixoto')->first();
        $hcs  = DB::table('polos')->where('nome', 'Hospital Crescêncio Silveira')->first();
        $upa  = DB::table('polos')->where('nome', 'Unidade de Pronto Atendimento')->first();

        if (!$hgvc || !$hap || !$hcs || !$upa) {
            $this->command->error('Erro ao recuperar os Polos criados.');
            return;
        }

        // Helper para montar array de setor
        $s = fn($polo_id, $nome, $estoque, $tipo = 'Ambos') => [
            'polo_id' => $polo_id,
            'nome'    => $nome,
            'estoque' => $estoque,
            'status'  => 'A',
            'tipo'    => $tipo,
        ];

        $setores = [
            // HGVC — COM ESTOQUE
            $s($hgvc->id, 'CENTRAL DE ABASTECIMENTO FARMACÊUTICO (CAF)', true),
            $s($hgvc->id, 'FARMÁCIA DE DISPENSAÇÃO',                     true),
            
            // HGVC — SEM ESTOQUE: Assistenciais/Clínicas/UTIs
            $s($hgvc->id, 'CENTRO CIRÚRGICO', false, 'Ambos'),
            $s($hgvc->id, 'CLÍNICA MÉDICA',   false, 'Ambos'),
            $s($hgvc->id, 'UTI 1',            false, 'Ambos'),
            $s($hgvc->id, 'SALA VERMELHA',    false, 'Ambos'),

            // HGVC — SEM ESTOQUE: Administrativos
            $s($hgvc->id, 'RECEPÇÃO',         false, 'Material'),
            $s($hgvc->id, 'RH',               false, 'Material'),
            $s($hgvc->id, 'ALMOXARIFADO',     false, 'Material'),

            // HAP (Apenas para ter outro polo)
            $s($hap->id, 'FARMÁCIA CENTRAL',  true),
            $s($hap->id, 'UTI 5',             false, 'Ambos'),
            $s($hap->id, 'COORDENAÇÃO',       false, 'Material'),

            // UPA
            $s($upa->id, 'FARMÁCIA', true),
        ];

        foreach ($setores as $setor) {
            DB::table('setores')->updateOrInsert(
                ['polo_id' => $setor['polo_id'], 'nome' => $setor['nome']],
                [
                    'descricao'  => null,
                    'tipo'       => $setor['tipo'],
                    'estoque'    => $setor['estoque'],
                    'status'     => $setor['status'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // =====================================================================
        // 3. Relações de distribuição: setor_distribuidor
        // =====================================================================
        // Helper: busca setor pelo nome e polo
        $get = fn($nome, $polo_id) => DB::table('setores')
            ->where('nome', $nome)
            ->where('polo_id', $polo_id)
            ->first();

        // --- HGVC: Setores com estoque ---
        $caf      = $get('CENTRAL DE ABASTECIMENTO FARMACÊUTICO (CAF)', $hgvc->id);
        $dispensa = $get('FARMÁCIA DE DISPENSAÇÃO',                     $hgvc->id);
        $satEmerg = $get('SATÉLITE DA EMERGÊNCIA',                      $hgvc->id);
        $cc       = $get('CENTRO CIRÚRGICO',                            $hgvc->id);

        // --- HGVC: Setores sem estoque ---
        $clinMed   = $get('CLÍNICA MÉDICA',    $hgvc->id);
        $clinCir   = $get('CLÍNICA CIRÚRGICA', $hgvc->id);
        $pediatria = $get('PEDIATRIA',         $hgvc->id);

        $uti1   = $get('UTI 1',     $hgvc->id);
        $uti2   = $get('UTI 2',     $hgvc->id);
        $uti3a  = $get('UTI 3A',    $hgvc->id);
        $uti3b  = $get('UTI 3B',    $hgvc->id);
        $uti4   = $get('UTI 4',     $hgvc->id);
        $utiP1  = $get('UTI PED1',  $hgvc->id);
        $utiP2  = $get('UTI PED 2', $hgvc->id);
        $utiNeo = $get('UTI NEO',   $hgvc->id);

        $salaVerm   = $get('SALA VERMELHA',       $hgvc->id);
        $salaTrauma = $get('SALA DE TRAUMA',       $hgvc->id);
        $obsM       = $get('OBSERVAÇÃO MASCULINA', $hgvc->id);
        $obsF       = $get('OBSERVAÇÃO FEMININA',  $hgvc->id);
        $salaMed    = $get('SALA DE MEDICAÇÃO',    $hgvc->id);

        // --- HAP ---
        $farmCentralHAP  = $get('FARMÁCIA CENTRAL',  $hap->id);
        $farmSateliteHAP = $get('FARMÁCIA SATÉLITE', $hap->id);
        $uti5    = $get('UTI 5',       $hap->id);
        $uti6a   = $get('UTI 6A',      $hap->id);
        $uti6b   = $get('UTI 6B',      $hap->id);
        $intern  = $get('INTERNAÇÃO',  $hap->id);
        $ambHAP  = $get('AMBULATÓRIO', $hap->id);
        $recHAP  = $get('RECEPÇÃO',    $hap->id);
        $coordHAP= $get('COORDENAÇÃO', $hap->id);

        // --- HCS ---
        $farmHCS    = $get('FARMÁCIA',           $hcs->id);
        $clinMedHCS = $get('CLÍNICA MÉDICA',     $hcs->id);
        $clinPsiHCS = $get('CLÍNICA PSIQUIÁTRICA',$hcs->id);

        // --- UPA ---
        $farmUPA = $get('FARMÁCIA', $upa->id);

        // -----------------------------------------------------------------
        // Relações de Distribuição (tabela setor_distribuidor)
        // -----------------------------------------------------------------
        $farmaciasComEstoque = array_filter([
            $caf,
            $dispensa,
            $satEmerg,
            $farmCentralHAP,
            $farmSateliteHAP,
            $farmHCS,
            $farmUPA,
        ]);

        $relacoes = [];

        // 1. Remanejamento (Cross-Docking): Toda farmácia com estoque pode solicitar de qualquer outra farmácia com estoque
        foreach ($farmaciasComEstoque as $origem) {
            foreach ($farmaciasComEstoque as $destino) {
                if ($origem->id !== $destino->id) {
                    $relacoes[] = [$origem, $destino];
                }
            }
        }

        // 2. HGVC: Centro Cirúrgico — pode solicitar de todas as farmácias com estoque de todos os polos
        foreach ($farmaciasComEstoque as $distribuidor) {
            if ($cc && $distribuidor && $cc->id !== $distribuidor->id) {
                $relacoes[] = [$cc, $distribuidor];
            }
        }

        // 3. HGVC: Clínicas ← Farmácia de Dispensação E ← CAF (medicamentos de uso coletivo)
        foreach ([$clinMed, $clinCir, $pediatria] as $clinica) {
            if ($clinica) {
                $relacoes[] = [$clinica, $dispensa];
                $relacoes[] = [$clinica, $caf];
            }
        }

        // 4. HGVC: UTIs ← Farmácia de Dispensação E ← CAF (soluções padrão)
        foreach ([$uti1, $uti2, $uti3a, $uti3b, $uti4, $utiP1, $utiP2, $utiNeo] as $uti) {
            if ($uti) {
                $relacoes[] = [$uti, $dispensa];
                $relacoes[] = [$uti, $caf];
            }
        }

        // 5. HGVC: Salas de Emergência ← Satélite da Emergência E ← CAF (soros)
        foreach ([$salaVerm, $salaTrauma, $obsM, $obsF, $salaMed] as $salaEmerg) {
            if ($salaEmerg) {
                $relacoes[] = [$salaEmerg, $satEmerg];
                $relacoes[] = [$salaEmerg, $caf];
            }
        }

        // 6. HAP: UTIs ← Farmácia Satélite (HAP)
        foreach ([$uti5, $uti6a, $uti6b] as $utiHAP) {
            if ($utiHAP) {
                $relacoes[] = [$utiHAP, $farmSateliteHAP];
            }
        }

        // 7. HAP: Demais setores ← Farmácia Central (HAP)
        foreach ([$intern, $ambHAP, $recHAP, $coordHAP] as $setorHAP) {
            if ($setorHAP) {
                $relacoes[] = [$setorHAP, $farmCentralHAP];
            }
        }

        // 8. HCS: Clínicas ← Farmácia (HCS)
        foreach ([$clinMedHCS, $clinPsiHCS] as $clinHCS) {
            if ($clinHCS) {
                $relacoes[] = [$clinHCS, $farmHCS];
            }
        }

        // 9. HGVC: Setores Administrativos e Assistenciais ← CAF
        $nomesAdminAssist = [
            'DIRETORIAS', 'OUVIDORIA', 'TRANSPORTE', 'MANUTENÇÃO PREDIAL',
            'MANUTENÇÃO DE EQUIPAMENTOS', 'CME', 'NUTRIÇÃO', 'NEP', 'LABORATÓRIO',
            'RECEPÇÃO', 'RH', 'COMPRAS', 'FINANCEIRO', 'CPL', 'CONTRATOS',
            'SAME', 'SERVIÇO SOCIAL', 'NIR', 'AGÊNCIA TRANSFUSIONAL', 'ULTRASSOM',
            'CIHDOTT', 'VIGILÂNCIA EPIDEMIOLÓGICA', 'CCIH', 'SIAST', 'TI',
            'PATRIMÔNIO', 'ALMOXARIFADO',
            'CHD', 'AMBULATÓRIO DE GASTRO', 'UNACON',
        ];

        foreach ($nomesAdminAssist as $nome) {
            $setor = $get($nome, $hgvc->id);
            if ($setor && $caf) {
                $relacoes[] = [$setor, $caf];
            }
        }

        foreach ($relacoes as [$solicitante, $distribuidor]) {
            if (!$solicitante || !$distribuidor) {
                continue;
            }
            DB::table('setor_distribuidor')->updateOrInsert(
                [
                    'setor_solicitante_id'  => $solicitante->id,
                    'setor_distribuidor_id' => $distribuidor->id,
                ],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $this->command->info('PolosESetoresSeeder concluído: ' . count($setores) . ' setores inseridos/atualizados.');
    }
}
