<?php
declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Identity\Entity\User;
use App\Domain\Identity\Enums\Permissions;
use App\Domain\Identity\Enums\Roles;
use App\Domain\Identity\Repository\CustomerRepositoryInterface;
use App\Domain\Ticketing\Repository\DepartmentRepositoryInterface;
use App\Domain\Ticketing\Repository\PriorityRepositoryInterface;
use App\Domain\Ticketing\Repository\TicketAttachmentRepositoryInterface;
use App\Domain\Ticketing\Repository\TicketCommentRepositoryInterface;
use App\Domain\Ticketing\Repository\TicketRepositoryInterface;
use Strux\Auth\Auth;
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
use Strux\Support\Helpers\Utils;

#[Prefix('/admin')]
#[Middleware([AuthorizationMiddleware::class])]
#[Authorize(roles: [Roles::ADMIN->value], permissions: [Permissions::IMPERSONATE_USER->value])]
class AdminTicketController extends Controller
{
    public function __construct(
        private readonly TicketRepositoryInterface           $ticketRepo,
        private readonly TicketCommentRepositoryInterface    $commentRepo,
        private readonly TicketAttachmentRepositoryInterface $attachmentRepo,
        private readonly DepartmentRepositoryInterface       $departmentRepo,
        private readonly PriorityRepositoryInterface         $priorityRepo,
        private readonly CustomerRepositoryInterface         $customerRepo
    )
    {
        parent::__construct();
    }

    #[Route(path: '/', name: 'admin.dashboard')]
    #[Route(path: '/dashboard', name: 'admin.dashboard')]
    #[Authorize(permissions: [Permissions::MANAGE_TICKETS->value])]
    public function index(): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $stats = [
            'total_tickets' => $this->ticketRepo->countAll(),
            'open_tickets' => $this->ticketRepo->countOpen(),
            'total_agents' => User::query()->where('role', 'Agent')->count(),
            'total_customers' => User::query()->where('role', 'Customer')->count()
        ];

        $recentTickets = $this->ticketRepo->listRecent(5);
        $recentComments = $this->commentRepo->recentOthersComments(5, null, null, $user->role);

