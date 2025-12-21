<?php

return [
    'mail' => [
        /*
        |--------------------------------------------------------------------------
        | Default Mailer
        |--------------------------------------------------------------------------
        |
        | This option controls the default mailer that is used by the framework
        | when handling emails. You may set this to any of the mailers
        | configured below.
        |
        */
        'default' => env('MAIL_MAILER', 'smtp'),

        /*
        |--------------------------------------------------------------------------
        | Mailer Configurations
        |--------------------------------------------------------------------------
        |
        | Here you may configure all the mailers used by your application
        | plus their respective etc. Several examples have been configured
        | for you and you are free to add your own.
        |
        */
        'mailers' => [
            'smtp' => [
                'transport' => 'smtp',
                'host' => env('MAIL_HOST', 'smtp.mailtrap.io'),
                'port' => env('MAIL_PORT', 2525),
                'encryption' => env('MAIL_ENCRYPTION', 'tls'), // 'tls' or 'ssl'
                'username' => env('MAIL_USERNAME'),
                'password' => env('MAIL_PASSWORD'),
                'timeout' => null,
            ],

            'sendmail' => [
                'transport' => 'sendmail',
                'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs'),
            ],

            'log' => [
                'transport' => 'log',
                // The channel from your etc/logging.php to use.
                'channel' => env('MAIL_LOG_CHANNEL'),
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Global "From" Address
        |--------------------------------------------------------------------------
        |
        | You may wish for all e-mails sent by your application to be sent from
        | the same address. Here, you may specify a name and address that is
        | used globally for all e-mails that are sent by your application.
        |
        */
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'name' => env('MAIL_FROM_NAME', 'My Custom Framework'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Debugging
        |--------------------------------------------------------------------------
        | Set to true to enable PHPMailer's verbose debugging output.
        | 0 = off, 1 = client messages, 2 = client and server messages
        */
        'debug' => (int)env('MAIL_DEBUG_LEVEL', 0)
    ]
];
