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
     *
     * Cria os usuários de teste do sistema (incluindo o admin padrão)
     * e os vincula aos setores.
     */
    public function run()
    {
        $now = Carbon::now();

        // =====================================================================
        // 1. Criar Usuários
        // =====================================================================
        $usuarios = [
            // ID = 1 - Admin
            [
                'name'            => 'ADMIN',
                'email'           => 'admin@admin.com',
                'password'        => Hash::make('admin'), // senha é 'admin'
                'cpf'             => '00000000000',
                'telefone'        => '00000000000',
                'data_nascimento' => '1990-01-01',
                'status'          => 'A',
                'regime_contratacao_id'    => 1,
            ],
            // ID = 2 - Solicitante
            [
                'name'            => 'Jean Solicitante',
                'email'           => 'jeansolicitante@gmail.com',
                'password'        => Hash::make('Admin123'),
                'cpf'             => '11111111111',
                'telefone'        => '00000000000',
                'data_nascimento' => '1990-01-01',
                'status'          => 'A',
                'regime_contratacao_id'    => 1,
            ],
            // ID = 3 - Almoxarife
            [
                'name'            => 'Arthur Almoxarife',
                'email'           => 'arthuralmoxarife@gmail.com',
                'password'        => Hash::make('Admin123'),
                'cpf'             => '22222222222',
                'telefone'        => '00000000000',
                'data_nascimento' => '1990-01-01',
                'status'          => 'A',
                'regime_contratacao_id'    => 1,
            ],
            // ID = 4 - Admin 2
            [
                'name'            => 'Pablo Admin',
                'email'           => 'pabloadmin@gmail.com',
                'password'        => Hash::make('Admin123'),
                'cpf'             => '33333333333',
                'telefone'        => '00000000000',
                'data_nascimento' => '1990-01-01',
                'status'          => 'A',
                'regime_contratacao_id'    => 1,
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

        // O admin@admin.com é mapeado para ser Admin global em DatabaseSeeder,
        // mas também precisa do vínculo no setor que for usar. Como esse Seeder
        // preenche TODOS os setores, vincularemos o 'admin@admin.com' também.
        $userAdminPrincipal = DB::table('users')->where('email', 'admin@admin.com')->first();

        $setores = DB::table('setores')->get();

        if ($setores->isEmpty()) {
            $this->command->error('Nenhum setor encontrado. Execute PolosESetoresSeeder primeiro.');
            return;
        }

        $vinculos = [
            ['usuario_id' => $userSolicitante->id,     'perfil' => 'solicitante'],
            ['usuario_id' => $userAlmoxarife->id,      'perfil' => 'almoxarife'],
            ['usuario_id' => $userAdmin->id,           'perfil' => 'admin'],
            ['usuario_id' => $userAdminPrincipal->id,  'perfil' => 'admin'],
        ];

        foreach ($vinculos as $vinculo) {
            foreach ($setores as $setor) {
                $hasDistribuidor = DB::table('setor_distribuidor')->where('setor_solicitante_id', $setor->id)->exists();
                if (!$hasDistribuidor && $vinculo['perfil'] === 'solicitante') {
                    continue;
                }

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
