<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Strux\Auth\AuthManager;
use Strux\Component\Config\Config;
use Strux\Component\Routing\Router;
use Strux\Support\Helpers\FlashServiceInterface;

/**
 * Class GuestMiddleware
 *
 * PSR-15 middleware to ensure a route is only accessible by unauthenticated users (guests).
 * If an authenticated user tries to access, they are redirected.
 */
class GuestMiddleware implements MiddlewareInterface
{
    private AuthManager $authManager;
    private ResponseFactoryInterface $responseFactory;
    private Router $router;
    private FlashServiceInterface $flash;
    private ?LoggerInterface $logger;

    /**
     * GuestMiddleware constructor.
     *
     * @param AuthManager $authManager
     * @param ResponseFactoryInterface $responseFactory
     * @param Router $router
     * @param FlashServiceInterface $flash
     * @param Config|null $config
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        AuthManager              $authManager,
        ResponseFactoryInterface $responseFactory,
        Router                   $router,
        FlashServiceInterface    $flash,
        private readonly ?Config $config = null,
        ?LoggerInterface         $logger = null
    )
    {
        $this->authManager = $authManager;
        $this->responseFactory = $responseFactory;
        $this->router = $router;
        $this->flash = $flash;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Check if user is authenticated
        if ($this->authManager->sentinel('web')->check()) {

            // 1. Check Query Params (e.g. GET /login?next=/profile)
            $queryParams = $request->getQueryParams();
            $next = $queryParams['next'] ?? null;

            // 2. Check Parsed Body (if logic flows from a form, though rare for this middleware)
            if (empty($next)) {
                $body = $request->getParsedBody();
                $next = is_array($body) ? ($body['next'] ?? null) : null;
            }

            // 3. Fallback to Config Default
            if (empty($next) || $next === '/') {
                $next = $this->config->get('auth.defaults.redirect_to', '/');
            }

            // Generate URL
            try {
                // If it looks like a path (starts with /), use it directly.
                // Otherwise, treat it as a named route.
                if (str_starts_with($next, '/')) {
                    $url = $next;
                } else {
                    $url = $this->router->route($next);
                }
            } catch (\Exception $e) {
                // Fallback if route name not found
                $url = '/';
            }

            // User is authenticated, set flash message
            $this->flash->set('success', 'You are already logged in.');

            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $url);
        }

        return $handler->handle($request);
    }
}
