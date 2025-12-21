<?php

return [
    'filesystems' => [
        /*
        |--------------------------------------------------------------------------
        | Default Filesystem Disk
        |--------------------------------------------------------------------------
        */
        'default' => env('FILESYSTEM_DISK', 'local'),

        /*
        |--------------------------------------------------------------------------
        | Filesystem Disks
        |--------------------------------------------------------------------------
        | Here you may configure as many filesystem "disks" as you wish.
        | Defaults have been set up for each driver as an example of the required options.
        | Supported Drivers: "local". More coming soon.
        |
        */
        'disks' => [
            'local' => [
                'driver' => 'local',
                'root' => ROOT_PATH . '/storage/app',
            ],

            'web' => [
                'driver' => 'local',
                'root' => ROOT_PATH . '/web/storage',
                'url' => env('APP_URL', 'http://localhost') . '/storage',
            ]
        ]
    ]
];