<?php

use App\Domain\Identity\Repository\CustomerRepositoryInterface;
use App\Domain\Identity\Repository\UserRepositoryInterface;
use App\Domain\Ticketing\Repository\DepartmentRepositoryInterface;
use App\Domain\Ticketing\Repository\PriorityRepositoryInterface;
use App\Domain\Ticketing\Repository\TicketAttachmentRepositoryInterface;
use App\Domain\Ticketing\Repository\TicketCommentRepositoryInterface;
use App\Domain\Ticketing\Repository\TicketRepositoryInterface;
use App\Infrastructure\Database\Repository\CustomerRepository;
use App\Infrastructure\Database\Repository\DepartmentRepository;
use App\Infrastructure\Database\Repository\PriorityRepository;
use App\Infrastructure\Database\Repository\TicketAttachmentRepository;
use App\Infrastructure\Database\Repository\TicketCommentRepository;
use App\Infrastructure\Database\Repository\TicketRepository;
use App\Infrastructure\Database\Repository\UserRepository;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerInterface;
use Strux\Component\Config\Config;
use Tuupola\Middleware\CorsMiddleware;

return [
    'singletons' => [
        UserRepositoryInterface::class => fn(ContainerInterface $c) => new UserRepository(),
        TicketRepositoryInterface::class => fn(ContainerInterface $c) => new TicketRepository(),
        TicketCommentRepositoryInterface::class => fn(ContainerInterface $c) => new TicketCommentRepository(),
        TicketAttachmentRepositoryInterface::class => fn(ContainerInterface $c) => new TicketAttachmentRepository(),
        DepartmentRepositoryInterface::class => fn(ContainerInterface $c) => new DepartmentRepository(),
        PriorityRepositoryInterface::class => fn(ContainerInterface $c) => new PriorityRepository(),
        CustomerRepositoryInterface::class => fn(ContainerInterface $c) => new CustomerRepository(),

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
        MiddlewareInterface::class => function (ContainerInterface $c) {
            $cfg = $c->get(Config::class)->get('cors');
            return new CorsMiddleware($cfg);
        }
    ],

    'transients' => [
        // Services created fresh every time
    ]
];