<?php

// return [
//     'paths'                    => ['api/*', 'sanctum/csrf-cookie'],
//     'allowed_methods'          => ['*'],
//     'allowed_origins'          => ['http://localhost:5173', 'http://127.0.0.1:5173', 'http://localhost:3000', 'http://127.0.0.1:3000'],
//     'allowed_origins_patterns' => [],
//     'allowed_headers'          => ['*'],
//     'exposed_headers'          => [],
//     'max_age'                  => 0,
//     'supports_credentials'     => true,
// ];

return [
    'paths'                    => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods'          => ['*'],
    'allowed_origins'          => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'https://beni-market-front.vercel.app', // ✅ ton domaine Vercel
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers'          => ['*'],
    'exposed_headers'          => [],
    'max_age'                  => 0,
    'supports_credentials'     => true,
];
