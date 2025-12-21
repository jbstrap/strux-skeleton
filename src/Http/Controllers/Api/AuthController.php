<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Strux\Component\Attributes\ApiController;
use Strux\Component\Attributes\ApiRoute;
use Strux\Component\Attributes\Consumes;
use Strux\Component\Attributes\Prefix;
use Strux\Component\Attributes\Produces;
use Strux\Component\Http\ApiResponse;
use Strux\Component\Http\Controller\Api\Controller;
use Strux\Component\Http\Request;
use Strux\Component\Validation\Rules\Email;
use Strux\Component\Validation\Rules\Required;
use Strux\Component\Validation\Validator;

#[ApiController]
#[Prefix('/api/auth')]
#[Produces('application/json')]
#[Consumes('application/json')]
class AuthController extends Controller
{
    #[ApiRoute('/login', methods: ['POST'], name: 'api.login')]
    public function login(Request $request): ApiResponse
    {
        // 1. Get the raw JSON body and validate its structure.
        $validator = new Validator($request->all());
        $validator->add('email', [new Required(), new Email()]);
        $validator->add('password', [new Required()]);

        if (!$validator->isValid()) {
            return $this->UnprocessableEntity($validator->getErrors());
        }

        // 2. Extract the validated credentials.
        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ];

        // 3. Use the 'web' sentinel's validate() method to check the credentials
        //    without creating a session.
        if (!$this->auth->sentinel('web')->validate($credentials)) {
            return $this->Unauthorized('Invalid email or password.');
        }

        // 4. If validation passes, get the user object.
        //    The sentinel stores it internally after a successful validation.
        $user = $this->auth->sentinel('web')->user();
        if (!$user) {
            // This case is unlikely but a good safeguard.
            return $this->Unauthorized('Could not retrieve user after validation.');
        }

        // 5. Create a JWT for the user.
        $token = $user->createToken();

        // 6. Return a successful response with the token.
        return $this->Ok([
            'token' => $token,
            'user' => $user->toArray(),
        ], 'Login successful.');
    }
}