<?php

declare(strict_types=1);

/**
 * Kernel PHP Framework
 *
 * This file is the single point of entry for all HTTP requests.
 */

use App\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Strux\Support\Bridge\Config;

/**
 * -------------------------------------------------------------------------
 * Define The Application Start Time
 * -------------------------------------------------------------------------
 */
define('APP_START_TIME', microtime(true));

/**
 * -------------------------------------------------------------------------
 * Define The Application Root Path
 * -------------------------------------------------------------------------
 */
define('ROOT_PATH', dirname(__DIR__));

/**
 * -------------------------------------------------------------------------
 * Register The Autoloader
 * -------------------------------------------------------------------------
 */
require_once ROOT_PATH . '/vendor/autoload.php';

/**
 * -------------------------------------------------------------------------
 * Create The Application
 * -------------------------------------------------------------------------
 */
$app = App::create(rootPath: ROOT_PATH);

$elapsedTimeMiddleware = new class implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $body = (string) $response->getBody();

        if (strpos($body, '{elapsed_time}') !== false) {
            $elapsed = round(microtime(true) - APP_START_TIME, 4);
            $body = str_replace('{elapsed_time}', (string) $elapsed, $body);

            $resource = fopen('php://temp', 'r+');
            fwrite($resource, $body);
            rewind($resource);
            $newBody = new \Strux\Component\Http\Psr7\Stream($resource);

            return $response->withBody($newBody);
        }

        return $response;
    }
};

$app->addMiddleware($elapsedTimeMiddleware);

$app->run();