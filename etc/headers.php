<?php

return [
    'headers' => [
        /*
        |--------------------------------------------------------------------------
        | X-Powered-By Header
        |--------------------------------------------------------------------------
        |
        | This option controls the sending of the X-Powered-By header, which
        | identifies the framework. For security through obscurity, users may
        | wish to disable this header entirely.
        |
        | To disable, set 'enabled' to false in this file, or set the
        | HEADERS_X_POWERED_BY_ENABLED environment variable to 'false'.
        |
        */
        'x_powered_by' => [
            'enabled' => env('HEADERS_X_POWERED_BY_ENABLED', true),
            'value' => 'Strux Framework',
        ]
    ]
];
