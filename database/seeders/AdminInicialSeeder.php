<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminInicialSeeder extends Seeder
{
    /**
     * Cria um setor inicial (FARMÁCIA CENTRAL) e vincula o usuário admin a ele,
     * permitindo o primeiro acesso ao sistema em um banco recém-criado.
     *
     * A partir desse setor/polo, novos setores podem ser criados pela própria
     * aplicação.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        // 1. Criar Usuário Admin
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@admin.com'],
            [
                'name'            => 'ADMIN',
                'password'        => Hash::make('admin'), // senha é 'admin'
                'cpf'             => '00000000000',
                'telefone'        => '00000000000',
                'data_nascimento' => '1990-01-01',
                'status'          => 'A',
                'regime_contratacao_id'    => 1,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]
        );

        // 2. Garante um polo padrão
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

        // 3. Cria o setor FARMÁCIA CENTRAL (com estoque, tipo Medicamento).
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

        // 4. Vincula o usuário admin (admin@admin.com) ao setor como 'admin'.
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
}
