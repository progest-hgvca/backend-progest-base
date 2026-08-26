<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminInicialSeeder extends Seeder
{
    /**
     * Cria o usuário adminti (super admin do sistema)
     */
    public function run()
    {
        $now = Carbon::now();

        // 1. Criar Usuário Admin TI
        DB::table('users')->updateOrInsert(
            ['email' => 'adminti@gmail.com'],
            [
                'name'            => 'ADMIN TI',
                'password'        => Hash::make('adminti'),
                'cpf'             => '00000000000',
                'telefone'        => '00000000000',
                'data_nascimento' => '1990-01-01',
                'status'          => 'A',
                'regime_contratacao_id' => 1,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]
        );

        $admin = DB::table('users')->where('email', 'adminti@gmail.com')->first();
        if (!$admin) return;

        // 2. Tentar encontrar o Polo HGVC e Setor TI para vincular o adminti
        $polo = DB::table('polos')->where('sigla', 'HGVC')->first();
        if ($polo) {
            DB::table('usuario_polo')->updateOrInsert(
                ['usuario_id' => $admin->id, 'polo_id' => $polo->id],
                ['created_at' => $now, 'updated_at' => $now]
            );

            $setor = DB::table('setores')->where('polo_id', $polo->id)->where('nome', 'TI')->first();
            if ($setor) {
                DB::table('usuario_setor')->updateOrInsert(
                    ['usuario_id' => $admin->id, 'setor_id' => $setor->id],
                    [
                        'perfil'     => 'admin', // O adminti tem acesso global de qualquer forma pelos guards
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }
}
