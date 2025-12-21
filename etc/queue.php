<?php

return [
    'queue' => [
        /*
        |--------------------------------------------------------------------------
        | Default Queue Connection Name
        |--------------------------------------------------------------------------
        */
        'default' => env('QUEUE_CONNECTION', 'database'),

        /*
        |--------------------------------------------------------------------------
        | Queue Connections
        |--------------------------------------------------------------------------
        |
        | Here you may configure the connection information for each server that
        | is used by your application. A default configuration has been added
        | for each back-end shipped with your framework.
        |
        */
        'connections' => [
            'sync' => [
                'driver' => 'sync',
            ],

            'database' => [
                'driver' => 'database',
                'table' => 'jobs', // The database table to use
                'queue' => 'default', // The default queue "channel"
                'retry_after' => 90, // Seconds to wait before retrying a job
            ],

            // 'redis' => [ ... ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Failed Job Logging
        |--------------------------------------------------------------------------
        */
        'failed' => [
            'driver' => 'database',
            'database' => env('DB_CONNECTION', 'mysql'),
            'table' => 'failed_jobs',
        ]
    ]
];