<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Strux\Component\Exceptions\AuthorizationException;
use Strux\Component\Exceptions\RouteNotFoundException;
use Strux\Component\Http\Psr7\Response;

class ExceptionHandlingMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (AuthorizationException $e) {
            // Handle Unauthorized / Forbidden
            $response = new Response();
            $response->withStatus($e->getCode() ?: 403);

            // You might want to render a nice view here instead of raw HTML
            // For now, basic output:
            $html = sprintf(
                '<html><head><title>Access Denied</title></head><body><h1>403 Forbidden</h1><p>%s</p><a href="/">Go Home</a></body></html>',
                htmlspecialchars($e->getMessage())
            );

            $response->getBody()->write($html);
            return $response;

        } catch (RouteNotFoundException $e) {
            // Handle 404
            $response = new Response();
            $response->withStatus(404);
            $response->getBody()->write('<h1>404 Not Found</h1><p>The requested page does not exist.</p>');
            return $response;

        } catch (\Throwable $e) {
            // Handle General Errors (500)
            // In production, log this and show generic message. In dev, show trace.
            $response = new Response();
            $response->withStatus(500);
            $response->getBody()->write('<h1>500 Internal Server Error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>');
            return $response;
        }
    }
}