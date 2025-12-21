<?php

return [
    'database' => [
        /*
        |--------------------------------------------------------------------------
        | Default Database Connection Name
        |--------------------------------------------------------------------------
        |
        | Here you may specify which of the database connections below you wish
        | to use as your default connection for all database work. Of course
        | you may use many connections at once using the Database library.
        |
        */
        'default' => env('DB_CONNECTION', 'sqlite'), // Use environment variable or default to sqlite

        /*
        |--------------------------------------------------------------------------
        | Database Connections
        |--------------------------------------------------------------------------
        |
        | Here are each of the database connections setup for your application.
        | Of course, examples of configuring each database platform that is
        | supported by PDO are provided below to make development simple.
        |
        */
        'connections' => [
            'sqlite' => [
                'driver' => 'sqlite',
                // Use an absolute path or a path relative to ROOT_PATH
                // DB_PATH from .env will override this path
                'path' => env('DB_PATH', ROOT_PATH . '/var/database/app.db'),
                'prefix' => '',
                'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true), // SQLite specific
                'options' => [ // SQLite specific options if needed, merged with global
                    // PDO::ATTR_TIMEOUT => 5, // Example
                ],
            ],

            'mysql' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'ticketing_db'),
                'username' => env('DB_USERNAME', 'ticketing_user'),
                'password' => env('DB_PASSWORD', 'ticketing_pass'),
                'unix_socket' => env('DB_SOCKET', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
                'options' => [ // MySQL specific options if needed, merged with global
                    // PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Global PDO Fetch Style & Options
        |--------------------------------------------------------------------------
        */
        'fetch' => PDO::FETCH_ASSOC, // Changed from FETCH_OBJ to match your DI setup
        'global_options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false, // Use real prepared statements
            // PDO::ATTR_PERSISTENT => false, // Persistent connections can have caveats
        ]
    ]
];
