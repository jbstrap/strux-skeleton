<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Identity\Entity\User;
use App\Domain\Identity\Enums\Roles;
use App\Domain\Identity\Event\UserRegistered;
use App\Domain\Identity\Repository\UserRepositoryInterface;
use Exception;
use Strux\Auth\Auth;
use Strux\Component\Http\Controller\Web\Controller;
use Strux\Component\Http\Request;
use Strux\Component\Http\Response;
use Strux\Component\Validation\Rules\Email;
use Strux\Component\Validation\Rules\Equal;
use Strux\Component\Validation\Rules\MinLength;
use Strux\Component\Validation\Rules\Password;
use Strux\Component\Validation\Rules\Required;
use Strux\Component\Validation\Validator;

/**
 * @property User $model - For model auto-completion.
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepo
    )
    {
        parent::__construct();
    }

    public function twigTest(): Response
    {
        return $this->view('test', [
            'title' => 'Twig Test',
            'items' => ['Apple', 'Banana', 'Cherry']
        ]);
    }

    /**
     * Display the login form.
     * Typically, this route should be protected by a "Guest" middleware
     * that redirects authenticated users to the dashboard.
     */
    // Example attribute routing:
    // #[Route('/login', methods: ['GET'], name: 'login')]
    // #[RouteMiddleware([GuestMiddleware::class])]
    public function showLogin(): Response
    {
        return $this->view('auth/login', [
            'title' => 'Login'
        ]);
    }

    /**
     * Handle user login attempt.
     */
    // Example attribute routing:
    // #[Route('/login', methods: ['POST'], name: 'login.attempt')]
    // #[RouteMiddleware([GuestMiddleware::class])]
    public function loginUser(Request $request): Response
    {
        $validator = new Validator($request->all());

        $validator->add('email', [new Required(), new Email()]);
        $validator->add('password', [new Required()]);

        if ($validator->isValid()) {
            $email = request()->input('email');
            $password = $request->input('password');

            $next = $request->input('next');

            if (Auth::attempt(['email' => $email, 'password' => $password])) {
                $intendedUrl = (!empty($next) && $next !== '/')
                    ? $next
                    : '/' . prefix() . '/dashboard';
                return $this->toRoute(
                    routeName: $intendedUrl,
                    flashMessages: ["success" => "Login successful! Welcome back."]
                );
            }
            $this->flash->set('error', 'Incorrect email or password');
            return $this->view('auth/login', ['title' => 'Login']);
        }
        $this->flash->set('errors', $validator->getErrors());
        return $this->toRoute('auth.login');
    }

    /**
     * Display the registration form.
     */
    // Example attribute routing:
    // #[Route('/register', methods: ['GET'], name: 'register')]
    // #[RouteMiddleware([GuestMiddleware::class])]
    public function showRegistration(): Response
    {
        return $this->view('auth/register', [
            'title' => 'Register'
        ]);
    }

    /**
     * Handle user registration attempt.
     */
    // Example attribute routing:
    // #[Route('/register', methods: ['POST'], name: 'register.attempt')]
    // #[RouteMiddleware([GuestMiddleware::class])]
    public function registerUser(Request $request): Response
    {
        $validator = new Validator($request->all());

        $validator->add('firstname', [new Required(), new MinLength(2)]);
        $validator->add('lastname', [new Required(), new MinLength(2)]);
        $validator->add('email', [new Required(), new Email()]);
        $validator->add('password', [new Required(), new Password()]);
        $validator->add('password_repeat', [new Required(), new Equal('password')]);

        if ($validator->isValid()) {
            /*$exists = User::query()->select('email')
                ->where('email', $request->input('email'))
                ->first();*/
            $exists = $this->userRepo->emailExists($request->input('email'));

            if ($exists) {
                $this->flash->set('error', 'The user with this email already exists');
                return $this->view('auth/register', ['title' => 'Register']);
            }

            $user = new User();
            $user->firstname = $request->input('firstname');
            $user->lastname = $request->input('lastname');
            $user->role = Roles::CUSTOMER->value;
            $user->email = $request->input('email');
            $user->setPassword($request->input('password'));

            try {
                if ($user->save()) {
                    $this->event->dispatch(new UserRegistered($user));

                    // The SendWelcomeEmail listener will now be automatically executed.

                    Auth::login($user);

                    $next = $request->input('next');

                    $intendedUrl = (!empty($next) && $next !== '/')
                        ? $next
                        : '/' . prefix() . '/dashboard';

                    return $this->toRoute(
                        routeName: $intendedUrl,
                        flashMessages: ['success' => 'Registration successful! Please check your email.']
                    );
                }
                $this->logger->error('Failed to save user during registration');
                $this->flash->set('error', 'An error occurred while creating customer. Please try again');
                return $this->view('auth/register', ['title' => 'Register']);
            } catch (Exception $e) {
                $this->flash->set('error', $e->getMessage());
                return $this->view('auth/register', ['title' => 'Register']);
            }
        }
        $this->flash->set('errors', $validator->getErrors());
        return $this->view('auth/register', ['title' => 'Register']);
    }

    public function forgotPassword(Request $request): Response
    {
        return $this->view('auth/forgot-password', ['title' => 'Forgot Password']);
    }

    public function forgotPasswordSend(Request $request): Response
    {
        return $this->view('auth/forgot-password', ['title' => 'Forgot Password Send']);
    }

    public function resetPassword(Request $request): Response
    {
        return $this->view('auth/reset-password', ['title' => 'Reset Password']);
    }

    public function resetPasswordUpdate(Request $request): Response
    {
        return $this->view('auth/reset-password', ['title' => 'Forgot Password Update']);
    }

    /**
     * Handle user logout.
     * This route MUST be protected by AuthorizationMiddleware.
     */
    // Example attribute routing:
    // #[Route('/logout', methods: ['POST'], name: 'logout')] // POST is good practice for logout
    // #[RouteMiddleware([AuthorizationMiddleware::class])]
    public function logoutUser(Request $request): Response
    {
        Auth::logout();
        $this->flash->set('success', 'You have been successfully logged out.');
        return $this->toRoute('auth.login'); // Redirect to home or login page
    }
}
