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
        |
        */
        'disks' => [
            'local' => [
                'driver' => 'local',
                // Files stored here are not typically web-accessible.
                'root' => ROOT_PATH . '/storage/app',
            ],

            'web' => [
                'driver' => 'local',
                // Files stored here are intended to be web-accessible.
                // The root path is inside the web directory.
                'root' => ROOT_PATH . '/web/storage',
                // The URL to access these files from the browser.
                'url' => env('APP_URL', 'http://localhost') . '/storage',
            ],

            // You could imagine adding an 's3' driver here in the future
            // that would use the AWS SDK instead of local file functions.
        ]
    ]
];