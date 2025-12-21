<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Identity\Entity\User;
use App\Http\Middleware\GuestMiddleware;
use Strux\Auth\Middleware\AuthorizationMiddleware;
use Strux\Component\Attributes\Middleware;
use Strux\Component\Attributes\Prefix;
use Strux\Component\Attributes\Route;
use Strux\Component\Exceptions\DatabaseException;
use Strux\Component\Http\Controller\Web\Controller;
use Strux\Component\Http\Request;
use Strux\Component\Http\Response;

#[Prefix('/users')]
// Example: Apply AuthorizationMiddleware if these routes should be protected
#[Middleware([AuthorizationMiddleware::class, GuestMiddleware::class])]
class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    #[Route('/', methods: ['GET'], name: 'users.index')]
    public function index(Request $request): Response
    {
        try {
            // Using the static findAll method from a User model
            // $users = User::findAll($this->db, $this->logger);
            $users = [
                'id' => 1,
                'firstname' => 'John',
                'lastname' => 'Doe',
                'email' => 'email@example.com'
            ];

            return $this->view('users/index', [ // Assumes 'users/index.php' or '.twig'
                'title' => 'User List',
                'users' => $users
            ]);
        } catch (DatabaseException $e) {
            $this->logError('Error fetching users: ' . $e->getMessage(), ['exception' => $e]);
            if ($this->flash) {
                $this->flash->error('Could not retrieve user list due to a database error.');
            }
            // Render an error view or redirect
            return $this->view('error/generic', ['title' => 'Database Error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified user.
     * Route: /users/{id}
     */
    #[Route('/{id:\d+}', methods: ['GET'], name: 'users.show')]
    public function show(Request $request, int $id): Response // $id is route param
    {
        $userModel = new User($this->db, $this->logger);

        try {
            $user = $userModel->findById($id);

            if (!$user) {
                if ($this->flash) {
                    $this->flash->error("User with ID {$id} not found.");
                }
                // Redirect to a user list or a 404 page
                return $this->redirectWith($this->route('users.index'), [], 404, true);
            }

            return $this->view('users/show', [ // Assumes 'users/show.php' or '.twig'
                'title' => 'User Profile: ' . htmlspecialchars($user->username ?? 'N/A'),
                'user' => $user
            ]);

        } catch (DatabaseException $e) {
            $this->logError("Database error showing user ID {$id}: " . $e->getMessage(), ['exception' => $e]);
            if ($this->flash) {
                $this->flash->error('A database error occurred while trying to fetch the user.');
            }
            return $this->view('error/generic', ['title' => 'Database Error', 'message' => 'Could not retrieve user details.'], 500);
        }
    }

    /**
     * Show the form for creating a new user.
     * Route: /users/create
     */
    #[Route('/create', methods: ['GET'], name: 'users.create')]
    public function create(Request $request): Response
    {
        return $this->view('users/create', [ // Assumes 'users/create.php' or '.twig'
            'title' => 'Create New User'
            // Pass an empty user object or default values if your form needs them
            // 'user' => new User(),
        ]);
    }

    /**
     * Store a newly created user in var.
     * Route: /users/store (or typically just /users with POST)
     */
    #[Route('/store', methods: ['POST'], name: 'users.store')]
    // Or, more RESTFully: #[Route('/', methods: ['POST'], name: 'users.store')]
    public function store(Request $request): Response
    {
        $username = $request->input("username");
        $email = $request->input("email");
        $plainPassword = $request->input("password");
        $customerId = $request->input("customer_id"); // Optional customer ID

        // Basic Validation (use a proper validator in a real src)
        $errors = [];
        if (empty($username)) $errors['username'] = 'Username is required.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'A valid email is required.';
        if (empty($plainPassword)) $errors['password'] = 'Password is required.';
        // Add more validation (e.g., password strength, username uniqueness check before save)

        if (!empty($errors)) {
            if ($this->flash) {
                foreach ($errors as $field => $message) {
                    $this->flash->error($message); // Or group errors under one key
                }
            }
            // Redirect back to create form with errors and old input (not fully implemented here)
            return $this->redirectWith($this->route('users.create'), [], 302, true);
        }

        $userModel = new User($this->db, $this->logger);
        $userModel->username = trim($username);
        $userModel->email = trim($email);
        $userModel->setPassword($plainPassword); // Hashes the password
        if (!empty($customerId)) {
            $userModel->customerId = (int)$customerId;
        }


        try {
            if ($userModel->save()) {
                if ($this->flash) {
                    $this->flash->success('User created successfully!');
                }
                return $this->redirect($this->route('users.show', ['id' => $userModel->id]));
            } else {
                if ($this->flash) {
                    $this->flash->error('Failed to create user. Please try again. Possible duplicate username/email.');
                }
            }
        } catch (DatabaseException $e) {
            $this->logError("Error creating user: " . $e->getMessage(), ['exception' => $e, 'data' => $request->allPost()]);
            if ($this->flash) {
                $this->flash->error('A database error occurred: ' . $e->getMessage());
            }
        }

        // If save fails or an error occurs, redirect back to create form
        return $this->redirectWith($this->route('users.create'), [], 302, true); // Add old input data to session/flash
    }

    /**
     * Show the form for editing the specified user.
     * Route: /users/edit/{id}
     * @throws DatabaseException
     */
    #[Route('/edit/{id:\d+}', methods: ['GET'], name: 'users.edit')]
    public function edit(Request $request, int $id): Response
    {
        $userModel = new User($this->db, $this->logger);
        $user = $userModel->findById($id);

        if (!$user) {
            if ($this->flash) $this->flash->error("User with ID $id not found.");
            return $this->redirectWith($this->route('users.index'), [], 404, true);
        }

        return $this->view('users/edit', [ // Assumes 'users/edit.php' or '.twig'
            'title' => 'Edit User: ' . htmlspecialchars($user->username),
            'user' => $user
        ]);
    }

    /**
     * Update the specified user in var.
     * Route: /users/update/{id}
     * @throws \Strux\Component\Exceptions\DatabaseException
     */
    #[Route('/update/{id:\d+}', methods: ['POST'], name: 'users.update')] // Or PUT if using true REST
    public function update(Request $request, int $id): Response
    {
        $userModel = new User($this->db, $this->logger);
        $user = $userModel->findById($id);

        if (!$user) {
            if ($this->flash) $this->flash->error("User with ID $id not found for update.");
            return $this->redirectWith($this->route('users.index'), [], 404, true);
        }

        $username = $request->input("username");
        $email = $request->input("email");
        $plainPassword = $request->input("password"); // Optional: only update if provided
        $customerId = $request->input("customer_id");

        // Basic Validation
        $errors = [];
        if (empty($username)) $errors['username'] = 'Username is required.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'A valid email is required.';
        // Add more validation

        if (!empty($errors)) {
            if ($this->flash) {
                foreach ($errors as $field => $message) {
                    $this->flash->error($message);
                }
            }
            return $this->redirectWith($this->route('users.edit', ['id' => $id]), [], 302, true);
        }

        $user->username = trim($username);
        $user->email = trim($email);
        if (!empty($plainPassword)) {
            $user->setPassword($plainPassword);
        }
        $user->customerId = !empty($customerId) ? (int)$customerId : null;


        try {
            if ($user->save()) {
                if ($this->flash) $this->flash->success('User updated successfully!');
                return $this->redirect($this->route('users.show', ['id' => $id]));
            } else {
                if ($this->flash) $this->flash->error('Failed to update user. Possible duplicate username/email.');
            }
        } catch (DatabaseException $e) {
            $this->logError("Error updating user #{$id}: " . $e->getMessage(), ['exception' => $e]);
            if ($this->flash) $this->flash->error('A database error occurred: ' . $e->getMessage());
        }

        return $this->redirectWith($this->route('users.edit', ['id' => $id]), [], 302, true);
    }

    /**
     * Remove the specified user from var.
     * Route: /users/delete/{id}
     * @throws \Strux\Component\Exceptions\DatabaseException
     */
    #[Route('/delete/{id:\d+}', methods: ['POST'], name: 'users.delete')] // Use POST for form-based delete
    public function delete(Request $request, int $id): Response
    {
        // Add check: ensure current user has permission to delete users, or can only delete self (with care)
        // For example, if ($this->auth()->user()->id === $id || $this->auth()->user()->isAdmin()) { ... }

        $userModel = new User($this->db, $this->logger);
        $user = $userModel->findById($id);

        if (!$user) {
            if ($this->flash) $this->flash->error("User with ID $id not found.");
            return $this->redirectWith($this->route('users.index'), [], 404, true);
        }

        // Implement actual delete logic in User model, e.g., $user->delete();
        // For now, just a message:
        $this->logInfo("Attempting to delete user ID: $id");
        // try {
        //     if ($user->delete()) { // Assuming a delete method in User model
        //         if ($this->flash) $this->flash->success("User #{$id} ({$user->username}) deleted successfully.");
        //     } else {
        //         if ($this->flash) $this->flash->error("Failed to delete user #{$id}.");
        //     }
        // } catch (DatabaseException $e) {
        //     $this->logError("Error deleting user #{$id}: " . $e->getMessage(), ['exception' => $e]);
        //     if ($this->flash) $this->flash->error('A database error occurred while deleting the user.');
        // }
        if ($this->flash) $this->flash->success("User #$id ({$user->username}) would be deleted (mocked)."); // Mock message

        return $this->redirectWith($this->route('users.index'), [], 302, true);
    }
}