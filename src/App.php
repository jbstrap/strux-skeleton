<?php

declare(strict_types=1);

namespace App;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Strux\Bootstrapping\Kernel;
use Strux\Foundation\Application;

class App extends Application
{
    /**
     * Factory method to create the application instance.
     *
     * @param string $rootPath
     * @return self
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public static function create(string $rootPath): self
    {
        /** @var self $app */
        $app = Kernel::create($rootPath, self::class);

        return $app;
    }

    // You can add custom helper methods here that you want available on $app instance
    public function version(): string
    {
        return '1.0.0';
    }
}