<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Identity\Enums\Permissions;
use App\Domain\Identity\Enums\Roles;
use App\Domain\Identity\Repository\CustomerRepositoryInterface;
use App\Domain\Identity\Repository\UserRepositoryInterface;
use App\Domain\Ticketing\Repository\TicketCommentRepositoryInterface;
use App\Domain\Ticketing\Repository\TicketRepositoryInterface;
use Strux\Auth\Middleware\AuthorizationMiddleware;
use Strux\Component\Attributes\Authorize;
use Strux\Component\Attributes\Middleware;
use Strux\Component\Attributes\Prefix;
use Strux\Component\Attributes\Route;
use Strux\Component\Http\Controller\Web\Controller;
use Strux\Component\Http\Request;
use Strux\Component\Http\Response;
use Strux\Component\Validation\Rules\Required;
use Strux\Component\Validation\Validator;

#[Prefix('/admin/users')]
#[Middleware([AuthorizationMiddleware::class])]
#[Authorize(roles: [Roles::ADMIN->value])]
class AdminUserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface          $userRepo,
        private readonly TicketRepositoryInterface        $ticketRepo,
        private readonly TicketCommentRepositoryInterface $commentRepo,
        private readonly CustomerRepositoryInterface      $customerRepo
    )
    {
        parent::__construct();
    }

    #[Route('/', name: 'admin.users')]
    #[Authorize(permissions: [Permissions::VIEW_USERS->value])]
    public function index(Request $request): Response
    {
        $page = max(1, (int)$request->input('page', 1));

        $paginator = $this->userRepo->listAll(
            page: $page,
            perPage: 6,
            query: $request->allQuery(),
            path: url('/' . prefix() . '/users')
        );

        return $this->view('users/index', [
            'title' => 'Users',
            'users' => $paginator->items,
            'paginator' => $paginator
        ]);
    }

    #[Route('/create', name: 'admin.users.create')]
    #[Authorize(permissions: [Permissions::MANAGE_USERS->value])]
    public function create(): Response
    {
        return $this->view('users/create', [
            'title' => 'Create User'
        ]);
    }

    #[Route('/store', methods: ['POST'], name: 'admin.users.store')]
    #[Authorize(permissions: [Permissions::MANAGE_USERS->value])]
    public function store(Request $request): Response
    {
        $validator = new Validator($request->all());
        $validator->add('firstname', [new Required()]);
        $validator->add('lastname', [new Required()]);
        $validator->add('email', [new Required()]);
        $validator->add('role', [new Required()]);
        $validator->add('password', [new Required()]);

        if (!$validator->isValid()) {
            return $this->redirectWith(
                $request->getRefer(),
                ['errors' => $validator->getErrors()]
            );
        }

        $email = strtolower($request->input('email'));

        if ($this->userRepo->emailExists($email)) {
            return $this->redirectWith(
                $request->getRefer(),
                ['errors' => ['email' => ['Email address already exists.']]]
            );
        }

        $userData = [
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'email' => $email,
            'role' => $request->input('role'),
            'password' => password_hash(
                $request->input('password'),
                PASSWORD_DEFAULT
            )
        ];

        $user = $this->userRepo->create($userData);

        if ($request->input('role') === Roles::CUSTOMER->value) {
            $customerData = [
                'userID' => $user->userID,
                'customerName' => $user->firstname . ' ' . $user->lastname,
                'phone' => null,
                'address' => null,
                'status' => 'Active'
            ];
            $this->customerRepo->create($customerData);
        }

        return $this->redirectWith(
            '/' . prefix() . '/users',
            ['success' => "User '{$user->firstname}' created successfully."]
        );
    }

    #[Route('/edit/int:id', name: 'admin.users.edit')]
    #[Authorize(permissions: [Permissions::MANAGE_USERS->value])]
    public function edit(int $id, Request $request): Response
    {
        // Fetch user
        $user = $this->userRepo->find($id);
        if (!$user) {
            return $this->redirectWith('/' . prefix() . '/users', ['error' => 'User not found.']);
        }

        // Pagination inputs
        $page = max(1, (int)$request->input('page', 1));
        $perPage = max(5, min(50, (int)$request->input('per_page', 6)));

        $ticketStats = [
            'total' => 0,
            'open' => 0,
            'closed' => 0,
            'assigned' => 0,
            'recent_activity' => 0,
        ];

        if ($user->isCustomer()) {
            $customerId = $user->getCustomerId();
            $ticketsPaginator = $this->ticketRepo->listForCustomer($customerId, $page, $perPage, [], url('/' . prefix() . '/users/edit/' . $user->userID));
            $ticketStats['total'] = $this->ticketRepo->countForCustomer($customerId);
            $ticketStats['open'] = $this->ticketRepo->countForCustomerActive($customerId);
            $ticketStats['recent_activity'] = (int)$this->commentRepo->recentOthersComments(5, $customerId, null, $user->role) ? 1 : 0;
            $recentComments = $this->commentRepo->recentOthersComments(5, $customerId, null, $user->role);
        } elseif ($user->isAgent()) {
            $agentId = $user->getAgentId();
            $ticketsPaginator = $this->ticketRepo->listForAgent($agentId, $page, $perPage, [], url('/' . prefix() . '/users/edit/' . $user->userID));
            $ticketStats['assigned'] = $this->ticketRepo->countAssignedTo($agentId);
            $ticketStats['pending'] = $this->ticketRepo->countForAgentPending($agentId);
            $ticketStats['resolved_this_month'] = $this->ticketRepo->countForAgentResolvedThisMonth($agentId);
            $recentComments = $this->commentRepo->recentOthersComments(5, null, $agentId, $user->role);
        } else {
            $ticketsPaginator = $this->ticketRepo->listAll($page, $perPage, [], url('/' . prefix() . '/users/edit/' . $user->userID));
            $ticketStats['total'] = $this->ticketRepo->countAll();
            $ticketStats['open'] = $this->ticketRepo->countOpen();
            $recentComments = $this->commentRepo->recentOthersComments(5, null, null, $user->role);
        }

        return $this->view('users/edit', [
            'title' => 'Edit: ' . ($user->firstname ?: $user->email),
            'user' => $user,
            'ticketsPaginator' => $ticketsPaginator,
            'ticketStats' => $ticketStats,
            'recentComments' => $recentComments,
            'page' => $page,
            'perPage' => $perPage,
            'errors' => $this->flash->get('errors') ?? null,
        ]);
    }

    #[Route('/update/int:id', methods: ['PUT'], name: 'admin.users.update')]
    #[Authorize(permissions: [Permissions::MANAGE_USERS->value])]
    public function update(int $id, Request $request): Response
    {
        $user = $this->userRepo->find($id);

        if (!$user) {
            return $this->redirectWith(
                '/' . prefix() . '/users',
                ['error' => 'User not found.']
            );
        }

        $email = strtolower($request->input('email', $user->email));

        if ($email !== $user->email) {
            if ($this->userRepo->emailExists($email, $user->userID)) {
                return $this->redirectWith(
                    $request->getRefer(),
                    ['errors' => ['email' => ['Email address already exists.']]]
                );
            }

            $user->email = $email;
        }

        $user->firstname = $request->input('firstname', $user->firstname);
        $user->lastname = $request->input('lastname', $user->lastname);
        $user->role = $request->input('role', $user->role);

        if ($newPassword = $request->input('password')) {
            $currentPassword = $request->input('current_password');

            if (!$currentPassword || !$user->verifyPassword($currentPassword, $user->password)) {
                return $this->redirectWith(
                    $request->getRefer(),
                    ['errors' => ['current_password' => ['Current password is incorrect.']]]
                );
            }

            $user->setPassword($newPassword);
        }

        $this->userRepo->save($user);

        return $this->redirectWith(
            '/' . prefix() . '/users',
            ['success' => "User '{$user->firstname}' updated successfully."]
        );
    }

    #[Route('/delete/int:id', methods: ['DELETE'], name: 'admin.users.delete')]
    #[Authorize(permissions: [Permissions::DELETE_USERS->value])]
    public function delete(int $id): Response
    {
        $user = $this->userRepo->find($id);

        if (!$user) {
            return $this->redirectWith(
                '/' . prefix() . '/users',
                ['error' => 'User not found.']
            );
        }

        $name = $user->firstname ?? 'Unknown';

        $this->userRepo->delete($user);

        return $this->redirectWith(
            '/' . prefix() . '/users',
            ['success' => "User '$name' deleted successfully."]
        );
    }
}