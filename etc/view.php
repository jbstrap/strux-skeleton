<?php

/**
 * View Engine Configuration
 *
 * Define your preferred view engine, template paths, and engine-specific options.
 */

return [
    'view' => [
        /**
         * Default view engine.
         * Options: 'twig', 'plates'
         * Can be overridden by environment variable VIEW_ENGINE.
         */
        'engine' => env('VIEW_ENGINE') ?: 'plates',

        /**
         * Template paths.
         * 'default' is the main directory for templates.
         * You can add other named paths (namespaces) for organization.
         * Example: 'admin' => ROOT_PATH . '/templates/views/admin'
         * For Twig, these become namespaces (e.g., @admin/template.html.twig).
         * For Plates, these become folders (e.g., $this->layout('admin::layout')).
         */
        'template_paths' => [
            'default' => defined('ROOT_PATH') ? ROOT_PATH . '/templates' : dirname(__DIR__) . '/templates',
            'partials' => defined('ROOT_PATH') ? ROOT_PATH . '/templates/partials' : dirname(__DIR__) . '/templates/partials',
        ],
        'context_providers' => [
            // \App\View\Context\AppContext::class,
            // \App\View\Context\AuthContext::class,
            // \App\View\Context\FlashContext::class,
        ],

        /**
         * Twig specific etc.
         * (Only used if 'engine' is 'twig')
         */
        'twig' => [
            /**
             * Path to the Twig cache directory.
             * Set false to disable caching (not recommended for production).
             * Can be overridden by environment variable TWIG_CACHE_ENABLED ('false' to disable).
             */
            'cache_path' => (env('TWIG_CACHE_ENABLED') === false || env('TWIG_CACHE_ENABLED') === null)
                ? false
                : (defined('ROOT_PATH') ? ROOT_PATH . '/var/cache/twig' : dirname(__DIR__) . '/var/cache/twig'),

            /**
             * Enable Twig's debug mode.
             * Automatically enabled if APP_DEBUG environment variable is 'true'.
             * Provides access to the dump() function in Twig templates.
             */
            'debug' => env('APP_DEBUG') === 'true',

            /**
             * Auto-reload templates when they change.
             * Recommended for development, can be disabled in production for a slight performance gain.
             * Automatically enabled if APP_ENV environment variable is 'development'.
             */
            'auto_reload' => env('APP_ENV') === 'development' || env('APP_ENV') === null, // Default to true if APP_ENV not set
        ],

        /**
         * Plates specific etc.
         * (Only used if 'engine' is 'plates')
         */
        'plates' => [
            /**
             * Default file extension for Plates templates.
             */
            'file_extension' => 'php', // Plates default is 'php'
        ]
    ]
];