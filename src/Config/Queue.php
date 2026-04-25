<?php

declare(strict_types=1);

namespace App\Config;

use Strux\Component\Config\ConfigInterface;

class Queue implements ConfigInterface
{
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
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
                    'table' => 'jobs',
                    'queue' => 'default',
                    'retry_after' => 90
                ],

                // 'redis' => [ ... ], // Coming soon
            ],

            /*
            |--------------------------------------------------------------------------
            | Failed Job Logging
            |--------------------------------------------------------------------------
            */
            'failed' => [
                'table' => 'failed_jobs',
            ]
        ];
    }
}