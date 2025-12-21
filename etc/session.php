<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default session driver that you would like
    | to use for your application. By default, we will use the lightweight
    | native file driver, but you may specify any of a number of other
    | wonderful drivers provided here.
    |
    | Supported: "native", "database"
    |
    */
    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime
    |--------------------------------------------------------------------------
    |
    | Here you may specify the number of minutes that you wish the session
    | to be allowed to remain idle before it expires. If you want them
    | to expire immediately on browser close, set this to zero.
    |
    */
    'lifetime' => env('SESSION_LIFETIME', 120), // In minutes

    /*
    |--------------------------------------------------------------------------
    | Session Database Connection
    |--------------------------------------------------------------------------
    |
    | When using the "database" session driver, you may specify the database
    | connection that should be used to store sessions. This value should
    | correspond to a connection defined in your database etc.
    |
    */
    'connection' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Table
    |--------------------------------------------------------------------------
    */
    'table' => 'sessions',

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Name
    |--------------------------------------------------------------------------
    */
    'cookie' => env('SESSION_COOKIE', 'strux_session'),

    /*
    |--------------------------------------------------------------------------
    | All other cookie options
    |--------------------------------------------------------------------------
    */
    'path' => '/',
    'domain' => env('SESSION_DOMAIN'),
    'secure' => env('SESSION_SECURE_COOKIE', false),
    'http_only' => true,
    'same_site' => 'lax',
];