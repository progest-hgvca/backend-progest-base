<?php

return [

    // Permite que essas rotas sejam acessadas de fora
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', '/user/add'],

    'allowed_methods' => ['*'],

    // AQUI É O PULO DO GATO: Em vez de '*', definimos as origens exatas do Vue
    // Adicionei localhost e 127.0.0.1 nas portas comuns do Vite (5173) e Vue CLI (8080)
    // Bem como o domínio do Traefik local para Docker
    //
    // Para liberar um novo domínio sem alterar o código, defina a variável
    // CORS_ALLOWED_ORIGINS com as origens separadas por vírgula e SEM barra final.
    // Ex.: CORS_ALLOWED_ORIGINS=https://meu-front.com,https://outro.com
    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('CORS_ALLOWED_ORIGINS', implode(',', [
            'http://localhost:5173',
            'http://localhost:5174',
            'http://127.0.0.1:5173',
            'http://localhost:8080',
            'https://app.localhost',
            'http://app.localhost',
            'https://frontend-progest-base-production.up.railway.app',
        ])))
    ))),

    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,

    // Permite envio de cookies/credenciais se necessário
    'supports_credentials' => true,
];