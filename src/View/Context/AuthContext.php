<?php

declare(strict_types=1);

namespace App\View\Context;

use Strux\Auth\AuthManager;
use Strux\Component\View\Context\ContextInterface;

class AuthContext implements ContextInterface
{
    private AuthManager $auth;

    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth;
    }

    public function process(): array
    {
        return [
            'auth' => $this->auth,
            'currentUser' => $this->auth->sentinel()->user()
        ];
    }
}