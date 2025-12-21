<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CORS (Cross-Origin Resource Sharing) Options
    |--------------------------------------------------------------------------
    |
    | Here you may configure your etc. for cross-origin resource sharing
    | or "CORS". This determines which cross-origin domains are allowed to
    | access your application's templates.
    |
    | For detailed options, see: https://github.com/tuupola/cors-middleware
    |
    */
    "cors" => [
        "origin" => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000')),
        "methods" => [
            'GET',
            'POST',
            'PUT',
            'PATCH',
            'DELETE',
            'OPTIONS'
        ],
        "headers.allow" => [
            'Content-Type',
            'Authorization',
            'X-Requested-With'
        ],
        "headers.expose" => [],
        "credentials" => true,
        "cache" => 3600
    ]
];