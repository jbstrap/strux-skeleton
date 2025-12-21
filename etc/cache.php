<?php

return [
    'cache' => [
        /*
        |--------------------------------------------------------------------------
        | Default Cache Store
        |--------------------------------------------------------------------------
        |
        | This option controls the default cache store that will be used by the
        | framework. This store is used when another store is not specified.
        | Supported: "filesystem", "array", "apcu"
        |
        */
        'default' => env('CACHE_DRIVER', 'apcu'),

        /*
        |--------------------------------------------------------------------------
        | Cache Stores
        |--------------------------------------------------------------------------
        |
        | Here you may define all the cache "stores" for your application as
        | well as their drivers. You may even define multiple stores for the
        | same cache driver to group types of cached data.
        |
        */
        'stores' => [
            'filesystem' => [
                'driver' => 'filesystem',
                'path' => env('CACHE_FILESYSTEM_PATH', ROOT_PATH . '/var/cache/simple'), // From your previous Cache.php
                'salt' => env('CACHE_SALT', 'YOUR_APP_SPECIFIC_SALT_FILESYSTEM'), // Driver-specific salt if needed
            ],

            'array' => [
                'driver' => 'array',
                'salt' => env('CACHE_SALT', 'YOUR_APP_SPECIFIC_SALT_ARRAY'), // Less relevant for an array but for consistency
                // 'serialize' => false, // Array driver usually stores data as is
            ],

            'apcu' => [
                'driver' => 'apcu',
                // APCu keys are global. A prefix helps avoid collisions with other apps or parts of your src.
                'prefix' => env('CACHE_APCU_PREFIX', 'app_cache_'),
                'salt' => env('CACHE_APCU_SALT', 'YOUR_APP_SPECIFIC_SALT_APCU'), // For key hashing consistency,
                // APCu TTL is handled per entry. No global TTL setting here.
            ]
        ],

        /*
        |--------------------------------------------------------------------------
        | Global Cache Key Prefix (Optional)
        |--------------------------------------------------------------------------
        | This global prefix can be used by drivers if they don't have a more specific one.
        | For APCu, the 'prefix' in the store etc is generally preferred.
        */
        'prefix' => env('CACHE_GLOBAL_PREFIX', 'myapp_core_cache_')
    ]
];