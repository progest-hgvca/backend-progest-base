<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Seeder OFICIAL e ESTÁTICO do Catálogo de Produtos.
 * Gerado automaticamente a partir da planilha "Lista cadastro PRODUTOS.xlsx".
 *
 * Vantagens:
 * 1. Não precisa de biblioteca Excel na AWS nem parse pesado em runtime.
 * 2. Executa em menos de 1 segundo consumindo quase zero de memória RAM.
 * 3. Totalmente seguro contra falhas de Out-Of-Memory (OOM) em servidores menores (t2/t3.micro).
 */
class CatalogoProdutosOficialSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $this->command->info('🚀 Inserindo Catálogo Oficial de Produtos (Seeder estático pré-processado)...');

        // 1. Grupos de Produtos
        $grupos = array (
  0 => 
  array (
    'nome' => 'MEDICAMENTOS',
    'tipo' => 'Medicamento',
    'controlado' => false,
  ),
  1 => 
  array (
    'nome' => 'INJETÁVEIS',
    'tipo' => 'Medicamento',
    'controlado' => false,
  ),
  2 => 
  array (
    'nome' => 'GERAL',
    'tipo' => 'Material',
    'controlado' => false,
  ),
  3 => 
  array (
    'nome' => 'MEDICAMENTOS CONTROLADOS',
    'tipo' => 'Medicamento',
    'controlado' => true,
  ),
  4 => 
  array (
    'nome' => 'ANTIBIÓTICOS',
    'tipo' => 'Medicamento',
    'controlado' => false,
  ),
  5 => 
  array (
    'nome' => 'SOLUÇÕES E SOROS',
    'tipo' => 'Medicamento',
    'controlado' => false,
  ),
  6 => 
  array (
    'nome' => 'MATERIAL DE LIMPEZA E HIGIENE',
    'tipo' => 'Material',
    'controlado' => false,
  ),
  7 => 
  array (
    'nome' => 'VITAMINAS E SUPLEMENTOS',
    'tipo' => 'Medicamento',
    'controlado' => false,
  ),
  8 => 
  array (
    'nome' => 'MATERIAL DE EXPEDIENTE',
    'tipo' => 'Material',
    'controlado' => false,
  ),
);

        $grupoIds = [];
        foreach ($grupos as $g) {
            DB::table('grupo_produto')->updateOrInsert(
                ['nome' => $g['nome']],
                [
                    'tipo'       => $g['tipo'],
                    'controlado' => $g['controlado'] ?? false,
                    'status'     => 'A',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
            $grupoIds[$g['nome']] = DB::table('grupo_produto')->where('nome', $g['nome'])->value('id');
        }

        // 2. Unidades de Medida
        $unidades = array (
  0 => 
  array (
    'nome' => 'COMP',
  ),
  1 => 
  array (
    'nome' => 'ENV',
  ),
  2 => 
  array (
    'nome' => 'UNIDADE',
  ),
  3 => 
  array (
    'nome' => 'AMP',
  ),
  4 => 
  array (
    'nome' => 'FA',
  ),
  5 => 
  array (
    'nome' => 'GL',
  ),
  6 => 
  array (
    'nome' => 'TB',
  ),
  7 => 
  array (
    'nome' => 'SER',
  ),
  8 => 
  array (
    'nome' => 'CAPS',
  ),
  9 => 
  array (
    'nome' => 'L',
  ),
  10 => 
  array (
    'nome' => 'RL',
  ),
);

        $unidadeIds = [];
        foreach ($unidades as $u) {
            DB::table('unidade_medida')->updateOrInsert(
                ['nome' => $u['nome']],
                [
                    'quantidade_unidade_minima' => 1,
                    'status'                    => 'A',
                    'created_at'                => $now,
                    'updated_at'                => $now,
                ]
            );
            $unidadeIds[$u['nome']] = DB::table('unidade_medida')->where('nome', $u['nome'])->value('id');
        }

        // 3. Produtos (em transação em lote)
        $produtos = array (
  0 => 
  array (
    'codigo' => '650219000024465',
    'nome' => 'ACETAZOLAMIDA, comprimido 250 mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  1 => 
  array (
    'codigo' => '650219000970123',
    'nome' => 'ACETILCISTEINA, 600 mg, envelope com 5 g',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'ENV',
  ),
  2 => 
  array (
    'codigo' => '650219000024473',
    'nome' => 'ACICLOVIR, em comprimido, 200 mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  3 => 
  array (
    'codigo' => '650219000024481',
    'nome' => 'ACICLOVIR, em po para injecao 250 mg (R).',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  4 => 
  array (
    'codigo' => '650219001145010',
    'nome' => 'ÁCIDO VALPROICO COMPRIMIDO 500 mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  5 => 
  array (
    'codigo' => '650219000024503',
    'nome' => 'ACIDO, acetilsalicilico 100 mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  6 => 
  array (
    'codigo' => '650219000024520',
    'nome' => 'ACIDO, folico 5 mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  7 => 
  array (
    'codigo' => '650219000120456',
    'nome' => 'ACIDO, tranexamico 250 mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  8 => 
  array (
    'codigo' => '650219001149865',
    'nome' => 'ACIDO, tranexamico, injetavel, 250mg em ampola de 05 ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  9 => 
  array (
    'codigo' => '650219000024597',
    'nome' => 'ACIDO, valproico 50mg/mL, xarope com 100 mL',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  10 => 
  array (
    'codigo' => '650219001149881',
    'nome' => 'ADENOSINA, 6mg, ampola, 2 ml',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  11 => 
  array (
    'codigo' => '650219000024651',
    'nome' => 'AGUA, para injecao apirogenica, injetavel, frasco ampola 10 ml.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  12 => 
  array (
    'codigo' => '650219001135694',
    'nome' => 'AGUA, para injecao apirogenica, injetavel, frasco ampola 1000ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  13 => 
  array (
    'codigo' => '650219000696307',
    'nome' => 'AGUA, para injecao, 500mL, sistema fechado de transferencia bolsa/frasco.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'FA',
  ),
  14 => 
  array (
    'codigo' => '650219000997498',
    'nome' => 'AGUA, para injecao, apirogenica, sistema fechado de transferencia, frasco ou Bolsa com 100mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'FA',
  ),
  15 => 
  array (
    'codigo' => '650219000024660',
    'nome' => 'ALBENDAZOL, comprimido ou capsula 400 mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  16 => 
  array (
    'codigo' => '650219001003542',
    'nome' => 'ALBUMINA, humana 20%, solucao injetavel F.A ou Bolsa 50 mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  17 => 
  array (
    'codigo' => '650219001064770',
    'nome' => 'ALCOOL, etilico 70%, solucao antisseptica uso externo , frasco com 100mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  18 => 
  array (
    'codigo' => '650219000999342',
    'nome' => 'ALCOOL, etilico 70%, solucao antisseptica uso externo, frasco com 1 litro.',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'FA',
  ),
  19 => 
  array (
    'codigo' => '650219000080411',
    'nome' => 'ALFENTANILA, cloridrato de solucao injetavel 0,544 mg/mL( 0,5mg/ml de alfentanila base ) amp. 5mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'AMP',
  ),
  20 => 
  array (
    'codigo' => '650219001148346',
    'nome' => 'ALPROSTADIL 500mcg/mL, solucao injetavel, ampola com 1mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  21 => 
  array (
    'codigo' => '650219001263242',
    'nome' => 'ALTEPLASE 10mg, po liofilizado para solucao injetavel, frasco-ampola + diluente 10ml, frasco-ampola.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  22 => 
  array (
    'codigo' => '650219001182501',
    'nome' => 'ALTEPLASE 20mg, po liofilizado para solucao injetavel, frasco-ampola + diluente 20ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  23 => 
  array (
    'codigo' => '650219000996173',
    'nome' => 'ALTEPLASE po liofilizado, injetavel, frasco ampola 50mg + frasco-ampola com 50mL do diluente.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  24 => 
  array (
    'codigo' => '650219000141305',
    'nome' => 'AMANTADINA cloridrato, de 100 mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  25 => 
  array (
    'codigo' => '650219000032069',
    'nome' => 'AMBROXOL, solucao oral (xarope) 6mg/mL fr. com 120mL',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  26 => 
  array (
    'codigo' => '650219001145746',
    'nome' => 'AMICACINA, sulfato, 250 mg/mL, solucao injetavel, ampola, 2 mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  27 => 
  array (
    'codigo' => '650219001178008',
    'nome' => 'AMINOACIDOS, 100mg/ml (10%), solucao para infusao parenteral, uso pediatrico, frasco com 100ml',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  28 => 
  array (
    'codigo' => '650219000024813',
    'nome' => 'AMINOFILINA, solucao injetavel 24 mg/mL, 10 ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  29 => 
  array (
    'codigo' => '650219000024830',
    'nome' => 'AMIODARONA, amiodarona 200 mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  30 => 
  array (
    'codigo' => '650219001145860',
    'nome' => 'AMIODARONA, cloridrato, 50 mg/mL, solucao injetavel, ampola, 3 mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  31 => 
  array (
    'codigo' => '650219000024848',
    'nome' => 'AMITRIPTILINA, comprimido 25 mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  32 => 
  array (
    'codigo' => '650219001146009',
    'nome' => 'AMOXICILINA, + acido clavulanico (1000mg + 200mg) sol. injetÿvel + diluente',
    'marca' => 'Diversas',
    'grupo' => 'ANTIBIÓTICOS',
    'unidade' => 'UNIDADE',
  ),
  33 => 
  array (
    'codigo' => '650219001146033',
    'nome' => 'AMOXICILINA, + Clavulonato de potassio 250 + 62,5mg/5mL, suspensao oral 75 mL',
    'marca' => 'Diversas',
    'grupo' => 'ANTIBIÓTICOS',
    'unidade' => 'UNIDADE',
  ),
  34 => 
  array (
    'codigo' => '650219000032077',
    'nome' => 'AMOXICILINA, p/ para suspensao oral 250mg/5mL fr. com 60mL',
    'marca' => 'Diversas',
    'grupo' => 'ANTIBIÓTICOS',
    'unidade' => 'UNIDADE',
  ),
  35 => 
  array (
    'codigo' => '650219000202142',
    'nome' => 'AMPICILINA, sodica + sulbactan sodica ( 2, 0 g + 1, 0 g ), po para solucao injetavel IM/IV.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  36 => 
  array (
    'codigo' => '650219001148761',
    'nome' => 'AMPICILINA, sodica 1g. Injetável',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  37 => 
  array (
    'codigo' => '652019000064483',
    'nome' => 'ANESTESICO uso odontologico, injetavel com vaso-constrictor-Cloridrato de prilocaina a 3% com felipressina 0,03 U.I./ml.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  38 => 
  array (
    'codigo' => '650219001146602',
    'nome' => 'ANFOTERICINA, B, solucao injetavel 50 mg FA(R)',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  39 => 
  array (
    'codigo' => '650219001146203',
    'nome' => 'ANLODIPINO, bensilato, comprimido 5mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  40 => 
  array (
    'codigo' => '650219001024744',
    'nome' => 'ANLODIPINO, bensilato, comprimido 10mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  41 => 
  array (
    'codigo' => '650219000701378',
    'nome' => 'ARIPIPRAZOL, 10mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  42 => 
  array (
    'codigo' => '650219001219847',
    'nome' => 'ARTICAINA cloridrato 72mg + Epinefrina 18mcg, solucao injetavel, tubete com 1,8ml.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  43 => 
  array (
    'codigo' => '650219001116576',
    'nome' => 'ATENOLOL, 25mg comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  44 => 
  array (
    'codigo' => '650219000981281',
    'nome' => 'ATENOLOL, comprimido 50mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  45 => 
  array (
    'codigo' => '650219000090913',
    'nome' => 'ATORVASTATINA 20mg, comprimido revestido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  46 => 
  array (
    'codigo' => '650219001099663',
    'nome' => 'ATORVASTATINA calcica 40mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  47 => 
  array (
    'codigo' => '650219001146335',
    'nome' => 'ATRACURIUM, dobesilato, injetável, 2,5 mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  48 => 
  array (
    'codigo' => '650219001231847',
    'nome' => 'ATROPINA sulfato, 10mg/ml (1%), solucao oftalmica, frasco com 5ml.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  49 => 
  array (
    'codigo' => '650219000024988',
    'nome' => 'ATROPINA, sulfato 0,25 mg/mL, injetável, 1 mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  50 => 
  array (
    'codigo' => '650219000997510',
    'nome' => 'AZITROMICINA, 40mg/mL, po para suspensao oral, frasco com 15 mL.',
    'marca' => 'Diversas',
    'grupo' => 'ANTIBIÓTICOS',
    'unidade' => 'FA',
  ),
  51 => 
  array (
    'codigo' => '650219001031163',
    'nome' => 'AZITROMICINA, comprimido ou capsula 500mg.',
    'marca' => 'Diversas',
    'grupo' => 'ANTIBIÓTICOS',
    'unidade' => 'COMP',
  ),
  52 => 
  array (
    'codigo' => '650219001191810',
    'nome' => 'AZUL, patente V, sal sodico, 25mg/ml (2,5%), ampola 2ml.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  53 => 
  array (
    'codigo' => '650219000025097',
    'nome' => 'BACLOFENO, comprimido 10mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  54 => 
  array (
    'codigo' => '650219001189220',
    'nome' => 'BENZILPENICILINA, benzatina 1.200.000 UI',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  55 => 
  array (
    'codigo' => '650219000025151',
    'nome' => 'BENZILPENICILINA, cristalina potássica 5.000.000 UI',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  56 => 
  array (
    'codigo' => '650219000025259',
    'nome' => 'BICARBONATO, de sodio 8,4% 10 mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  57 => 
  array (
    'codigo' => '650219000025267',
    'nome' => 'BICARBONATO, de sodio 8,4% 250 mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  58 => 
  array (
    'codigo' => '650219001154206',
    'nome' => 'BIPERIDENO, cloridrato, comprimido 2 mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  59 => 
  array (
    'codigo' => '650219000025291',
    'nome' => 'BIPERIDENO, lactato, solucao injetavel 5 mg/mL ampola 1 mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  60 => 
  array (
    'codigo' => '650219000061336',
    'nome' => 'BISACODIL, 5mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  61 => 
  array (
    'codigo' => '894019001188658',
    'nome' => 'Nutrição parenteral 1440 Kcal 1875 mL',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  62 => 
  array (
    'codigo' => '894019001188682',
    'nome' => 'Nutrição parenteral 1900 Kcal 2053 mL',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  63 => 
  array (
    'codigo' => '650219000025348',
    'nome' => 'BROMOPRIDA solucao injetavel 5mg/mL ampola 2 mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  64 => 
  array (
    'codigo' => '650219001150359',
    'nome' => 'BUPIVACAINA, 0,5% + epinefrina, solucao injetavel 5 mg/ml fr 20 mL Com Vaso',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'FA',
  ),
  65 => 
  array (
    'codigo' => '650219001147285',
    'nome' => 'BUPIVACAINA, 0,5% hiperbarica + glicose 8% (Pesada)',
    'marca' => 'Diversas',
    'grupo' => 'SOLUÇÕES E SOROS',
    'unidade' => 'GL',
  ),
  66 => 
  array (
    'codigo' => '650219001147293',
    'nome' => 'BUPIVACAINA, 0,5%, solucao injetavel 5 mg/ml.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  67 => 
  array (
    'codigo' => '650219001168894',
    'nome' => 'BUPIVACAINA, cloridrato 5 mg/mL (0,50%), solucao injetavel, ampola, 4 mL. ISOBARICA',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  68 => 
  array (
    'codigo' => '650219000025461',
    'nome' => 'CAPTOPRIL, 25 mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  69 => 
  array (
    'codigo' => '650219000025470',
    'nome' => 'CARBAMAZEPINA, comprimido 200 mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  70 => 
  array (
    'codigo' => '650219001282581',
    'nome' => 'CARBAMAZEPINA, xarope 100mg/5ml. Emabalgem: frasco com 100ml',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  71 => 
  array (
    'codigo' => '650219000025518',
    'nome' => 'CARBONATO, de litio, 300 mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  72 => 
  array (
    'codigo' => '650219001153757',
    'nome' => 'CARVAO, ativado, po  30g',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  73 => 
  array (
    'codigo' => '650219001150430',
    'nome' => 'CARVEDILOL 25 mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  74 => 
  array (
    'codigo' => '650219001150421',
    'nome' => 'CARVEDILOL, 12,5 mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  75 => 
  array (
    'codigo' => '650219000963020',
    'nome' => 'CARVEDILOL, 3,125 mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  76 => 
  array (
    'codigo' => '650219000957615',
    'nome' => 'CARVEDILOL, 6,25 mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  77 => 
  array (
    'codigo' => '650219001145380',
    'nome' => 'CEFALEXINA, 50 mg/mL, po para suspensao oral, frasco, 60 mL',
    'marca' => 'Diversas',
    'grupo' => 'ANTIBIÓTICOS',
    'unidade' => 'FA',
  ),
  78 => 
  array (
    'codigo' => '650219001150456',
    'nome' => 'CEFALEXINA, capsula ou dragea 500 mg.',
    'marca' => 'Diversas',
    'grupo' => 'ANTIBIÓTICOS',
    'unidade' => 'COMP',
  ),
  79 => 
  array (
    'codigo' => '650219000999563',
    'nome' => 'CEFAZOLINA, po, para solucao injetavel 1 g IM /IV.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  80 => 
  array (
    'codigo' => '650219001074156',
    'nome' => 'CEFEPIME, 2,0g em sistema fechado de transferência',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  81 => 
  array (
    'codigo' => '650219001218204',
    'nome' => 'CEFTAZIDIMA 2.000 mg + avibactam 500 mg, po p/ sol. p/ infusao, frasco ampola',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  82 => 
  array (
    'codigo' => '650219001161539',
    'nome' => 'CEFTRIAXONA, sodica 1g.',
    'marca' => 'Diversas',
    'grupo' => 'ANTIBIÓTICOS',
    'unidade' => 'UNIDADE',
  ),
  83 => 
  array (
    'codigo' => '650219000036595',
    'nome' => 'CETOCONAZOL, 20mg/g creme tubo 30g',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'TB',
  ),
  84 => 
  array (
    'codigo' => '650219001144499',
    'nome' => 'CETOPROFENO 100 mg, po liofilizado para solucao injetavel, frasco ou ampola - Intravenoso',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  85 => 
  array (
    'codigo' => '650219001057944',
    'nome' => 'CETOPROFENO, comprimido 100 mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  86 => 
  array (
    'codigo' => '650219001144502',
    'nome' => 'CETOPROFENO, solucao injetavel 100 mg I.M. ampola 2 mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  87 => 
  array (
    'codigo' => '650219001144529',
    'nome' => 'CICLOFOSFAMIDA, ciclofosfamida 1g.',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  88 => 
  array (
    'codigo' => '650219000196673',
    'nome' => 'CILOSTAZOL, 100 mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  89 => 
  array (
    'codigo' => '650219000979996',
    'nome' => 'CIPROFLOXACINO, cloridrato de, solucao injetavel 2mg/mL 200 mL, frasco ampola ou bolsa em sistema fechado.',
    'marca' => 'Diversas',
    'grupo' => 'ANTIBIÓTICOS',
    'unidade' => 'AMP',
  ),
  90 => 
  array (
    'codigo' => '650219000026085',
    'nome' => 'CIPROFLOXACINO, comprimido 500mg',
    'marca' => 'Diversas',
    'grupo' => 'ANTIBIÓTICOS',
    'unidade' => 'COMP',
  ),
  91 => 
  array (
    'codigo' => '650219000051063',
    'nome' => 'CISATRACURIO, 2mg/mL solucao injetavel (2,68mg de besilato de cisatracurio) ampola 5mL (R)',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  92 => 
  array (
    'codigo' => '650219001206583',
    'nome' => 'CISATRACURIO, besilato, 2mg/ml, solucao injetavel, ampola com 10ml.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  93 => 
  array (
    'codigo' => '650219000036455',
    'nome' => 'CLINDAMICINA, 600mg sol. injetavel 150mg/mL ampola ou F.A. 4mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  94 => 
  array (
    'codigo' => '650219001025872',
    'nome' => 'CLOBAZAM, 10 mg comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  95 => 
  array (
    'codigo' => '650219001143530',
    'nome' => 'CLONAZEPAM, 2,5 mg/ml, em gotas',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'UNIDADE',
  ),
  96 => 
  array (
    'codigo' => '650219001027026',
    'nome' => 'CLONAZEPAM, comprimido 0,5 mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'COMP',
  ),
  97 => 
  array (
    'codigo' => '650219001054740',
    'nome' => 'CLONAZEPAM, comprimido 2 mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'COMP',
  ),
  98 => 
  array (
    'codigo' => '650219000046981',
    'nome' => 'CLONIDINA, cloridrato de, 0,100mg comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  99 => 
  array (
    'codigo' => '650219000046604',
    'nome' => 'CLONIDINA, cloridrato de, 0,150mg sol. Injetavel, 1 mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  100 => 
  array (
    'codigo' => '650219001162012',
    'nome' => 'CLONIDINA, cloridrato de, 0,150mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  101 => 
  array (
    'codigo' => '650219000053791',
    'nome' => 'CLONIDINA, cloridrato de, 0,200mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  102 => 
  array (
    'codigo' => '650219000155560',
    'nome' => 'CLOPIDOGREL, bissulfato de 75 mg, de clopidogrel base, comprimido revestido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  103 => 
  array (
    'codigo' => '650219000120340',
    'nome' => 'CLORETO, de potassio, solucao injetavel a 19,1% ampola 10mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  104 => 
  array (
    'codigo' => '650219000074306',
    'nome' => 'CLORETO, de potassio, xarope 60 mg/ml',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  105 => 
  array (
    'codigo' => '650219000696285',
    'nome' => 'CLORETO, de sodio, 0,9%, 250mL, sistema fechado de transferencia frasco/bolsa.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  106 => 
  array (
    'codigo' => '650219000182915',
    'nome' => 'CLORETO, de sodio, solucao injetavel 0,9%, sistema fechado de transferencia, 1.000 mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  107 => 
  array (
    'codigo' => '650219000997528',
    'nome' => 'CLORETO, de sodio, solucao injetavel 0,9%, sistema fechado de transferencia, frasco ou bolsa 100 mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'FA',
  ),
  108 => 
  array (
    'codigo' => '650219000074357',
    'nome' => 'CLORETO, de sodio, solucao injetavel 20%, 10mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  109 => 
  array (
    'codigo' => '650219000111775',
    'nome' => 'CLORETO, de sodio, solucao injetavel a 0,9% 10mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  110 => 
  array (
    'codigo' => '650219000696277',
    'nome' => 'CLORETO, e sodio, 0,9%, 500mL, sistema fechado de transferencia frasco/bolsa.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  111 => 
  array (
    'codigo' => '650219001093410',
    'nome' => 'CLOREXIDINA solucao aquosa 1%, frasco com 1000ml .',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  112 => 
  array (
    'codigo' => '650219001093436',
    'nome' => 'CLOREXIDINA solucao degermante a 2%. frasco com 100 ml.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  113 => 
  array (
    'codigo' => '650219001082159',
    'nome' => 'CLOREXIDINA,, solucao aquosa a 1%, frasco com 100 ml .',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  114 => 
  array (
    'codigo' => '650219000074403',
    'nome' => 'CLORHEXIDINA, (digluconato), solucao alcoolica a 0,5% 1 L',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  115 => 
  array (
    'codigo' => '650219001066080',
    'nome' => 'CLORHEXIDINA, (digluconato), solucao alcoolica a 0,5%, frasco com 100 ml .',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  116 => 
  array (
    'codigo' => '650219000074411',
    'nome' => 'CLORHEXIDINA, (digluconato), solucao degermante a 2%. 1 L',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  117 => 
  array (
    'codigo' => '650219001217860',
    'nome' => 'CLORHEXIDINA, solucao aquosa 0,12%, uso odontologico, frasco com 250mL. ORAL',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  118 => 
  array (
    'codigo' => '650219000026476',
    'nome' => 'CLORPROMAZINA, 100 mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  119 => 
  array (
    'codigo' => '650219000026484',
    'nome' => 'CLORPROMAZINA, comprimido 25 mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  120 => 
  array (
    'codigo' => '650219000026506',
    'nome' => 'CLORPROMAZINA, solucao injetavel 5 mg/ml, 1 mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  121 => 
  array (
    'codigo' => '650219000026492',
    'nome' => 'CLORPROMAZINA, solucao oral 40 mg/ml',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  122 => 
  array (
    'codigo' => '650219000975478',
    'nome' => 'Clozapina 100 mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  123 => 
  array (
    'codigo' => '650219000695394',
    'nome' => 'CLOZAPINA 25 mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  124 => 
  array (
    'codigo' => '650219000026549',
    'nome' => 'CODEINA, + paracetamol',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  125 => 
  array (
    'codigo' => '650219000975370',
    'nome' => 'CODEINA, comprimido 30 mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  126 => 
  array (
    'codigo' => '650219001185390',
    'nome' => 'CONTRASTE, radiologico iodado, nao ionico, baixa osmolaridade, equivalente a 300mg/ml de iodo, solucao injetavel, frasco-ampola com 100ml.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  127 => 
  array (
    'codigo' => '650219001185403',
    'nome' => 'CONTRASTE, radiologico iodado, nao ionico, baixa osmolaridade, equivalente a 300mg/ml de iodo, solucao injetavel, frasco-ampola com 50ml.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  128 => 
  array (
    'codigo' => '650219001004220',
    'nome' => 'DANTROLENO, sodico 20 mg, ampola.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  129 => 
  array (
    'codigo' => '650219001256530',
    'nome' => 'DAPAGLIFLOZINA 10 mg , comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  130 => 
  array (
    'codigo' => '650219000026808',
    'nome' => 'DESLANOSIDEO, solucao injetavel 0,4 mg, ampola 2 mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  131 => 
  array (
    'codigo' => '650219000166103',
    'nome' => 'DESMOPRESSINA, acetato de 0,1mg/mL spray nasal. Frasco com 25 doses de 10 mcg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  132 => 
  array (
    'codigo' => '650219000026859',
    'nome' => 'DEXAMETASONA acetato de, creme 0,1% tb. 10g',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'TB',
  ),
  133 => 
  array (
    'codigo' => '650219000026883',
    'nome' => 'DEXAMETASONA, comprimido, 4mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  134 => 
  array (
    'codigo' => '650219001167618',
    'nome' => 'DEXAMETASONA, fosfato di-sodico 4mg/mL, injetável, 2,5 ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  135 => 
  array (
    'codigo' => '650219000026921',
    'nome' => 'DEXCLORFENIRAMINA, comprimido 2 mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  136 => 
  array (
    'codigo' => '650219000026913',
    'nome' => 'DEXCLORFENIRAMINA, solucao oral 0,4 mg/mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  137 => 
  array (
    'codigo' => '650219000099295',
    'nome' => 'DEXMEDETOMIDINA, cloridrato, de 118mcg/mL (100mcg de dexmedetomidina base) solucao injetavel.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  138 => 
  array (
    'codigo' => '650219001213776',
    'nome' => 'DEXTRANO, + hipromelose + benzalconio, clor (COLIRIO LUBRIFICANTE)',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  139 => 
  array (
    'codigo' => '650219000026972',
    'nome' => 'DIAZEPAM, comprimido 10 mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'COMP',
  ),
  140 => 
  array (
    'codigo' => '650219000026980',
    'nome' => 'DIAZEPAM, comprimido 5 mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'COMP',
  ),
  141 => 
  array (
    'codigo' => '650219000026999',
    'nome' => 'DIAZEPAM, solucao injetavel 5 mg/mL ampola 2mL (R)',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'AMP',
  ),
  142 => 
  array (
    'codigo' => '650219001049518',
    'nome' => 'DIFENIDRAMINA, cloridrato de, solucao injetavel 50 mg/ml,  1 mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  143 => 
  array (
    'codigo' => '650219000027090',
    'nome' => 'DIGOXINA, 0,25 mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  144 => 
  array (
    'codigo' => '650219000091880',
    'nome' => 'DIMETICONA, 75mg/mL emulsao oral, frasco 10mL',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  145 => 
  array (
    'codigo' => '650219000027120',
    'nome' => 'DIMETICONA, comprimido 40mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  146 => 
  array (
    'codigo' => '650219001155555',
    'nome' => 'DIPIRONA, 500mg/mL + escopolamina, butilbrometo 4mg/mL, solucao injetavel, ampola com 5mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  147 => 
  array (
    'codigo' => '650219000027154',
    'nome' => 'DIPIRONA, sodica 500 mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  148 => 
  array (
    'codigo' => '650219000027162',
    'nome' => 'DIPIRONA, sodica 500 mg/ml ampola 2mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  149 => 
  array (
    'codigo' => '650219000027170',
    'nome' => 'DIPIRONA, sodica 500 mg/mL, Gotas',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  150 => 
  array (
    'codigo' => '650219000027189',
    'nome' => 'DOBUTAMINA, cloridrato de',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  151 => 
  array (
    'codigo' => '650219000027197',
    'nome' => 'DOMPERIDONA, domperidona 1 mg/mL, suspensão oral',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  152 => 
  array (
    'codigo' => '650219000027219',
    'nome' => 'DOPAMINA, cloridrato',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  153 => 
  array (
    'codigo' => '650219001134299',
    'nome' => 'DROPERIDOL 2,5mg/ml injetavel, frasco ou ampola com 1ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  154 => 
  array (
    'codigo' => '664019001248332',
    'nome' => 'EMBALAGEM para unitarizacao de Blister cortado, medindo 60mm x 100mm sem tarja de classificacao de risco, frente cristal transparente.',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  155 => 
  array (
    'codigo' => '664019001248308',
    'nome' => 'EMBALAGEM para unitarizacao de Blister cortado, medindo 60mm x 60mm sem tarja de classificacao de risco, frente cristal transparente',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  156 => 
  array (
    'codigo' => '664019001248316',
    'nome' => 'EMBALAGEM para unitarizacao de Blister cortado, medindo 70mm x 130mm sem tarja de classificacao de risco, frente cristal transparente.',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  157 => 
  array (
    'codigo' => '650219000027812',
    'nome' => 'ENALAPRIL, maleato de, 10 mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  158 => 
  array (
    'codigo' => '650219000980242',
    'nome' => 'ENALAPRIL, maleato de, 20 mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  159 => 
  array (
    'codigo' => '650219000100137',
    'nome' => 'ENALAPRIL, maleato de, 5 mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  160 => 
  array (
    'codigo' => '650219001251953',
    'nome' => 'ENOXAPARINA, sodica, 20mg, solucao injetavel para uso subcutaneo, seringa preenchida com 0,2ml.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'SER',
  ),
  161 => 
  array (
    'codigo' => '650219001251961',
    'nome' => 'ENOXAPARINA, sodica, 40mg, solucao injetavel para uso subcutaneo, seringa preenchida com 0,4ml.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'SER',
  ),
  162 => 
  array (
    'codigo' => '650219001251970',
    'nome' => 'ENOXAPARINA, sodica, 60mg, solucao injetavel para uso subcutaneo, seringa preenchida com 0,6ml.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'SER',
  ),
  163 => 
  array (
    'codigo' => '650219001251988',
    'nome' => 'ENOXAPARINA, sodica, 80mg, solucao injetavel para uso subcutaneo, seringa preenchida com 0,8ml.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'SER',
  ),
  164 => 
  array (
    'codigo' => '650219000027863',
    'nome' => 'EPINEFRINA, injetável, 1 ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  165 => 
  array (
    'codigo' => '650219000993891',
    'nome' => 'ERITROPOETINA, humana, recombinante 4.000UI, solucao injetavel FA ou po liofilizado + diluente.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'FA',
  ),
  166 => 
  array (
    'codigo' => '650219001245490',
    'nome' => 'ESCETAMINA cloridrato de, solucao injetavel 50 mg/ml ampola 10 mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  167 => 
  array (
    'codigo' => '650219001245503',
    'nome' => 'ESCETAMINA cloridrato de, solucao injetavel 50 mg/ml ampola 2 mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  168 => 
  array (
    'codigo' => '650219000036048',
    'nome' => 'ESCINA, amorfa + escina polissulfonada sodica + salicilato de dietilamina (0,01g+0,01g+0,05g)g gel tubo 30g REPARIL GEL',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'TB',
  ),
  169 => 
  array (
    'codigo' => '650219000690520',
    'nome' => 'ESCITALOPRAM, 10 mg comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  170 => 
  array (
    'codigo' => '650219001147242',
    'nome' => 'ESCOPOLAMINA, butilbrometo 10 mg + dipirona 250 mg, comprimido revestido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  171 => 
  array (
    'codigo' => '650219001149660',
    'nome' => 'ESCOPOLAMINA, butilbrometo 10mg dragea.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  172 => 
  array (
    'codigo' => '650219001149687',
    'nome' => 'ESCOPOLAMINA, butilbrometo 20mg/mL, solucao injetavel, ampola com 1mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  173 => 
  array (
    'codigo' => '650219000077879',
    'nome' => 'ESPIRONOLACTONA, 25mg comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  174 => 
  array (
    'codigo' => '650219000991546',
    'nome' => 'ESTERES, etilico dos acidos graxos iodados do oleo da papoula, ampola 10mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  175 => 
  array (
    'codigo' => '650219000028053',
    'nome' => 'ETILEFRINA, cloridrato, injetável, 10mg/ml, 1 ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  176 => 
  array (
    'codigo' => '650219000028088',
    'nome' => 'ETOMIDATO, solucao injetavel 2mg/mL ampola 10mL ( R )',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  177 => 
  array (
    'codigo' => '650219001154222',
    'nome' => 'FENITOINA, fenitoina 100mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  178 => 
  array (
    'codigo' => '650219000028142',
    'nome' => 'FENITOINA, fenitoina 50mg/mL, 5 ml',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  179 => 
  array (
    'codigo' => '650219001156233',
    'nome' => 'FENOBARBITAL, fenobarbital 100mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'COMP',
  ),
  180 => 
  array (
    'codigo' => '650219000028177',
    'nome' => 'FENOBARBITAL, fenobarbital 40 mg/mL, solução oral gotas, 20 ml',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'UNIDADE',
  ),
  181 => 
  array (
    'codigo' => '650219001156250',
    'nome' => 'FENOBARBITAL, sodico, injetável , 2 ml',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'UNIDADE',
  ),
  182 => 
  array (
    'codigo' => '650219001059343',
    'nome' => 'FENOTEROL, bromidrato, solucao oral 5mg/mL frasco 20mL',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  183 => 
  array (
    'codigo' => '650219000046620',
    'nome' => 'Fentanila 5mL',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'UNIDADE',
  ),
  184 => 
  array (
    'codigo' => '650219000028207',
    'nome' => 'FENTANILA, +droperidol, injetável, 2 ml',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'UNIDADE',
  ),
  185 => 
  array (
    'codigo' => '650219001156268',
    'nome' => 'FENTANILA, citrato de, injetavel 0,05mg/mL, ampola 2mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'AMP',
  ),
  186 => 
  array (
    'codigo' => '650219001155024',
    'nome' => 'FENTANILA, citrato de, solucao injetavel 78,5 mcg/ml., frasco ampola c/ 10 ml.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'AMP',
  ),
  187 => 
  array (
    'codigo' => '650219001259202',
    'nome' => 'FERRO, III, coloidal de sacarato de hidroxido de ferro I.V.,100mg, ampola de 5 ml',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  188 => 
  array (
    'codigo' => '650219001161555',
    'nome' => 'FILGRASTIM, 300mcg, solucao injetavel, frasco-ampola ou seringa.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  189 => 
  array (
    'codigo' => '664019001013351',
    'nome' => 'FITA, teste, indicada para verificacao da concentracao do ortoftaldeido, desinfetante em alto nivel.',
    'marca' => 'Diversas',
    'grupo' => 'MATERIAL DE LIMPEZA E HIGIENE',
    'unidade' => 'UNIDADE',
  ),
  190 => 
  array (
    'codigo' => '650219000977780',
    'nome' => 'FITOMENADIONA, (vitamina K1), solucao injetavel, intramuscular, 10mg/mL ampola 1mL',
    'marca' => 'Diversas',
    'grupo' => 'VITAMINAS E SUPLEMENTOS',
    'unidade' => 'AMP',
  ),
  191 => 
  array (
    'codigo' => '650219000086720',
    'nome' => 'FLUCONAZOL, capsula de 150 mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'CAPS',
  ),
  192 => 
  array (
    'codigo' => '650219000980129',
    'nome' => 'FLUCONAZOL, solucao injetavel 2mg/mL 100 mL, frasco ampola ou bolsa em sistema fechado.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  193 => 
  array (
    'codigo' => '650219000028290',
    'nome' => 'FLUFENAZINA, enantato ou decanoato, injetavel 25mg/mL ampola 1mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  194 => 
  array (
    'codigo' => '650219000028312',
    'nome' => 'FLUMAZENIL, injetável, 5 ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  195 => 
  array (
    'codigo' => '650219001162560',
    'nome' => 'FLUOXETINA, fluoxetina, 20 mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  196 => 
  array (
    'codigo' => '681000000412813',
    'nome' => 'FORMOL, a 10%, embalagem de 1 litro',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'L',
  ),
  197 => 
  array (
    'codigo' => '650219000981907',
    'nome' => 'FOSFATO, de sodio ( monobasico 160 mg/mL e dibasico 60mg/mL ), enema solucao frasco de 130ml,',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  198 => 
  array (
    'codigo' => '650219001156640',
    'nome' => 'FRACAO, fosfolipidica, de pulmao porcino (alfaporactano) 80mg/ml suspensao injetavel.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  199 => 
  array (
    'codigo' => '650219001154770',
    'nome' => 'FRACAO, fosfolipidica, de pulmao porcino (surfactante pulmonar) 120mg/1,5mL suspensao esteril',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  200 => 
  array (
    'codigo' => '650219000028401',
    'nome' => 'FUROSEMIDA, comprimido 40mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  201 => 
  array (
    'codigo' => '650219000028398',
    'nome' => 'FUROSEMIDA, solucao injetavel 10mg/ml, 2 ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  202 => 
  array (
    'codigo' => '650219000949809',
    'nome' => 'GABAPENTINA, 300 mg, capsula.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'CAPS',
  ),
  203 => 
  array (
    'codigo' => '650219001069888',
    'nome' => 'GANCICLOVIR, 250mg bolsa em sistema fechado.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  204 => 
  array (
    'codigo' => '650219001149130',
    'nome' => 'GENTAMICINA, sulfato 80mg, injetável, 2 ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  205 => 
  array (
    'codigo' => '650219000028460',
    'nome' => 'GLIBENCLAMIDA, comprimido 5 mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  206 => 
  array (
    'codigo' => '650219000028479',
    'nome' => 'GLICERINA, glicerina 120mg/mL. 1l',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'GL',
  ),
  207 => 
  array (
    'codigo' => '681019001261088',
    'nome' => 'GLICINA, 98%(base livre), 33gramas, neutralizante para solucao de ortoftaldeido.',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  208 => 
  array (
    'codigo' => '650219000696170',
    'nome' => 'GLICOSE, 5% 500mL sistema fechado de transferencia frasco/bolsa.',
    'marca' => 'Diversas',
    'grupo' => 'SOLUÇÕES E SOROS',
    'unidade' => 'FA',
  ),
  209 => 
  array (
    'codigo' => '650219000991716',
    'nome' => 'GLICOSE, 5%, 100mL, sistema fechado de transferencia, frasco/bolsa.',
    'marca' => 'Diversas',
    'grupo' => 'SOLUÇÕES E SOROS',
    'unidade' => 'FA',
  ),
  210 => 
  array (
    'codigo' => '650219000696188',
    'nome' => 'GLICOSE, 5%, 250 mL, sistema fechado de transferência Frasco/bolsa',
    'marca' => 'Diversas',
    'grupo' => 'SOLUÇÕES E SOROS',
    'unidade' => 'FA',
  ),
  211 => 
  array (
    'codigo' => '650219001039610',
    'nome' => 'GLICOSE, solucao injetavel 10%, 500mL, sistema fechado de transferencia.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  212 => 
  array (
    'codigo' => '650219000028525',
    'nome' => 'GLICOSE, solucao injetavel 25%, 10mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  213 => 
  array (
    'codigo' => '650219000028568',
    'nome' => 'GLICOSE, solucao injetavel 50% 10mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  214 => 
  array (
    'codigo' => '650219000028584',
    'nome' => 'GLUCONATO, de calcio 10%',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  215 => 
  array (
    'codigo' => '650219000027588',
    'nome' => 'HALOPERIDOL, comprimido, 1mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  216 => 
  array (
    'codigo' => '650219000028630',
    'nome' => 'HALOPERIDOL, comprimido, 5mg. (Item de RP)',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  217 => 
  array (
    'codigo' => '650219000027570',
    'nome' => 'HALOPERIDOL, decanoato, injetável, 1 ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  218 => 
  array (
    'codigo' => '650219000028649',
    'nome' => 'HALOPERIDOL, solucao injetavel 5mg/ml, ampola 1ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  219 => 
  array (
    'codigo' => '650219000028622',
    'nome' => 'HALOPERIDOL, solucao oral 2mg/ml, frasco 20ml. (Item de RP)',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  220 => 
  array (
    'codigo' => '650219000028665',
    'nome' => 'HEPARINA, sodica 5.000 UI/0,25mL. SC',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  221 => 
  array (
    'codigo' => '650219000991554',
    'nome' => 'HEPARINA, sodica, solucao injetavel 5.000 UI/mL F.A. 5mL, para profilaxia de tromboses arteriovenosas e de embolia pulmonar. IV',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  222 => 
  array (
    'codigo' => '650219000046639',
    'nome' => 'HIDRALAZINA, 50mg comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  223 => 
  array (
    'codigo' => '650219000027553',
    'nome' => 'HIDRALAZINA, cloridrato 20mg/mL, injetável, 1 ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  224 => 
  array (
    'codigo' => '650219000027596',
    'nome' => 'HIDRALAZINA, cloridrato 25mg comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  225 => 
  array (
    'codigo' => '650219000179787',
    'nome' => 'HIDROCLOROTIAZIDA, 25 mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  226 => 
  array (
    'codigo' => '650219001137310',
    'nome' => 'HIDROCORTISONA, succinato sodico, 100 mg, Injetavel',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  227 => 
  array (
    'codigo' => '650219001137301',
    'nome' => 'HIDROCORTISONA, succinato sodico, 500 mg, Injetavel.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  228 => 
  array (
    'codigo' => '650219001149571',
    'nome' => 'HIDROXIDO, de aluminio. Solução oral',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  229 => 
  array (
    'codigo' => '650219000033448',
    'nome' => 'HIDROXIUREIA capsula 500mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'CAPS',
  ),
  230 => 
  array (
    'codigo' => '650219000980471',
    'nome' => 'IBUPROFENO, 50mg/mL, suspensao oral 30mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  231 => 
  array (
    'codigo' => '650219001250310',
    'nome' => 'IMUNOGLOBULINA humana 10g, solucao injetavel, frasco-ampola.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  232 => 
  array (
    'codigo' => '650219001250329',
    'nome' => 'IMUNOGLOBULINA humana 2,5g, solucao injetavel, frasco-ampola.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  233 => 
  array (
    'codigo' => '650219001141325',
    'nome' => 'IMUNOGLOBULINA humana 5g, solucao injetavel, frasco-ampola.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  234 => 
  array (
    'codigo' => '650219000167371',
    'nome' => 'INSULINA aspart solucao injetavel 100 UI/mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  235 => 
  array (
    'codigo' => '650219001006185',
    'nome' => 'INSULINA glulisina 100 UI/mL, solucao injetavel,',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'GL',
  ),
  236 => 
  array (
    'codigo' => '650219000154377',
    'nome' => 'INSULINA lispro derivada de ADN recombinante contendo 100 unidades (U-100) por mL em',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  237 => 
  array (
    'codigo' => '650219000028797',
    'nome' => 'INSULINA, humana nph',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  238 => 
  array (
    'codigo' => '650219000028800',
    'nome' => 'INSULINA, humana regular, solucao injetavel 100 UI/mL ( R ) fr. com 10mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  239 => 
  array (
    'codigo' => '650219000074381',
    'nome' => 'IODOPOVIDONA, solucao alcoolica 10mg/ml em iodo Tintura',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  240 => 
  array (
    'codigo' => '650219001058096',
    'nome' => 'IODOPOVIDONA, solucao alcoolica 10mg/ml em iodo, embalagem com 100ml.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  241 => 
  array (
    'codigo' => '650219000074420',
    'nome' => 'IODOPOVIDONA, solucao aquosa 10mg/ml em iodo',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  242 => 
  array (
    'codigo' => '650219001058100',
    'nome' => 'IODOPOVIDONA, solucao aquosa 10mg/ml em iodo, embalagem almotolia com 100ml.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  243 => 
  array (
    'codigo' => '650219000074390',
    'nome' => 'IODOPOVIDONA, solucao degermante 10 mg/ml em iodo',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  244 => 
  array (
    'codigo' => '650219001058118',
    'nome' => 'IODOPOVIDONA, solucao degermante 10mg/ml em iodo, embalagem almotolia com 100ml.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  245 => 
  array (
    'codigo' => '650219001266187',
    'nome' => 'IPRATROPIO, brometo 0,025%',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  246 => 
  array (
    'codigo' => '650219000028967',
    'nome' => 'ISOFLURANO, iquido inalacao ( R )',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  247 => 
  array (
    'codigo' => '650219000029009',
    'nome' => 'ISOSSORBIDA, dinitrato 10mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  248 => 
  array (
    'codigo' => '650219000029017',
    'nome' => 'ISOSSORBIDA, dinitrato 5mg. Sublingual',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  249 => 
  array (
    'codigo' => '650219000220990',
    'nome' => 'ISOSSORBIDA, mononitrato, comprimido 20mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  250 => 
  array (
    'codigo' => '650219000084441',
    'nome' => 'IVERMECTINA, 6mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  251 => 
  array (
    'codigo' => '650219000029025',
    'nome' => 'LACTULOSE, solucao oral 667mg/ml, frasco com 120ml.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  252 => 
  array (
    'codigo' => '650219000695351',
    'nome' => 'LAMOTRIGINA, 100mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  253 => 
  array (
    'codigo' => '650219001027069',
    'nome' => 'LAMOTRIGINA, 25mg, comprimido,',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  254 => 
  array (
    'codigo' => '650219001065777',
    'nome' => 'LAMOTRIGINA, 50mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  255 => 
  array (
    'codigo' => '650219001148524',
    'nome' => 'LEVETIRACETAM 250 mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  256 => 
  array (
    'codigo' => '650219001148966',
    'nome' => 'LEVOBUPIVACAINA cloridrato de, 5mg/mL + bitartarato de epinefrina 9,1mcg/ml, solucao injetavel, frasco-ampola com 20mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  257 => 
  array (
    'codigo' => '650219000109584',
    'nome' => 'LEVOBUPIVACAINA, cloridrato de, a 0,5% com excesso de enantiomerico de 50% sem vaso constrictor solucao injetavel',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  258 => 
  array (
    'codigo' => '650219000131237',
    'nome' => 'LEVOFLOXACINO, 500 mg comprimido revestido',
    'marca' => 'Diversas',
    'grupo' => 'ANTIBIÓTICOS',
    'unidade' => 'COMP',
  ),
  259 => 
  array (
    'codigo' => '650219001148974',
    'nome' => 'LEVOFLOXACINO, 500mg, solucao injetavel, frasco ampola ou bolsa em sistema fechado.',
    'marca' => 'Diversas',
    'grupo' => 'ANTIBIÓTICOS',
    'unidade' => 'AMP',
  ),
  260 => 
  array (
    'codigo' => '650219000029092',
    'nome' => 'LEVOMEPROMAZINA, comprimido 25mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  261 => 
  array (
    'codigo' => '650219000047791',
    'nome' => 'LEVOMEPROMAZINA, solucao oral a 4% frasco com 20mL',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  262 => 
  array (
    'codigo' => '650219000029130',
    'nome' => 'LEVOTIROXINA, comprimido 25mcg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  263 => 
  array (
    'codigo' => '650219000979643',
    'nome' => 'LEVOTIROXINA, comprimido 50mcg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  264 => 
  array (
    'codigo' => '650219001148532',
    'nome' => 'LIDOCAINA, 10% Spray',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  265 => 
  array (
    'codigo' => '650219000029157',
    'nome' => 'LIDOCAINA, cloridrato 2% 20mg/g - Geléia',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  266 => 
  array (
    'codigo' => '650219000029165',
    'nome' => 'LIDOCAINA, cloridrato 2% 20mg/mL - sem vaso (com 5 mL)',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  267 => 
  array (
    'codigo' => '650219001148400',
    'nome' => 'LIDOCAINA, cloridrato 20mg/mL (2%) + epinefrina 0,005mg/mL (1:200.000), solucao injetavel, frasco-ampola com 20ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  268 => 
  array (
    'codigo' => '650219000029190',
    'nome' => 'LIDOCAINA, cloridrato de, solucao injetavel 20mg/mL F.A. 20mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  269 => 
  array (
    'codigo' => '650219000155276',
    'nome' => 'LINEZOLIDA, solucao para infusao a 2 mg/mL bolsa de 300 mL',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  270 => 
  array (
    'codigo' => '650219000084883',
    'nome' => 'LOPERAMIDA, 2mg comprimidos',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  271 => 
  array (
    'codigo' => '650219000117315',
    'nome' => 'LORATADINA, 10 mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  272 => 
  array (
    'codigo' => '650219001015184',
    'nome' => 'LORAZEPAM, 2 mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  273 => 
  array (
    'codigo' => '650219001031660',
    'nome' => 'LOSARTANA, potassico, 50mg, comprimido revestido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  274 => 
  array (
    'codigo' => '650219000958514',
    'nome' => 'MANITOL, 20%, solucao injetavel 200 mg/mL F.A./bolsa 250mL em sistema fechado.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  275 => 
  array (
    'codigo' => '650219000172200',
    'nome' => 'MEROPENEM, triidratada 1.140 mg ( equivalente anidro 1g)',
    'marca' => 'Diversas',
    'grupo' => 'ANTIBIÓTICOS',
    'unidade' => 'UNIDADE',
  ),
  276 => 
  array (
    'codigo' => '650219000975400',
    'nome' => 'METADONA, comprimido 10 mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'COMP',
  ),
  277 => 
  array (
    'codigo' => '650219000975419',
    'nome' => 'METADONA, comprimido 5 mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'COMP',
  ),
  278 => 
  array (
    'codigo' => '650219001015192',
    'nome' => 'METARAMINOL, bitartarato de, 10mg/mL solucao injetavel, ampola com 1mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  279 => 
  array (
    'codigo' => '650219000706604',
    'nome' => 'METFORMINA, cloridrato de, 500mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  280 => 
  array (
    'codigo' => '650219001052330',
    'nome' => 'METFORMINA, cloridrato, comprimido, 850mg,',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  281 => 
  array (
    'codigo' => '650219000029610',
    'nome' => 'METILDOPA, metildopa 250mg (Item de RP)',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  282 => 
  array (
    'codigo' => '650219001250175',
    'nome' => 'METILPREDNISOLONA 125 mg, po para solucao injetavel, frasco-ampola + diluente. A embalagem deve apresentar a frase: venda proibida pelo comercio.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  283 => 
  array (
    'codigo' => '650219000029661',
    'nome' => 'METILPREDNISOLONA, metilprednisolona 500mg FA',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  284 => 
  array (
    'codigo' => '650219000029670',
    'nome' => 'METOCLOPRAMIDA, comprimido 10mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  285 => 
  array (
    'codigo' => '650219000029696',
    'nome' => 'METOCLOPRAMIDA, solucao injetavel 5 mg/mL ampola 2 mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  286 => 
  array (
    'codigo' => '650219001261169',
    'nome' => 'METOPROLOL succinato, 50 mg, comprimido ou capsula de liberacao controlada.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  287 => 
  array (
    'codigo' => '650219001156659',
    'nome' => 'METOPROLOL, 5 mg tartarato, injetável, 5 ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'RL',
  ),
  288 => 
  array (
    'codigo' => '650219000690260',
    'nome' => 'METOPROLOL, succinato 50mg comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  289 => 
  array (
    'codigo' => '650219001261150',
    'nome' => 'METOPROLOL, succinato, 25 mg, comprimido ou capsula de liberacao controlada.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  290 => 
  array (
    'codigo' => '650219000967912',
    'nome' => 'METOPROLOL, succinato, 25mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  291 => 
  array (
    'codigo' => '650219001156667',
    'nome' => 'METRONIDAZOL, 500mg solucao injetavel 100mL, frasco ampola ou bolsa em sistema fechado.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  292 => 
  array (
    'codigo' => '650219000029750',
    'nome' => 'METRONIDAZOL, comprimido 250mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  293 => 
  array (
    'codigo' => '650219000200379',
    'nome' => 'METRONIDAZOL, geleia ou creme vaginal, 100mg/g, tubo com 50 gr.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'TB',
  ),
  294 => 
  array (
    'codigo' => '650219001074199',
    'nome' => 'MICAFUNGINA, 100mg, frasco-ampola.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  295 => 
  array (
    'codigo' => '650219001074202',
    'nome' => 'MICAFUNGINA, 50mg, frasco-ampola.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  296 => 
  array (
    'codigo' => '650219000969435',
    'nome' => 'MICONAZOL, nitrato, creme vaginal, a 2%, embalagem com bisnaga de 80g + aplicador ginecologico',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  297 => 
  array (
    'codigo' => '650219001156675',
    'nome' => 'MIDAZOLAN, 50mg solucao injetavel 10ml, na embalagem deve conter a descricao "venda proibida pelo comercio"',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  298 => 
  array (
    'codigo' => '650219001156683',
    'nome' => 'MIDAZOLAN, midazolan 15mg/3mL',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  299 => 
  array (
    'codigo' => '650219001019457',
    'nome' => 'MILRINONA, 1mg por mL, ampola 10 mL, solucao injetavel',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  300 => 
  array (
    'codigo' => '650219000029831',
    'nome' => 'MIRTAZAPINA, comprimido 30mg ( R )',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  301 => 
  array (
    'codigo' => '650219001053027',
    'nome' => 'MORFINA, sulfato de, 0,1mg/mL, solucao injetavel 1mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'UNIDADE',
  ),
  302 => 
  array (
    'codigo' => '650219000078352',
    'nome' => 'MORFINA, sulfato de, 0,2mg/mL, solucao injetavel 1mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'UNIDADE',
  ),
  303 => 
  array (
    'codigo' => '650219000975702',
    'nome' => 'MORFINA, sulfato de, acao lenta-prolongada, capsula 30mg.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'CAPS',
  ),
  304 => 
  array (
    'codigo' => '650219000975508',
    'nome' => 'MORFINA, sulfato de, comprimido 10 mg, acao curta.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'COMP',
  ),
  305 => 
  array (
    'codigo' => '650219000112054',
    'nome' => 'MORFINA, sulfato solucao injetavel 10mg/mL ampola de 1mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'AMP',
  ),
  306 => 
  array (
    'codigo' => '650219001262840',
    'nome' => 'MUPIROCINA, 2% creme bisnaga com 15 g com 20 mg/g',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  307 => 
  array (
    'codigo' => '650219000194735',
    'nome' => 'NALBUFINA cloridrato de 10 mg/mL, ampola de 1 mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  308 => 
  array (
    'codigo' => '650219000029262',
    'nome' => 'NALOXONA, solucao injetavel 0,4mg/ml, ampola 1ml.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  309 => 
  array (
    'codigo' => '650219000032247',
    'nome' => 'NEOMICINA, sulfato de + bacitracina (5mg + 250UI)/g pomada, tubo com 15g.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'TB',
  ),
  310 => 
  array (
    'codigo' => '650219000080519',
    'nome' => 'NEOSTIGMINA, metilsulfato, solucao injetavel 0,5mg ampola 1ml.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  311 => 
  array (
    'codigo' => '650219000029327',
    'nome' => 'NIFEDIPINA, microcristalizada (Retard), 20 mg, comprimido retard',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  312 => 
  array (
    'codigo' => '650219000078328',
    'nome' => 'NIMODIPINA, 30mg, comprimidos.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  313 => 
  array (
    'codigo' => '650219001153498',
    'nome' => 'NISTATINA, 100.000 UI/g + oxido de zinco 200 mg/g, pomada, bisnaga com 60g',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  314 => 
  array (
    'codigo' => '650219000029343',
    'nome' => 'NISTATINA, creme vaginal 25.000 UI/g, tubo com 60 gramas + aplicador.',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'TB',
  ),
  315 => 
  array (
    'codigo' => '650219000029335',
    'nome' => 'NISTATINA, suspensao oral 100.000 UI/ml, frasco com 50ml.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  316 => 
  array (
    'codigo' => '650219000078336',
    'nome' => 'NITROGLICERINA, 5mg/mL, solucao injetavel 10mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  317 => 
  array (
    'codigo' => '650219001238337',
    'nome' => 'NITROGLICERINA, 5mg/mL, solucao injetavel 5mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  318 => 
  array (
    'codigo' => '650219001152076',
    'nome' => 'NITROPRUSSIATO, de sodio, po liofilizado para infusao 25mg/mL, ampola 2mL + diluente a 5% de glic',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  319 => 
  array (
    'codigo' => '650219001152408',
    'nome' => 'NOREPINEFRINA, bitartarato de, 1mg(de norepinefrina base)/mL, ampola 4mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  320 => 
  array (
    'codigo' => '650219000165751',
    'nome' => 'OCTREOTIDA 0.1mg/mL injetavel.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  321 => 
  array (
    'codigo' => '650219000165794',
    'nome' => 'OLANZAPINA 5mg comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  322 => 
  array (
    'codigo' => '650219000165786',
    'nome' => 'OLANZAPINA, 10mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  323 => 
  array (
    'codigo' => '650219000032131',
    'nome' => 'OLEO, mineral, puro, liquido oral, frasco 100 ml',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  324 => 
  array (
    'codigo' => '650219000697095',
    'nome' => 'OMEPRAZOL 40 MG',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  325 => 
  array (
    'codigo' => '650219001182960',
    'nome' => 'OMEPRAZOL, omeprazol 20mg. Comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  326 => 
  array (
    'codigo' => '650219001152823',
    'nome' => 'OMEPRAZOL, omeprazol 40mg Injetável',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  327 => 
  array (
    'codigo' => '650219000695017',
    'nome' => 'ONDANSENTRONA, 8 mg, comprimido revestido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  328 => 
  array (
    'codigo' => '650219001152840',
    'nome' => 'ONDANSETRON, solucao injetavel 8 mg ampola 4 mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  329 => 
  array (
    'codigo' => '650219001152831',
    'nome' => 'ONDANSETRONA, cloridrato 2mg/mL, solucao injetavel, ampola com 2 mL(4mg).',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  330 => 
  array (
    'codigo' => '681019001243470',
    'nome' => 'ORTOFTALDEIDO, soluçao neutra, concentrada igual ou superior a 0,55%, com margem de desvio em torno de 10%,',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  331 => 
  array (
    'codigo' => '650219001154869',
    'nome' => 'OXACILINA, sodica, 500 mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  332 => 
  array (
    'codigo' => '650219000172154',
    'nome' => 'OXALIPLATINA 50 mg, po liofilizado para infusao venosa frasco ampola com tampa flip-off',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  333 => 
  array (
    'codigo' => '650219001000187',
    'nome' => 'OXCARBAMAZEPINA 60 mg/mL, suspensao oral, frasco 100mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  334 => 
  array (
    'codigo' => '650219000030058',
    'nome' => 'OXIDO, de zinco+vit. A + vit.D, pomada topica,tb. com 45g',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  335 => 
  array (
    'codigo' => '650219001163566',
    'nome' => 'PANCURONIO, pancuronio, injetável, 2 ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  336 => 
  array (
    'codigo' => '650219000149241',
    'nome' => 'PANTOPRAZOL 40 mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  337 => 
  array (
    'codigo' => '650219001212729',
    'nome' => 'PANTOPRAZOL, sodico, 40mg, po liofilizado para solucao injetavel, frasco-ampola.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  338 => 
  array (
    'codigo' => '650219001247085',
    'nome' => 'PARACETAMOL 10 mg/mL solucao para infusao, bolsa ou frasco 100 mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  339 => 
  array (
    'codigo' => '650219000030104',
    'nome' => 'PARACETAMOL, paracetamol 500mg, comp',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  340 => 
  array (
    'codigo' => '650219000030090',
    'nome' => 'PARACETAMOL, solucao oral, em gotas, 200 mg/ml, frasco 10 ml.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  341 => 
  array (
    'codigo' => '650219001155903',
    'nome' => 'PERMETRINA 5%',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  342 => 
  array (
    'codigo' => '650219000030198',
    'nome' => 'PEROXIDO, de hidrogenio, solucao topica 10 volumes',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  343 => 
  array (
    'codigo' => '650219001148800',
    'nome' => 'PIPERACILINA, 4g + tazobactam 0,5g po para solucao injetavel, frasco ampola.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  344 => 
  array (
    'codigo' => '650219001149008',
    'nome' => 'POLIESTIRENO, sulfonato de calcio po envelope com 30g ® SORCAL',
    'marca' => 'Diversas',
    'grupo' => 'MATERIAL DE EXPEDIENTE',
    'unidade' => 'ENV',
  ),
  345 => 
  array (
    'codigo' => '650219001153633',
    'nome' => 'POLIMIXINA, B sulfato, 1.000.000UI, po liofilizado, injetavel, frasco-ampola.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  346 => 
  array (
    'codigo' => '650219000182796',
    'nome' => 'POLIMIXINA, B, po liofilizado, para solucao injetavel 500.000 ui',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  347 => 
  array (
    'codigo' => '650219001144561',
    'nome' => 'POLIVITAMINICO, complexo B (cloridrato ou nitrato de tiamina(Vit. B1) 4 mg a 5mg + riboflavina(Vit.B2) 2mg + cloridrato de piridoxina(Vit. B6) 1mg a 2 mg + nicotinamida(Vit.B3 ou Vit.PP) 10mg a 20 mg + pantotenato de calcio(Vit. B5) 2mg a 4mg), dragea ou comprimido',
    'marca' => 'Diversas',
    'grupo' => 'VITAMINAS E SUPLEMENTOS',
    'unidade' => 'COMP',
  ),
  348 => 
  array (
    'codigo' => '650219001209183',
    'nome' => 'POLIVITAMINICO, vitamina A 3000UI, B1 2mg, B2 1,5mg, nicotinamida 15mg, B5 10mg, B6 2mg, biotina 0,2mg, C 80mg, D 900UI, E 15mg, por mL, frasco com 20mL.',
    'marca' => 'Diversas',
    'grupo' => 'VITAMINAS E SUPLEMENTOS',
    'unidade' => 'FA',
  ),
  349 => 
  array (
    'codigo' => '650219000135720',
    'nome' => 'PREDNISOLONA, fosfato sodico de (equivalente a 1 mg de prednisolona) 1,34 mg/mL solucao oral fraco com 100 mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  350 => 
  array (
    'codigo' => '650219001157949',
    'nome' => 'PREDNISONA, 20mg, comprimido ou capsula.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  351 => 
  array (
    'codigo' => '650219001157957',
    'nome' => 'PREDNISONA, 5mg comprimido ou capsula.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  352 => 
  array (
    'codigo' => '650219000030392',
    'nome' => 'PROMETAZINA, prometazina 25mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  353 => 
  array (
    'codigo' => '650219000030406',
    'nome' => 'PROMETAZINA, prometazina 25mg/ml, injetável, 2 ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  354 => 
  array (
    'codigo' => '650219000085006',
    'nome' => 'PROPATILNITRATO, 10mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  355 => 
  array (
    'codigo' => '650219000123935',
    'nome' => 'PROPOFOL, emulsao injetavel 10 mg/mL F.A. 100 mL (R)',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'UNIDADE',
  ),
  356 => 
  array (
    'codigo' => '650219001007084',
    'nome' => 'PROPOFOL, emulsao injetavel 10mg/mL Ampola ou F.A. 20mL ( R ).',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'AMP',
  ),
  357 => 
  array (
    'codigo' => '650219000030449',
    'nome' => 'PROPRANOLOL, comprimido 40mg',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  358 => 
  array (
    'codigo' => '650219000162957',
    'nome' => 'PROSTAGLANDINA, E, (Alprostadil) 20 mcg po liofilizado frasco ampola.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  359 => 
  array (
    'codigo' => '650219000975524',
    'nome' => 'PROTAMINA, cloridrato de, 1% solucao injetavel, ampola 5mL.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  360 => 
  array (
    'codigo' => '650219000165816',
    'nome' => 'QUETIAPINA, fumarato de 100 mg comprimidos.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  361 => 
  array (
    'codigo' => '650219000165832',
    'nome' => 'QUETIAPINA, fumarato de, 25 mg comprimidos.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  362 => 
  array (
    'codigo' => '650219000180670',
    'nome' => 'REMIFENTANILA, cloridrato, po liofilizado, injetavel, 2 mg, 2 ml',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'UNIDADE',
  ),
  363 => 
  array (
    'codigo' => '664019001171178',
    'nome' => 'RIBBOM, para uso em maquina unitarizadora de medicamentos',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  364 => 
  array (
    'codigo' => '650219001032925',
    'nome' => 'RISPERIDONA solucao oral 1mg/ml, frasco 30mL + seringa dosadora.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  365 => 
  array (
    'codigo' => '650219001032283',
    'nome' => 'RISPERIDONA, 1mg, comprimidos',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  366 => 
  array (
    'codigo' => '650219000207012',
    'nome' => 'RISPERIDONA, 2mg, comprimidos',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  367 => 
  array (
    'codigo' => '650219001038524',
    'nome' => 'RIVAROXABANA, 10mg comprimido revestido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  368 => 
  array (
    'codigo' => '650219001096800',
    'nome' => 'RIVAROXABANA, 15mg comprimido revestido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  369 => 
  array (
    'codigo' => '650219000099287',
    'nome' => 'ROCURONIO, brometo de, 10mg/mL solucao injetavel ampola 5mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  370 => 
  array (
    'codigo' => '650219001148559',
    'nome' => 'SACCHAROMYCES, boulardii 100mg liofilizado capsula.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'CAPS',
  ),
  371 => 
  array (
    'codigo' => '650219001206958',
    'nome' => 'SAIS, para reidratacao oral',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  372 => 
  array (
    'codigo' => '650219000950246',
    'nome' => 'SALBUTAMOL, 100mcg aerosol com 200 doses',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  373 => 
  array (
    'codigo' => '650219001074970',
    'nome' => 'SALBUTAMOL, 5mg/ml solucao para nebulizacao',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  374 => 
  array (
    'codigo' => '650219001146432',
    'nome' => 'SALBUTAMOL, sulfato 0,4mg/ml, xarope, frasco de 120ml',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  375 => 
  array (
    'codigo' => '650219001003003',
    'nome' => 'SERTRALINA, cloridrato, 50mg, comprimido revestido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  376 => 
  array (
    'codigo' => NULL,
    'nome' => 'SERVIÇO DE FORNECIMENTO DE MANIPULAÇÃO DE NUTRIÇÃO PARENTERAL (ADULTO)',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  377 => 
  array (
    'codigo' => NULL,
    'nome' => 'SERVIÇO DE FORNECIMENTO DE MANIPULAÇÃO DE NUTRIÇÃO PARENTERAL (NEO)',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  378 => 
  array (
    'codigo' => NULL,
    'nome' => 'SERVIÇO DE FORNECIMENTO DE MANIPULAÇÃO DE NUTRIÇÃO PARENTERAL (PED)',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  379 => 
  array (
    'codigo' => '650219000099236',
    'nome' => 'SEVOFLURANO, solucao inalatorio 100mL (anestesico).',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  380 => 
  array (
    'codigo' => '650219000133043',
    'nome' => 'SINVASTATINA, 20 mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  381 => 
  array (
    'codigo' => '650219000992534',
    'nome' => 'SOLUCAO, de cloreto de sodio, potassio e calcio+lactato de sodio (ringer com lactato).',
    'marca' => 'Diversas',
    'grupo' => 'SOLUÇÕES E SOROS',
    'unidade' => 'UNIDADE',
  ),
  382 => 
  array (
    'codigo' => '650219000984108',
    'nome' => 'SUFENTANILA, citrato, solucao injetavel 0,05mg/mL, ampola de 1mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'AMP',
  ),
  383 => 
  array (
    'codigo' => '650219001005049',
    'nome' => 'SUFENTANILA, citrato, solucao injetavel 5mcg/mL, ampola de 2mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'AMP',
  ),
  384 => 
  array (
    'codigo' => '650219001138928',
    'nome' => 'SUGAMADEX sodico, 100 mg/mL, frasco-ampola de 2 mL',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  385 => 
  array (
    'codigo' => '650219001146645',
    'nome' => 'SULFADIAZINA, de prata, pasta 1%, pote contendo 400g.',
    'marca' => 'Diversas',
    'grupo' => 'MATERIAL DE EXPEDIENTE',
    'unidade' => 'UNIDADE',
  ),
  386 => 
  array (
    'codigo' => '650219000036366',
    'nome' => 'SULFAMETOXAZOL, + trimetroprima (400mg+80mg) comprimido envelopado',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  387 => 
  array (
    'codigo' => '650219001146688',
    'nome' => 'SULFAMETOXAZOL, +trimetoprima 400mg+80mg IV, 5 ml',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  388 => 
  array (
    'codigo' => '650219001146670',
    'nome' => 'SULFAMETOXAZOL, +trimetoprima 40mg+8mg/ml SUSPENSÃO 50 mL',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  389 => 
  array (
    'codigo' => '650219000030937',
    'nome' => 'SULFATO, de bario',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  390 => 
  array (
    'codigo' => '650219000041335',
    'nome' => 'SULFATO, de magnesio 50% solucao injetavel ampola 10mL',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  391 => 
  array (
    'codigo' => '650219001270893',
    'nome' => 'SULFATO, de zinco 17,60 mg/mL, sol. oral, frasco com 100mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  392 => 
  array (
    'codigo' => '650219000030953',
    'nome' => 'SULFATO, ferroso 25mg/mL',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  393 => 
  array (
    'codigo' => '650219000030961',
    'nome' => 'SULFATO, ferroso 40mg, comp',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  394 => 
  array (
    'codigo' => '650219000046680',
    'nome' => 'SUXAMETONIO, injetavel 10mg/mL, frasco ampola 10mL (succinilcolina, cloreto)',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  395 => 
  array (
    'codigo' => '650219001148699',
    'nome' => 'TEICOPLANINA, 400mg, po liofilizado, injetavel, frasco-ampola.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  396 => 
  array (
    'codigo' => '650219000133868',
    'nome' => 'TENECTEPLASE, po liofilizado injetavel frasco ampola 10.000 U (40 mg) + diluente 10 mL em seringa pre-carregada.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  397 => 
  array (
    'codigo' => '650219001121766',
    'nome' => 'TENECTEPLASE, po liofilizado injetavel frasco ampola 10000 U (50 mg) + diluente 10 mL em seringa precarregada',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  398 => 
  array (
    'codigo' => '650219000690511',
    'nome' => 'TERLIPRESSINA, acetato de, 1mg po liofilo injetavel + solucao diluente com 5 ml',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  399 => 
  array (
    'codigo' => '650219000978213',
    'nome' => 'TIAMINA, 300mg, comprimido revestido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  400 => 
  array (
    'codigo' => '650219001145525',
    'nome' => 'TIAMINA, cloridrato 100mg/mL, ampola com 1mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  401 => 
  array (
    'codigo' => '650219000202177',
    'nome' => 'TIGECICLINA 50 mg, frasco ampola.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'AMP',
  ),
  402 => 
  array (
    'codigo' => '650219001157744',
    'nome' => 'TIOPENTAL, sodico',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'UNIDADE',
  ),
  403 => 
  array (
    'codigo' => '650519001191519',
    'nome' => 'TIRA, reagente, descartavel, para determinacao de glicemia capilar',
    'marca' => 'Diversas',
    'grupo' => 'GERAL',
    'unidade' => 'CAPS',
  ),
  404 => 
  array (
    'codigo' => '650219000031194',
    'nome' => 'TOBRAMICINA, solucao oftalmologica fr. com 5mL',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'UNIDADE',
  ),
  405 => 
  array (
    'codigo' => '650219000165956',
    'nome' => 'TOPIRAMATO, 50mg, comprimido.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  406 => 
  array (
    'codigo' => '650219001136836',
    'nome' => 'TOXINA, botulinica tipo A, 100 U, solucao injetavel,',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'UNIDADE',
  ),
  407 => 
  array (
    'codigo' => '650219001146300',
    'nome' => 'TRAMADOL, 50 mg cloridrato de capsula ou comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'COMP',
  ),
  408 => 
  array (
    'codigo' => '650219000031232',
    'nome' => 'TRAMADOL, tramadol 50mg/ml, injetável, 2 ml',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS CONTROLADOS',
    'unidade' => 'UNIDADE',
  ),
  409 => 
  array (
    'codigo' => '650219000100943',
    'nome' => 'URSODESOXICOLICO, acido, 150 mg comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  410 => 
  array (
    'codigo' => '650219000031550',
    'nome' => 'VANCOMICINA, cloridrato de, po para solucao injetavel 500 mg FA (R)',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'FA',
  ),
  411 => 
  array (
    'codigo' => '650219001167600',
    'nome' => 'VARFARINA, sodica 5mg comprimido ou capsula.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
  412 => 
  array (
    'codigo' => '650219001188593',
    'nome' => 'VASELINA, liquida esterelizada frasco 100 mL.',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'FA',
  ),
  413 => 
  array (
    'codigo' => '650219001016431',
    'nome' => 'VASOPRESSINA, 20 UI, ampola 1,0 mL, solucao injetavel.',
    'marca' => 'Diversas',
    'grupo' => 'INJETÁVEIS',
    'unidade' => 'AMP',
  ),
  414 => 
  array (
    'codigo' => '650219000031666',
    'nome' => 'VITAMINA, C , solucao injetavel, em ampola de 5ml.',
    'marca' => 'Diversas',
    'grupo' => 'VITAMINAS E SUPLEMENTOS',
    'unidade' => 'AMP',
  ),
  415 => 
  array (
    'codigo' => '650219000031682',
    'nome' => 'VITAMINA, Complexo B, solucao injetavel 2 ml.',
    'marca' => 'Diversas',
    'grupo' => 'VITAMINAS E SUPLEMENTOS',
    'unidade' => 'COMP',
  ),
  416 => 
  array (
    'codigo' => '650219001188100',
    'nome' => 'VORICONAZOL 50mg, comprimido',
    'marca' => 'Diversas',
    'grupo' => 'MEDICAMENTOS',
    'unidade' => 'COMP',
  ),
);

        $determinarPortaria = function ($nome, $grupo) {
            if ($grupo !== 'MEDICAMENTOS CONTROLADOS') {
                return null;
            }
            $n = mb_strtoupper($nome, 'UTF-8');
            if (str_contains($n, 'MORFINA') || str_contains($n, 'FENTANIL') || str_contains($n, 'PETIDINA') || str_contains($n, 'ALFENTANIL') || str_contains($n, 'SUFENTANIL')) {
                return 'A1';
            }
            if (str_contains($n, 'TRAMADOL')) {
                return 'A2';
            }
            if (str_contains($n, 'METILFENIDATO')) {
                return 'A3';
            }
            if (str_contains($n, 'CLONAZEPAM') || str_contains($n, 'DIAZEPAM') || str_contains($n, 'MIDAZOLAM') || str_contains($n, 'LORAZEPAM') || str_contains($n, 'ALPRAZOLAM') || str_contains($n, 'BROMAZEPAM') || str_contains($n, 'CLOBAZAM')) {
                return 'B1';
            }
            if (str_contains($n, 'SIBUTRAMINA')) {
                return 'B2';
            }
            if (str_contains($n, 'FENOBARBITAL') || str_contains($n, 'HALOPERIDOL') || str_contains($n, 'AMITRIPTILINA') || str_contains($n, 'CARBAMAZEPINA') || str_contains($n, 'VALPROAT') || str_contains($n, 'RISPERIDONA') || str_contains($n, 'BIPERIDENO')) {
                return 'C1';
            }
            return 'C1';
        };

        DB::transaction(function () use ($produtos, $grupoIds, $unidadeIds, $determinarPortaria, $now) {
            foreach ($produtos as $p) {
                $portaria = $determinarPortaria($p['nome'], $p['grupo']);
                DB::table('produtos')->updateOrInsert(
                    ['nome' => $p['nome']],
                    [
                        'codigo_simpas'    => $p['codigo'],
                        'marca'            => $p['marca'] ?? 'Diversas',
                        'grupo_produto_id' => $grupoIds[$p['grupo']] ?? null,
                        'unidade_medida_id'=> $unidadeIds[$p['unidade']] ?? null,
                        'lista_portaria'   => $portaria,
                        'status'           => 'A',
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ]
                );
            }
        });

        $this->command->info('✅ ' . count($produtos) . ' produtos oficiais do catálogo inseridos com sucesso!');
    }
}
