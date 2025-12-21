<?php

declare(strict_types=1);

namespace App\Registry;

use Strux\Bootstrapping\Registry\ServiceRegistry;
use Psr\Container\ContainerInterface;

class MyCustomRegistry extends ServiceRegistry
{
    /**
     * Register bindings in the container.
     */
    public function build(): void
    {
        // Example: Bind a complex 3rd party service
        $this->container->singleton('\Some\ThirdParty\Client::class', function (ContainerInterface $c) {
            return new \Some\ThirdParty\Client(
                apiKey: getenv('API_KEY')
            );
        });
    }

    /**
     * Optional: Boot logic after the app is created.
     */
    public function init($app): void
    {
        // Example: Register a global event listener or middleware dynamically
    }
}