<?php

declare(strict_types=1);

use App\Domain\Identity\Entity\User;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\TestInvokeMethodController;
use App\Http\Middleware\GuestMiddleware;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Strux\Auth\Auth;
use Strux\Auth\Middleware\AuthorizationMiddleware;
use Strux\Component\Config\Config;
use Strux\Component\Http\Request;
use Strux\Component\Http\Response;
use Strux\Component\Routing\Router;

return function (Router $router): void {

    // --- Authentication Routes ---
    // GuestMiddleware would redirect authenticated users away from login/register pages
    // We'd need to create GuestMiddleware similar to AuthorizationMiddleware but checking $auth->check() and redirecting if true.
    // For now, let's assume it exists or these routes are just web.
    // Example of applying GuestMiddleware to a group:
    $router->group(['middleware' => [GuestMiddleware::class]], function (Router $router) {
        $router->get('/auth/login', [AuthController::class, 'showLogin'])->name('auth.login');
        $router->post('/auth/login', [AuthController::class, 'loginUser'])->name('auth.login.attempt');
        $router->get('/auth/register', [AuthController::class, 'showRegistration'])->name('auth.register');
        $router->post('/auth/register', [AuthController::class, 'registerUser'])->name('auth.register.attempt');
        $router->get('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
        $router->post('/auth/forgot-password/send', [AuthController::class, 'forgotPasswordSend'])->name('auth.forgot-password.send');
        $router->get('/auth/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password');
        $router->put('/auth/reset-password/update', [AuthController::class, 'resetPasswordUpdate'])->name('auth.reset-password.update');
        $router->get('/auth/verify-email', [AuthController::class, 'showEmailVerification'])->name('auth.verify-email');
        $router->get('/auth/verify-email/resend', [AuthController::class, 'resendEmailVerification'])->name('auth.verify-email.resend');
    });

    $router->get('/twig/test', [AuthController::class, 'twigTest'])->name('twig.test');

    // Corrected logout route: Middleware is the 3rd argument for post()
    $router->post('/auth/logout', [AuthController::class, 'logoutUser'])
        ->middleware([AuthorizationMiddleware::class])
        ->name('auth.logout');

    // --- Example Closure Routes using new syntax and helpers ---
    $router->get('/profile', function (Auth $auth): Response { // AuthService injected
        /*$user = User::query()
            ->with('customer')
            ->where('userID', $auth->user()->userID)
            ->first();*/
        $user = User::find($auth->user()->userID, with: ['customer']);
        return view('customer/profile', [ // Using view() helper
            'title' => 'Profile | ' . htmlspecialchars($user->firstname . '|' . $auth->user()->userID ?? 'N/A'),
            'user' => $user
        ]);
    })
        ->middleware([AuthorizationMiddleware::class])
        ->name('profile'); // Example name

    $router->post('/profile/update/int:name|?', function (Request $appRequest, Auth $auth, ?int $name = null): Response {
        // $name is from route, or default. $appRequest is Strux\Component\Http\Request
        $newName = $appRequest->input('new_name', $name); // Example: get from form input
        logger()->info("Profile update attempt for: " . ($name ?? 'current user') . " to " . $newName);
        // ... update logic ...
        return to_route('profile.view', [], 302, ['success' => 'Profile for ' . es_($name ?? 'user') . ' updated!']);
    })
        ->middleware([AuthorizationMiddleware::class])
        ->defaults(['name' => 'DefaultUserFromRouteDefault']) // Default for the :name param
        ->name('profile.update');


    // --- API Routes Example ---
    $router->group(['prefix' => 'api/v1'], function (Router $router) {
        $router->get('/ping', function (): Response { // No params needed, use helpers
            return json(['message' => 'pong', 'timestamp' => time()]); // Using json() helper
        });

        // This route will return a simple JSON response.
        $router->get('/test-cors', function () {
            return response()->json([
                'message' => 'CORS is working!',
                'time' => date('H:i:s')
            ]);
        });
    });

    // --- Redirect Example ---
    $router->get('/old-home', null) // Handler is null for a redirect-only route
    ->name('old.home.redirect'); // Name the redirect itself (optional)
    // The redirect target is now part of the Route attribute for attribute routing.
    // For traditional, Router needs a ->redirect() method or similar.
    // The current Router::addRedirect is separate.
    // To use toPath/toRoute with traditional, we need to adjust how Router handles this.
    // For now, let's assume a redirect method on the router:
    // $router->redirect('/old-home', '/new-home-path', 301);
    // Or, if a route definition itself can be a redirect:
    // This requires `addRouteDefinition` to handle a special handler or `Route` attribute properties.
    // The current structure is more geared towards attribute-defined redirects.

    // Let's use the Router's addRedirect method for traditional redirects:
    $router->addRedirect('/legacy-profile', '/profile');
    $router->addRedirect('/info', '/about', 302);

    // --- Example with typed parameters and defaults ---
    $router->get('/blog/:category|?/page/int:page|?', function (Request $req, ?string $category = null, ?int $page = null): Response {
        $content = "Category: " . htmlspecialchars($category) . ", Page: " . ($page !== null ? htmlspecialchars((string)$page) : 'Not set');
        // $page will have its default from ->defaults() if not in URL
        return response($content); // Using response() helper
    })
        ->defaults(['category' => 'general', 'page' => 1])
        ->name('blog.category.page');

    $router->group(['prefix' => 'test'], function (Router $router) {
        $router->get('/string:id', function (Request $request, string $id) {
            return response("ID is: " . htmlspecialchars($id) . " and type is: " . gettype($id));
        });
        $router->get('/cache/int:id', function (Request $request, int $id) {
            // Resolve services using ContainerBridge as we are in a closure
            /** @var CacheInterface $cache */
            $cache = container(CacheInterface::class);
            /** @var LoggerInterface $logger */
            $logger = container(LoggerInterface::class);
            /** @var Config $appConfig */
            $appConfig = container(Config::class); // To log which driver is used

            $cacheDriverInUse = $appConfig->get('cache.default', 'unknown');
            $cacheKey = 'test_route_id_' . $id;
            $ttlSeconds = 30; // Cache for 30 seconds for testing

            $cachedData = $cache->get($cacheKey);

            if ($cachedData === null) {
                $logger->info("[CacheTestRoute] Cache MISS for key: $cacheKey (Driver: $cacheDriverInUse)");
                $freshData = "ID is: $id. Fetched fresh at " . date('H:i:s') . " (Driver: $cacheDriverInUse)";

                if ($cache->set($cacheKey, $freshData, $ttlSeconds)) {
                    $logger->info("[CacheTestRoute] Cache SET for key: $cacheKey with TTL {$ttlSeconds}s");
                } else {
                    $logger->error("[CacheTestRoute] FAILED to SET cache for key: $cacheKey");
                }
                // Return the fresh data
                return response($freshData); // Using your global response() helper
            } else {
                $logger->info("[CacheTestRoute] Cache HIT for key: $cacheKey (Driver: $cacheDriverInUse)");
                // Return the cached data, perhaps indicating it's from cache
                return response($cachedData . " - (Retrieved from cache at " . date('H:i:s') . ")");
            }
        });
    });

    $router->get('/phpinfo', function (Response $response): Response {
        phpinfo();
        return $response;
    });

    $router->get('/test-invoke-method', TestInvokeMethodController::class)
        ->name('test.invoke.method');

    // In a test route
    $router->get('/relations', function () {
        // Find a product
        /*$product = \App\Domain\Ticketing\Entity\Product::find(1);
        dump($product);*/

        // Eager load its categories
        $productWithCategories = \App\Domain\Ticketing\Entity\Product::query()->with('categories')->all();
        // dump($productWithCategories);

        // Find a category and get its products
        $categoryWithProducts = \App\Domain\Ticketing\Entity\Category::query()->with('products')->get();
        dump($categoryWithProducts);
    });

    // In `routes/web.php`
    $router->get('/terms-of-service', function (): Response {
        // This content rarely changes, so cache it for a long time.
        return view('pages/terms');
    })
        ->name('terms')
        ->cache(86400); // <-- Cache for a full day (86400 seconds)
};