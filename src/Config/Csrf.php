<?php

declare(strict_types=1);

namespace App\Config;

use Strux\Component\Config\ConfigInterface;

class Csrf implements ConfigInterface
{
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            /**
             * --------------------------------------------------------------------------
             * Except
             * --------------------------------------------------------------------------
             *
             * The URIs that should be excluded from CSRF verification. You can use
             * wildcards (*) to match multiple paths. This is the perfect place
             * to exclude all of your API routes.
             *
             */
            'except' => [
                'api/*', // Exclude all routes starting with 'api/'
            ],

            /**
             * --------------------------------------------------------------------------
             * Token Lifetime
             * --------------------------------------------------------------------------
             *
             * The number of seconds the CSRF token is valid. Defaults to 2 hours.
             *
             */
            'lifetime' => 7200
        ];
    }
}