<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Dotenv\Exception\ValidationException;
use Psr\Container\ContainerInterface;
use Strux\Bootstrapping\Registry\FrameworkRegistry;
use Strux\Component\Config\Config;
use Strux\Foundation\App;
use Strux\Foundation\Container;
use Strux\Support\ContainerBridge;

/**
 * -------------------------------------------------------------------------
 * Load Environment Variables
 * -------------------------------------------------------------------------
 */
if (class_exists(Dotenv::class) && file_exists(ROOT_PATH . '/.env')) {
    try {
        $dotenv = Dotenv::createImmutable(ROOT_PATH);
        $dotenv->load();
    } catch (InvalidPathException|ValidationException $e) {
        error_log("FATAL: Dotenv Exception: " . $e->getMessage());
        http_response_code(500);
        die("<h1>Application Configuration Error</h1><p>Essential configuration failed to load.</p>");
    }
}

/**
 * -------------------------------------------------------------------------
 * Create The Application Container
 * -------------------------------------------------------------------------
 */
$container = new Container();
$container->singleton(ContainerInterface::class, $container);
ContainerBridge::setContainer($container);

/**
 * -------------------------------------------------------------------------
 * Register Core Configuration
 * -------------------------------------------------------------------------
 */
$configValues = require ROOT_PATH . '/./etc/config.php';
$container->singleton(Config::class, fn() => new Config($configValues));

/**
 * -------------------------------------------------------------------------
 * Bootstrap The Framework
 * -------------------------------------------------------------------------
 */
$framework = new FrameworkRegistry($container);
$framework->build();

/**
 * -------------------------------------------------------------------------
 * Create & Initialize The Application
 * -------------------------------------------------------------------------
 */
$app = new App($container);
$framework->init($app);

return $app;