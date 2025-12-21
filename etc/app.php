<?php

return [
    // Your “application” etc
    'app' => [
        'name' => env('APP_NAME', 'Ticketing System'),

        // 'domain' = DDD structure (src/Domain/Identity/Entity)
        // 'standard' = Flat structure (src/Entity, src/Controller)
        'mode' => env('APP_MODE', 'domain'),

        'meta' => [
            'title' => env('META_TITLE', 'Ticketing System - A Simple PHP Framework'),
            'description' => env('META_DESCRIPTION', 'A lightweight PHP framework for building web applications.'),
        ],
        'env' => env('APP_ENV', 'development'), // or production, testing
        'debug' => (bool)env('APP_DEBUG', true),
        'url' => env('APP_URL', 'http://127.0.0.1:8000'),
        'timezone' => 'UTC',
        'sessions' => [
            'driver' => 'file',
            'lifetime' => 120,
            'expire_on_close' => false,
            'path' => '/tmp',
            'name' => 'session_id',
            'domain' => null,
            'secure' => false,
            'http_only' => true,
        ],
        'csrf' => [
            'token_name' => 'csrf_token',
            'cookie_name' => 'csrf_cookie',
            'expire' => 7200,
            'secure' => false,
            'http_only' => true,
            'same_site' => null,
        ],
        'encryption' => [
            'cipher' => 'AES-256-CBC',
            'key' => 'random_key_32_bytes_long',
            'cipher_mode' => 'CBC'
        ],

        /*
        |--------------------------------------------------------------------------
        | Autoloader Service Registries
        |--------------------------------------------------------------------------
        | List Class Names of custom ServiceRegistries here.
        | Do NOT put raw service definitions (closures) here.
        | Use etc/services.php for that.
        */
        'registries' => [
            \App\Registry\MyCustomRegistry::class,
        ]
    ]
];
