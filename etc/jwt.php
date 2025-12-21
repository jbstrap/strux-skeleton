<?php

return [
    'jwt' => [
        // The secret key for signing the JWT.
        // IMPORTANT: This should be a long, random string and kept secret.
        'secret' => env('JWT_SECRET'),

        // The signing algorithm.
        'algo' => 'HS256',

        // The token lifetime in seconds.
        'expiration' => (int)env('JWT_EXPIRATION', 3600),

        // The token issuer identifier.
        'issuer' => env('APP_URL', 'http://127.0.0.1:8000'),

        // The token audience identifier.
        'audience' => env('APP_URL', 'http://127.0.0.1:8000')
    ]
];