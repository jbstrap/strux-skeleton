<?php

declare(strict_types=1);

/**
 * Kernel PHP Framework
 *
 * This file is the single point of entry for all HTTP requests.
 */

use App\App;
use App\Http\Controllers\Web\WelcomeController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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

// $app->addRegistry(\App\Registry\AppRegistry::class);

// dump($app->version());

// dump('App Config: ', Config::all());

// dump('App Config: ', Config::all());

$elapsedTimeMiddleware = new class implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $body = (string) $response->getBody();

        if (strpos($body, '{elapsed_time}') !== false) {
            $elapsed = round(microtime(true) - APP_START_TIME, 4);
            $body = str_replace('{elapsed_time}', (string) $elapsed, $body);

            // Create a new stream for the body
            $resource = fopen('php://temp', 'r+');
            fwrite($resource, $body);
            rewind($resource);
            $newBody = new \Strux\Component\Http\Psr7\Stream($resource);
            
            return $response->withBody($newBody);
        }

        return $response;
    }
};

$afterMiddleware = new class implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        return $response->withHeader('X-Added-Header', 'some-value');
    }
};

/** * -------------------------------------------------------------------------
 * 1. Register Global Middleware
 * -------------------------------------------------------------------------
 * We can add anonymous classes or string class names here.
 */
$app->addMiddleware(new class implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        return $response->withHeader('X-Framework-Test', 'Passed');
    }
});

/*$app->addMiddleware(new class implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (RouteNotFoundException $e) {
            $response = new Response();
            $response = $response->withStatus(404);
            $response->getBody()->write('<h1>404 Not Found</h1>');
            return $response;
        } catch (\Throwable $e) {
            $response = new Response();
            $response = $response->withStatus(500);
            $response->getBody()->write('<h1>Error</h1><p>' . $e->getMessage() . '</p>');
            return $response;
        }
    }
});*/

// $app->addMiddleware($beforeMiddleware);
$app->addMiddleware($elapsedTimeMiddleware);
$app->addMiddleware($afterMiddleware);

// $app->disableMiddleware(ConvertEmptyStringsToNull::class);

// $app->addMiddleware(\App\Http\Middleware\ExceptionHandlingMiddleware::class);

/** * -------------------------------------------------------------------------
 * 2. Bind Services to Container
 * -------------------------------------------------------------------------
 */
$app->addSingleton('app.version', fn() => '1.0.0-beta');

/**
 * -------------------------------------------------------------------------
 * 3. Define Routes
 * -------------------------------------------------------------------------
 */

// Closure Route
$app->get('/version', function () use ($app) {
    $version = $app->getContainer()->get('app.version');
    return new \Strux\Component\Http\Response("<h1>It Works!</h1><p>Running version: {$version}</p>");
});

// Controller Route
// \Strux\Support\Bridge\Router::get('/welcome', [WelcomeController::class, 'index']);
// \Strux\Support\Bridge\Router::get('/api', [WelcomeController::class, 'api']);

// Route Group with Prefix
$app->group(['prefix' => '/api'], function (App $app) {

    // JSON Route (Closure)
    $app->get('/status', static function () {
        return \response()->json(['status' => 'ok']);
    });

    // Controller JSON Route
    // Note: If your router doesn't auto-convert arrays to JSON, use Response object as above.
    $app->get('/controller', [WelcomeController::class, 'api']);
});

$app->run();