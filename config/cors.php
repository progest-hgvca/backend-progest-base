<?php

return [

    // Permite que essas rotas sejam acessadas de fora
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', '/user/add'],

    'allowed_methods' => ['*'],

    // AQUI É O PULO DO GATO: Em vez de '*', definimos as origens exatas do Vue
    // Em produção, o domínio do frontend vem da variável de ambiente FRONTEND_URL
    // (configurada no painel do Railway/Render). Localmente, mantemos as portas do Vite.
    'allowed_origins' => array_values(array_filter([
        env('FRONTEND_URL'),
        'http://localhost:5173',
        'http://localhost:5174',
        'http://127.0.0.1:5173',
        'http://localhost:8080',
        'https://app.localhost',
        'http://app.localhost',
    ])),

    // Libera qualquer subdominio do Railway (*.up.railway.app), assim o
    // frontend em producao funciona mesmo sem depender do valor exato de
    // FRONTEND_URL. Sao expressoes regulares (preg_match).
    'allowed_origins_patterns' => [
        '#^https://[a-z0-9-]+\.up\.railway\.app$#',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,

    // Permite envio de cookies/credenciais se necessário
    'supports_credentials' => true,
];