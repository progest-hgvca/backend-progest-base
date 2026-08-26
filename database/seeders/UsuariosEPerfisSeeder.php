<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsuariosEPerfisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $now = Carbon::now();

        // =====================================================================
        // 1. Criar Usuários
        // =====================================================================
        $usuarios = [
            [
                'name'            => 'Jean Solicitante',
                'email'           => 'jeansolicitante@gmail.com',
                'password'        => Hash::make('Admin123'),
                'cpf'             => '11111111111',
                'telefone'        => '00000000000',
                'data_nascimento' => '1990-01-01',
                'status'          => 'A',
                'regime_contratacao_id' => 1,
            ],
            [
                'name'            => 'Arthur Almoxarife',
                'email'           => 'arthuralmoxarife@gmail.com',
                'password'        => Hash::make('Admin123'),
                'cpf'             => '22222222222',
                'telefone'        => '00000000000',
                'data_nascimento' => '1990-01-01',
                'status'          => 'A',
                'regime_contratacao_id' => 1,
            ],
            [
                'name'            => 'Pablo Admin',
                'email'           => 'pabloadmin@gmail.com',
                'password'        => Hash::make('Admin123'),
                'cpf'             => '33333333333',
                'telefone'        => '00000000000',
                'data_nascimento' => '1990-01-01',
                'status'          => 'A',
                'regime_contratacao_id' => 1,
            ],
        ];

        foreach ($usuarios as $dados) {
            DB::table('users')->updateOrInsert(
                ['email' => $dados['email']],
                array_merge($dados, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }

        // =====================================================================
        // 2. Vincular Perfis aos Setores
        // =====================================================================
        $userSolicitante = DB::table('users')->where('email', 'jeansolicitante@gmail.com')->first();
        $userAlmoxarife  = DB::table('users')->where('email', 'arthuralmoxarife@gmail.com')->first();
        $userAdmin       = DB::table('users')->where('email', 'pabloadmin@gmail.com')->first();

        $setores = DB::table('setores')->get();

        if ($setores->isEmpty()) {
            $this->command->error('Nenhum setor encontrado. Execute PolosESetoresSeeder primeiro.');
            return;
        }

        // A pedido, os usuários fakes (inclusive no modo Demo) devem ter acesso
        // a todos os setores para facilitar as demonstrações e testes livres.
        $vinculos = [
            ['usuario_id' => $userSolicitante->id, 'perfil' => 'solicitante'],
            ['usuario_id' => $userAlmoxarife->id,  'perfil' => 'almoxarife'],
            ['usuario_id' => $userAdmin->id,       'perfil' => 'admin'],
        ];

        foreach ($vinculos as $vinculo) {
            foreach ($setores as $setor) {
                // Almoxarifes e farmacêuticos só devem ter acesso a setores que gerenciam estoque.
                if (!$setor->estoque && $vinculo['perfil'] === 'almoxarife') {
                    continue;
                }

                DB::table('usuario_setor')->updateOrInsert(
                    [
                        'usuario_id' => $vinculo['usuario_id'],
                        'setor_id'   => $setor->id,
                    ],
                    [
                        'perfil'     => $vinculo['perfil'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }
}
