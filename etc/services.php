<?php

return [
    'singletons' => [
        // Overriding Core Services (e.g. Logger)
        /*LoggerInterface::class => static function (ContainerInterface $container): Logger {
            $logger = new Logger('app');
            $env = $container->get(Config::class)->get('app.env', 'production');
            echo "LoggerInterface binding in services.php, env: $env\n";
            if ($env === 'development') {
                $logger->pushHandler(new StreamHandler('php://stderr', Level::Debug));
                $logger->pushHandler(new BrowserConsoleHandler(Level::Debug));
            } else {
                $logger->pushHandler(new RotatingFileHandler(
                    dirname(__DIR__) . '/var/logs/app.log',
                    7,
                    Level::Warning
                ));
            }
            return $logger;
        },*/
    ],

    'scoped' => [
        // Scoped services (e.g. Request-specific middleware configs)
    ],

    'transients' => [
        // Services created fresh every time
    ]
];