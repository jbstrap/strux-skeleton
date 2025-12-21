<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Exception;
use Strux\Component\Http\Controller\Api\Controller;
use Strux\Component\Http\Response;

class ApiTokenController extends Controller
{
    /**
     * Create and store a new API token for the authenticated user.
     *
     * @return Response
     * @throws Exception
     */
    public function store(): Response
    {
        $user = $this->auth->user();
        if (!$user) {
            // This should not be reachable if AuthorizationMiddleware is used, but it's a good safeguard.
            return $this->toRoute('auth.login', flashMessages: ['error' => 'You must be logged in.']);
        }

        // Generate a secure, URL-safe token.
        $newToken = bin2hex(random_bytes(30));

        $user->api_token = $newToken;
        $user->save();

        // Flash the new token to the session to be displayed ONCE.
        // It's crucial for security that we don't make the token retrievable again.
        return $this->toRoute('profile', flashMessages: [
            'status' => 'API token generated successfully!',
            'new_token' => $newToken
        ]);
    }
}