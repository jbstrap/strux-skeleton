<?php

return [
    'maintenance' => [
        /**
         * Activate maintenance mode.
         * Can be overridden by an environment variable, e.g., MAINTENANCE_MODE=true
         */
        'active' => filter_var(env('MAINTENANCE_MODE'), FILTER_VALIDATE_BOOLEAN) ?? false,

        /**
         * IP addresses allowed accessing the application during maintenance mode.
         * Separate multiple IPs with commas in env var: ALLOWED_MAINTENANCE_IPS="1.2.3.4,5.6.7.8"
         */
        'allowed_ips' => array_filter(array_map('trim', explode(',', env('ALLOWED_MAINTENANCE_IPS') ?? ''))),
        // Example: 'allowed_ips' => ['127.0.0.1', '::1', 'your_office_ip'],

        /**
         * Route paths (regex patterns) that should remain accessible during maintenance mode.
         * Remember to escape slashes if needed for regex.
         * Example: '/admin/.*', '/api/v1/status'
         */
        'allowed_routes' => [
            // '/admin/login',
            // '/api/status'
        ],

        /**
         * The view template to render for the maintenance page.
         * Set to null to use the default hardcoded HTML.
         * Example for Twig: 'system/maintenance.html.twig'
         * Example for Plates: 'system::maintenance'
         */
        'view_template' => 'system/maintenance',

        /**
         * The HTTP status code to return.
         */
        'status_code' => 503,

        /**
         * The value for the Retry-After header (in seconds or an HTTP-date).
         * Example: 3600 (for 1 hour)
         * Example: 'Tue, 21 Oct 2025 07:28:00 GMT'
         */
        'retry_after' => 3600,

        /**
         * Optional: Secret key to bypass maintenance mode via a query parameter.
         * e.g., https://yoursite.com?bypass_token=YOUR_SECRET_KEY
         * This is an alternative/addition to IP whitelisting.
         * Store this securely, e.g., in .env
         */
        // 'bypass_token' => env('MAINTENANCE_BYPASS_TOKEN') ?? null,
    ]
];