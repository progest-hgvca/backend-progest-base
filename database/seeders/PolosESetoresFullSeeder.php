<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ATENÇÃO - REGRA DE NEGÓCIO DO PROJETO:
 * A estrutura de Polos, Setores e Matriz de Distribuição/Consumo abaixo reflete
 * a operação real dos hospitais atendidos pelo ProGest (HGVC, HAP, HCS, UPA).
 * NÃO ALTERAR NEM EXCLUIR estes setores ou relações em seeders/migrations.
 */
class PolosESetoresFullSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Estrutura oficial completa (63 setores)
     */
    public function run()
    {
        $now = Carbon::now();

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

        $hgvc = DB::table('polos')->where('nome', 'Hospital Geral')->first();
        $hap  = DB::table('polos')->where('nome', 'Hospital Afrânio Peixoto')->first();
        $hcs  = DB::table('polos')->where('nome', 'Hospital Crescêncio Silveira')->first();
        $upa  = DB::table('polos')->where('nome', 'Unidade de Pronto Atendimento')->first();

        if (!$hgvc || !$hap || !$hcs || !$upa) {
            $this->command->error('Erro ao recuperar os Polos criados.');
            return;
        }

        $s = fn($polo_id, $nome, $estoque, $tipo = 'Ambos') => [
            'polo_id' => $polo_id,
            'nome'    => $nome,
            'estoque' => $estoque,
            'status'  => 'A',
            'tipo'    => $tipo,
        ];

        $setores = [
            // HGVC — COM ESTOQUE (3 setores)
            $s($hgvc->id, 'CENTRAL DE ABASTECIMENTO FARMACÊUTICO (CAF)', true, 'Ambos'),
            $s($hgvc->id, 'FARMÁCIA DE DISPENSAÇÃO',                     true, 'Ambos'),
            $s($hgvc->id, 'SATÉLITE DA EMERGÊNCIA',                      true, 'Ambos'),

            // HGVC — SEM ESTOQUE: Centro Cirúrgico (1)
            $s($hgvc->id, 'CENTRO CIRÚRGICO', false, 'Ambos'),

            // HGVC — SEM ESTOQUE: Clínicas (3)
            $s($hgvc->id, 'CLÍNICA MÉDICA',    false, 'Ambos'),
            $s($hgvc->id, 'CLÍNICA CIRÚRGICA', false, 'Ambos'),
            $s($hgvc->id, 'PEDIATRIA',         false, 'Ambos'),

            // HGVC — SEM ESTOQUE: UTIs (8)
            $s($hgvc->id, 'UTI 1',     false, 'Ambos'),
            $s($hgvc->id, 'UTI 2',     false, 'Ambos'),
            $s($hgvc->id, 'UTI 3A',    false, 'Ambos'),
            $s($hgvc->id, 'UTI 3B',    false, 'Ambos'),
            $s($hgvc->id, 'UTI 4',     false, 'Ambos'),
            $s($hgvc->id, 'UTI PED1',  false, 'Ambos'),
            $s($hgvc->id, 'UTI PED 2', false, 'Ambos'),
            $s($hgvc->id, 'UTI NEO',   false, 'Ambos'),

            // HGVC — SEM ESTOQUE: Salas de Emergência (5)
            $s($hgvc->id, 'SALA VERMELHA',       false, 'Ambos'),
            $s($hgvc->id, 'SALA DE TRAUMA',       false, 'Ambos'),
            $s($hgvc->id, 'OBSERVAÇÃO MASCULINA', false, 'Ambos'),
            $s($hgvc->id, 'OBSERVAÇÃO FEMININA',  false, 'Ambos'),
            $s($hgvc->id, 'SALA DE MEDICAÇÃO',    false, 'Ambos'),

            // HGVC — SEM ESTOQUE: Setores Administrativos (27)
            $s($hgvc->id, 'DIRETORIAS',                 false, 'Material'),
            $s($hgvc->id, 'OUVIDORIA',                  false, 'Material'),
            $s($hgvc->id, 'TRANSPORTE',                 false, 'Material'),
            $s($hgvc->id, 'MANUTENÇÃO PREDIAL',         false, 'Material'),
            $s($hgvc->id, 'MANUTENÇÃO DE EQUIPAMENTOS', false, 'Material'),
            $s($hgvc->id, 'CME',                        false, 'Material'),
            $s($hgvc->id, 'NUTRIÇÃO',                   false, 'Material'),
            $s($hgvc->id, 'NEP',                        false, 'Material'),
            $s($hgvc->id, 'LABORATÓRIO',                false, 'Material'),
            $s($hgvc->id, 'RECEPÇÃO',                   false, 'Material'),
            $s($hgvc->id, 'RH',                         false, 'Material'),
            $s($hgvc->id, 'COMPRAS',                    false, 'Material'),
            $s($hgvc->id, 'FINANCEIRO',                 false, 'Material'),
            $s($hgvc->id, 'CPL',                        false, 'Material'),
            $s($hgvc->id, 'CONTRATOS',                  false, 'Material'),
            $s($hgvc->id, 'SAME',                       false, 'Material'),
            $s($hgvc->id, 'SERVIÇO SOCIAL',             false, 'Material'),
            $s($hgvc->id, 'NIR',                        false, 'Material'),
            $s($hgvc->id, 'AGÊNCIA TRANSFUSIONAL',      false, 'Material'),
            $s($hgvc->id, 'ULTRASSOM',                  false, 'Material'),
            $s($hgvc->id, 'CIHDOTT',                    false, 'Material'),
            $s($hgvc->id, 'VIGILÂNCIA EPIDEMIOLÓGICA',  false, 'Material'),
            $s($hgvc->id, 'CCIH',                       false, 'Material'),
            $s($hgvc->id, 'SIAST',                      false, 'Material'),
            $s($hgvc->id, 'TI',                         false, 'Material'),
            $s($hgvc->id, 'PATRIMÔNIO',                 false, 'Material'),
            $s($hgvc->id, 'ALMOXARIFADO',               false, 'Material'),

            // HGVC — SEM ESTOQUE: Setores Assistenciais (3)
            $s($hgvc->id, 'CHD',                   false, 'Ambos'),
            $s($hgvc->id, 'AMBULATÓRIO DE GASTRO', false, 'Ambos'),
            $s($hgvc->id, 'UNACON',                false, 'Ambos'),

            // HAP (9 setores)
            $s($hap->id, 'FARMÁCIA CENTRAL',   true, 'Ambos'),
            $s($hap->id, 'FARMÁCIA SATÉLITE',  true, 'Ambos'),
            $s($hap->id, 'UTI 5',              false, 'Ambos'),
            $s($hap->id, 'UTI 6A',             false, 'Ambos'),
            $s($hap->id, 'UTI 6B',             false, 'Ambos'),
            $s($hap->id, 'INTERNAÇÃO',         false, 'Ambos'),
            $s($hap->id, 'AMBULATÓRIO',        false, 'Ambos'),
            $s($hap->id, 'RECEPÇÃO',           false, 'Material'),
            $s($hap->id, 'COORDENAÇÃO',        false, 'Material'),

            // HCS (3 setores)
            $s($hcs->id, 'FARMÁCIA',             true, 'Ambos'),
            $s($hcs->id, 'CLÍNICA MÉDICA',       false, 'Ambos'),
            $s($hcs->id, 'CLÍNICA PSIQUIÁTRICA', false, 'Ambos'),

            // UPA (1 setor)
            $s($upa->id, 'FARMÁCIA', true, 'Ambos'),
        ];

        foreach ($setores as $setor) {
            DB::table('setores')->updateOrInsert(
                ['polo_id' => $setor['polo_id'], 'nome' => $setor['nome']],
                [
                    'estoque'    => $setor['estoque'],
                    'status'     => $setor['status'],
                    'tipo'       => $setor['tipo'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // =====================================================================
        // 3. Relações de distribuição: setor_distribuidor
        // =====================================================================
        $get = fn($nome, $polo_id) => DB::table('setores')->where('nome', $nome)->where('polo_id', $polo_id)->first();

        $farmaciasComEstoque = DB::table('setores')->where('estoque', true)->get();
        $caf      = $get('CENTRAL DE ABASTECIMENTO FARMACÊUTICO (CAF)', $hgvc->id);
        $dispensa = $get('FARMÁCIA DE DISPENSAÇÃO', $hgvc->id);
        $satEmerg = $get('SATÉLITE DA EMERGÊNCIA', $hgvc->id);

        $farmCentralHAP  = $get('FARMÁCIA CENTRAL', $hap->id);
        $farmSateliteHAP = $get('FARMÁCIA SATÉLITE', $hap->id);
        
        $farmHCS = $get('FARMÁCIA', $hcs->id);
        $farmUPA = $get('FARMÁCIA', $upa->id);

        $relacoes = [];

        // 1. Remanejamento: Toda farmácia com estoque pode solicitar de qualquer outra farmácia com estoque
        foreach ($farmaciasComEstoque as $origem) {
            foreach ($farmaciasComEstoque as $destino) {
                if ($origem->id !== $destino->id) {
                    $relacoes[] = [$origem, $destino];
                }
            }
        }

        // 2. Distribuir setores baseados nos Polos e Nomes
        $todosSetores = DB::table('setores')->where('estoque', false)->get();
        
        foreach ($todosSetores as $setor) {
            if ($setor->polo_id === $hgvc->id) {
                if ($setor->nome === 'CENTRO CIRÚRGICO') {
                    // Centro Cirúrgico pega de todas as farmácias
                    foreach ($farmaciasComEstoque as $distribuidor) {
                        $relacoes[] = [$setor, $distribuidor];
                    }
                } elseif (str_contains($setor->nome, 'CLÍNICA') || str_contains($setor->nome, 'UTI') || str_contains($setor->nome, 'PEDIATRIA')) {
                    // Clínicas e UTIs HGVC -> Dispensação e CAF
                    if ($dispensa) $relacoes[] = [$setor, $dispensa];
                    if ($caf) $relacoes[] = [$setor, $caf];
                } elseif (str_contains($setor->nome, 'SALA') || str_contains($setor->nome, 'OBSERVAÇÃO')) {
                    // Emergência HGVC -> Satélite e CAF
                    if ($satEmerg) $relacoes[] = [$setor, $satEmerg];
                    if ($caf) $relacoes[] = [$setor, $caf];
                } else {
                    // Administrativos / Assistenciais -> CAF
                    if ($caf) $relacoes[] = [$setor, $caf];
                }
            } elseif ($setor->polo_id === $hap->id) {
                if (str_contains($setor->nome, 'UTI')) {
                    if ($farmSateliteHAP) $relacoes[] = [$setor, $farmSateliteHAP];
                } else {
                    if ($farmCentralHAP) $relacoes[] = [$setor, $farmCentralHAP];
                }
            } elseif ($setor->polo_id === $hcs->id) {
                if ($farmHCS) $relacoes[] = [$setor, $farmHCS];
            } elseif ($setor->polo_id === $upa->id) {
                if ($farmUPA) $relacoes[] = [$setor, $farmUPA];
            }
        }

        foreach ($relacoes as [$solicitante, $distribuidor]) {
            if (!$solicitante || !$distribuidor) continue;
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

        $this->command->info('PolosESetoresFullSeeder concluído: ' . count($setores) . ' setores e suas relações de distribuição criadas.');
    }
}
