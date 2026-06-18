<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Cria um setor inicial (FARMÁCIA CENTRAL) e vincula o usuário admin a ele,
 * permitindo o primeiro acesso ao sistema em um banco recém-criado.
 *
 * A partir desse setor/polo, novos setores podem ser criados pela própria
 * aplicação. A migration é idempotente (updateOrInsert), então pode rodar
 * novamente sem gerar duplicatas.
 */
class SeedFarmaciaCentralAndAdminLink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = now();

        // 1. Garante um polo padrão (a tabela 'unidades' foi renomeada para 'polos').
        DB::table('polos')->updateOrInsert(
            ['nome' => 'Hospital Geral'],
            [
                'sigla'      => 'HGVC',
                'status'     => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $polo = DB::table('polos')->where('nome', 'Hospital Geral')->first();
        if (!$polo) {
            return;
        }

        // 2. Cria o setor FARMÁCIA CENTRAL (com estoque, tipo Medicamento).
        DB::table('setores')->updateOrInsert(
            ['polo_id' => $polo->id, 'nome' => 'FARMÁCIA CENTRAL'],
            [
                'descricao'  => 'Setor raiz de medicamentos (CAF)',
                'estoque'    => true,          // estoque = 1
                'tipo'       => 'Medicamento',
                'status'     => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $setor = DB::table('setores')
            ->where('polo_id', $polo->id)
            ->where('nome', 'FARMÁCIA CENTRAL')
            ->first();

        // 3. Vincula o usuário admin (admin@admin.com) ao setor como 'admin'.
        $admin = DB::table('users')->where('email', 'admin@admin.com')->first();
        if ($admin && $setor) {
            DB::table('usuario_setor')->updateOrInsert(
                ['usuario_id' => $admin->id, 'setor_id' => $setor->id],
                [
                    'perfil'     => 'admin',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $polo = DB::table('polos')->where('nome', 'Hospital Geral')->first();
        if (!$polo) {
            return;
        }

        $setor = DB::table('setores')
            ->where('polo_id', $polo->id)
            ->where('nome', 'FARMÁCIA CENTRAL')
            ->first();

        if ($setor) {
            // Remove o vínculo do admin e o setor (não remove o polo nem o usuário
            // admin para preservar outros possíveis vínculos).
            DB::table('usuario_setor')->where('setor_id', $setor->id)->delete();
            DB::table('setores')->where('id', $setor->id)->delete();
        }
    }
}