        return $this->view('account/dashboard', [
            'title' => 'Dashboard',
            'user' => $user,
            'stats' => $stats,
            'recentTickets' => $recentTickets,
            'recentComments' => $recentComments
        ]);
    }

    #[Route('/tickets', name: 'admin.tickets')]
    #[Authorize(permissions: [Permissions::VIEW_ALL_TICKETS->value])]
    public function tickets(Request $request): Response
    {
        $page = max(1, (int)$request->input('page', 1));
        $perPage = max(1, min(50, (int)$request->input('per_page', 6)));
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'department' => $request->input('department'),
        ];

        $path = url('/' . prefix() . '/tickets');
        $paginator = $this->ticketRepo->listAll($page, $perPage, $filters, $path);

        return $this->view('account/index', [
            'title' => 'Tickets',
            'paginator' => $paginator,
            'tickets' => $paginator->items
        ]);
    }

    #[Route(path: '/tickets/create', name: 'admin.tickets.create')]
    #[Authorize(permissions: [Permissions::CREATE_TICKETS->value])]
    public function create(): Response
    {
        $departments = $this->departmentRepo->all();
        $priorities = $this->priorityRepo->all();
        $customers = $this->customerRepo->listAll();

        return $this->view('account/create', [
            'title' => 'Create Ticket',
            'departments' => $departments,
            'priorities' => $priorities,
            'customers' => $customers
        ]);
    }

    #[Route(path: '/tickets/store', methods: ['POST'], name: 'admin.tickets.store')]
    #[Authorize(permissions: [Permissions::CREATE_TICKETS->value])]
    public function store(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $validator = new Validator($request->all());
        $validator->add('subject', [new Required()]);
        $validator->add('replyMessage', [new Required()]);
        $validator->add('departmentID', [new Required()]);
        $validator->add('priorityID', [new Required()]);
        $validator->add('customerID', [new Required()]);

        if (!$validator->isValid()) {
            return $this->redirectWith($this->request->getRefer(), [
                'errors' => $validator->getErrors()
            ]);
        }

        try {
            $ticketData = [
                'ticketID' => Utils::generateId(upperChars: false, lowerChars: false),
                'customerID' => $request->input('customerID', type: 'int'),
                'subject' => $request->input('subject'),
                'departmentID' => $request->input('departmentID', type: 'int'),
                'priorityID' => $request->input('priorityID', type: 'int'),
                'assignedTo' => $request->input('assignedTo', type: 'int') ?: null,
                'statusID' => 1
            ];

            $ticket = $this->ticketRepo->create($ticketData);

            $authorRole = $user->hasRole('Admin') ? 'Admin' : $user->role;
            $comment = $this->commentRepo->createForTicket($ticket->ticketID, $authorRole, $request->input('replyMessage'));

            $files = $request->file('attachment');
            if (!is_array($files)) $files = $files ? [$files] : [];
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('attachments', 'web');
                    $this->attachmentRepo->createForComment($comment->commentID, $file->getClientOriginalName(), $path);
                }
            }

            return $this->redirectWith('/' . prefix() . '/tickets/success/' . $ticket->ticketID, [
                'success' => 'Ticket created successfully.'
            ]);
        } catch (\Throwable $e) {
            $this->flash->set('error', 'System Error: ' . $e->getMessage());
            return $this->view('account/create', ['title' => 'Create Ticket']);
        }
    }

    #[Route('/tickets/view/int:id', name: 'admin.tickets.view')]
    #[Authorize(permissions: [Permissions::MANAGE_TICKETS->value])]
    public function show(int $id): Response
    {
        $ticket = $this->ticketRepo->findByIdWithRelations($id);
        if (!$ticket) {
            return $this->redirectWith('/' . prefix() . '/tickets', ['error' => 'Ticket not found or access denied.']);
        }
        return $this->view('account/view', ['title' => 'Ticket #' . $ticket->ticketID, 'ticket' => $ticket]);
    }

    #[Route('/tickets/reply/int:id', methods: ['POST'], name: 'admin.tickets.reply')]
    #[Authorize(permissions: [Permissions::COMMENT_TICKETS->value])]
    public function reply(int $id, Request $request): Response
    {
        $user = Auth::user();
        $ticket = $this->ticketRepo->findByIdWithRelations($id);
        if (!$ticket) {
            return $this->redirectWith('/' . prefix() . '/tickets', ['error' => 'Ticket not found.']);
        }

        $validator = new Validator($request->all());
        $validator->add('replyMessage', [new Required()]);
        if (!$validator->isValid()) {
            return $this->redirectWith('/' . prefix() . "/tickets/view/$id", ['error' => 'Message cannot be empty.']);
        }

        $authorRole = $user->hasRole('Admin') ? 'Admin' : $user->role;
        $comment = $this->commentRepo->createForTicket($ticket->ticketID, $authorRole, $request->input('replyMessage'));

        $files = $request->file('attachment');
        if (!is_array($files)) $files = $files ? [$files] : [];
        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $path = $file->store('attachments', 'web');
                $this->attachmentRepo->createForComment($comment->commentID, $file->getClientOriginalName(), $path);
            }
        }

        return $this->redirectWith('/' . prefix() . "/tickets/view/$id", ['success' => 'Reply posted successfully.']);
    }

    #[Route('/tickets/close/int:id', methods: ['POST'], name: 'admin.tickets.close')]
    #[Authorize(permissions: [Permissions::CHANGE_STATUS->value])]
    public function close(int $id): Response
    {
        $ticket = $this->ticketRepo->findByIdWithRelations($id);
        if (!$ticket) {
            return $this->redirectWith('/' . prefix() . '/tickets', ['error' => 'Ticket not found.']);
        }

        $ticket->statusID = 3;
        $this->ticketRepo->save($ticket);

        return $this->redirectWith('/' . prefix() . "/tickets/view/$id", ['success' => 'Ticket closed successfully.']);
    }

    #[Route('/tickets/delete/int:id', methods: ['DELETE', 'POST'], name: 'admin.tickets.delete')]
    #[Authorize(permissions: [Permissions::DELETE_USERS->value])]
    public function delete(int $id): Response
    {
        $ticket = $this->ticketRepo->findByIdWithRelations($id);
        if (!$ticket) {
            return $this->redirectWith('/' . prefix() . '/tickets', ['error' => 'Ticket not found.']);
        }

        $this->ticketRepo->delete($ticket);
        return $this->redirectWith('/' . prefix() . '/tickets', ['success' => "Ticket #$id deleted successfully."]);
    }
}