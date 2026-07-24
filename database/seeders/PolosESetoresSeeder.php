<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PolosESetoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
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
            ['nome' => 'Hospital Geral',            'sigla' => 'HGVC', 'status' => 'A'],
            ['nome' => 'Hospital Afrânio Peixoto',  'sigla' => 'HAP',  'status' => 'A'],
            ['nome' => 'Hospital Crescêncio Silveira', 'sigla' => 'HCS', 'status' => 'A'],
            ['nome' => 'UPA',                        'sigla' => 'UPA',  'status' => 'A'],
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
        $upa  = DB::table('polos')->where('nome', 'UPA')->first();

        if (!$hgvc || !$hap || !$hcs || !$upa) {
            $this->command->error('Erro ao recuperar os Polos criados.');
            return;
        }

        $setores = [
            // HGVC - com estoque
            ['polo_id' => $hgvc->id, 'nome' => 'FARMÁCIA CENTRAL',        'estoque' => true,  'status' => 'A', 'tipo' => 'Medicamento'],
            ['polo_id' => $hgvc->id, 'nome' => 'FARMÁCIA DE DISPENSAÇÃO', 'estoque' => true,  'status' => 'A', 'tipo' => 'Medicamento'],
            ['polo_id' => $hgvc->id, 'nome' => 'SATÉLITE DA EMERGÊNCIA',  'estoque' => true,  'status' => 'A', 'tipo' => 'Medicamento'],
            // HGVC - sem estoque
            ['polo_id' => $hgvc->id, 'nome' => 'CENTRO CIRÚRGICO',        'estoque' => false, 'status' => 'A', 'tipo' => 'Medicamento'],
            ['polo_id' => $hgvc->id, 'nome' => 'CLÍNICA MÉDICA',          'estoque' => false, 'status' => 'A', 'tipo' => 'Medicamento'],
            ['polo_id' => $hgvc->id, 'nome' => 'EMERGÊNCIA',              'estoque' => false, 'status' => 'A', 'tipo' => 'Medicamento'],

            // HAP - com estoque
            ['polo_id' => $hap->id,  'nome' => 'ALMOXARIFADO',            'estoque' => true,  'status' => 'A', 'tipo' => 'Medicamento'],
            ['polo_id' => $hap->id,  'nome' => 'UTI',                     'estoque' => true,  'status' => 'A', 'tipo' => 'Medicamento'],
            // HAP - sem estoque
            ['polo_id' => $hap->id,  'nome' => 'SETOR DE INTERNAÇÃO',     'estoque' => false, 'status' => 'A', 'tipo' => 'Medicamento'],

            // HCS - com estoque
            ['polo_id' => $hcs->id,  'nome' => 'ALMOXARIFADO',            'estoque' => true,  'status' => 'A', 'tipo' => 'Medicamento'],
            // HCS - sem estoque
            ['polo_id' => $hcs->id,  'nome' => 'CLÍNICA MÉDICA',          'estoque' => false, 'status' => 'A', 'tipo' => 'Medicamento'],
            ['polo_id' => $hcs->id,  'nome' => 'CLÍNICA CIRÚRGICA',       'estoque' => false, 'status' => 'A', 'tipo' => 'Medicamento'],

            // UPA - com estoque
            ['polo_id' => $upa->id,  'nome' => 'ALMOXARIFADO',            'estoque' => true,  'status' => 'A', 'tipo' => 'Medicamento'],
            // UPA - sem estoque
            ['polo_id' => $upa->id,  'nome' => 'ÁREA DE ATENDIMENTO',     'estoque' => false, 'status' => 'A', 'tipo' => 'Medicamento'],
            ['polo_id' => $upa->id,  'nome' => 'POSTO DE ENFERMAGEM',     'estoque' => false, 'status' => 'A', 'tipo' => 'Medicamento'],
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
        $setor = fn($nome, $poloId) => DB::table('setores')
            ->where('nome', $nome)
            ->where('polo_id', $poloId)
            ->first();

        $farmaciaCentral = $setor('FARMÁCIA CENTRAL',        $hgvc->id);
        $farmDisp        = $setor('FARMÁCIA DE DISPENSAÇÃO', $hgvc->id);
        $satEmerg        = $setor('SATÉLITE DA EMERGÊNCIA',  $hgvc->id);
        $centroCirc      = $setor('CENTRO CIRÚRGICO',        $hgvc->id);
        $clinicaMedHGVC  = $setor('CLÍNICA MÉDICA',          $hgvc->id);
        $emergencia      = $setor('EMERGÊNCIA',              $hgvc->id);

        $almoxHAP        = $setor('ALMOXARIFADO',            $hap->id);
        $utiHAP          = $setor('UTI',                     $hap->id);
        $internacaoHAP   = $setor('SETOR DE INTERNAÇÃO',     $hap->id);

        $almoxHCS        = $setor('ALMOXARIFADO',            $hcs->id);
        $clinicaMedHCS   = $setor('CLÍNICA MÉDICA',          $hcs->id);
        $clinicaCirHCS   = $setor('CLÍNICA CIRÚRGICA',       $hcs->id);

        $almoxUPA        = $setor('ALMOXARIFADO',            $upa->id);
        $atendimentoUPA  = $setor('ÁREA DE ATENDIMENTO',     $upa->id);
        $postoUPA        = $setor('POSTO DE ENFERMAGEM',     $upa->id);

        $relacoes = [
            // HGVC
            [$farmDisp,       $farmaciaCentral],   
            [$satEmerg,       $farmaciaCentral],   
            [$centroCirc,     $farmDisp],           
            [$clinicaMedHGVC, $farmDisp],           
            [$emergencia,     $satEmerg],           

            // HAP
            [$almoxHAP,       $farmaciaCentral],   
            [$utiHAP,         $almoxHAP],           
            [$internacaoHAP,  $almoxHAP],           

            // HCS
            [$almoxHCS,       $farmaciaCentral],   
            [$clinicaMedHCS,  $almoxHCS],           
            [$clinicaCirHCS,  $almoxHCS],           

            // UPA
            [$almoxUPA,       $farmaciaCentral],   
            [$atendimentoUPA, $almoxUPA],           
            [$postoUPA,       $almoxUPA],           
        ];

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
    }
}
