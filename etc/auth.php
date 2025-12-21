<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "sentinel" and the
    | default path user is redirected to after successful login if no
    | specific 'next' parameter is present.
    |
    */
    'defaults' => [
        'sentinel' => 'web',
        'redirect_to' => '/', // Default route name or path after login
    ],

    /*
    |--------------------------------------------------------------------------
    | Sentinels
    |--------------------------------------------------------------------------
    |
    | Here you may define all the sentinels for your application as
    | well as their drivers. You can even define multiple sentinels for
    | the same driver if you have different user tables.
    |
    | Supported Drivers: "session", "token"
    |
    */
    'sentinels' => [
        'web' => [
            'driver' => 'session',
            'model' => \App\Domain\Identity\Entity\User::class, // The user model for this sentinel
        ],

        'api' => [
            'driver' => 'token',
            //'model' => \App\Domain\Identity\Entity\User::class,
            'storage_key' => 'api_token', // The column name for the API token
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Access Security
    |--------------------------------------------------------------------------
    |
    | This array maps your models to their corresponding Access Authority
    | class. These classes determine if a user is authorized to perform
    | actions on a given model.
    |
    */
    'authorities' => [
        // \App\Domain\Ticketing\Entity\Ticket::class => \App\Domain\Ticketing\Security\TicketAuthority::class,
        // \App\Models\Post::class => \App\Security\PostAuthority::class,
    ]
];