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

#[Prefix('/agent')]
#[Middleware([AuthorizationMiddleware::class])]
#[Authorize(roles: [Roles::AGENT->value])]
class AgentTicketController extends Controller
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

    #[Route(path: '/', name: 'agent.dashboard')]
    #[Route(path: '/dashboard', name: 'agent.dashboard')]
    public function index(): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $agentId = $user->getAgentId();

        $stats = [
            'assigned_tickets' => $this->ticketRepo->countAssignedTo($agentId),
            'pending_action' => $this->ticketRepo->countForAgentPending($agentId),
            'resolved_this_month' => $this->ticketRepo->countForAgentResolvedThisMonth($agentId),
            'avg_rating' => '4.8'
        ];

        $recentTickets = $this->ticketRepo->listForAgent($agentId, 5);
        $recentComments = $this->commentRepo->recentOthersComments(5, null, $agentId, $user->role);

        return $this->view('account/dashboard', [
            'title' => 'Dashboard',
            'user' => $user,
            'stats' => $stats,
            'recentTickets' => $recentTickets->items,
            'recentComments' => $recentComments
        ]);
    }

    #[Route('/tickets', name: 'agent.tickets')]
    #[Authorize(permissions: [Permissions::VIEW_ALL_TICKETS->value])]
    public function tickets(Request $request): Response
    {
        /* @var User $user */
        $user = Auth::user();

        $page = max(1, $request->input('page', 1, type: 'int'));
        $perPage = max(1, min(50, $request->input('per_page', 6, type: 'int')));
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'department' => $request->input('department'),
        ];

        $path = url('/' . prefix() . '/tickets'); // base path for page links
        $paginator = $this->ticketRepo->listAll($page, $perPage, $filters, $path);

        return $this->view('account/index', [
            'title' => 'Tickets',
            'paginator' => $paginator,
            'tickets' => $paginator->items
        ]);
    }

    #[Route('/tickets/assigned', name: 'agent.tickets.assigned')]
    #[Authorize(permissions: [Permissions::VIEW_ASSIGNED_TICKETS->value])]
    public function assignedTickets(Request $request): Response
    {
        /* @var User $user */
        $user = Auth::user();

        $page = max(1, $request->input('page', 1, type: 'int'));
        $perPage = max(1, min(50, $request->input('per_page', 6, type: 'int')));
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'department' => $request->input('department'),
        ];

        $path = url('/' . prefix() . '/tickets'); // base path for page links
        $paginator = $this->ticketRepo->listForAgent($user->getAgentId(), $page, $perPage, $filters, $path);

        return $this->view('account/index', [
            'title' => 'My Assigned Tickets',
            'paginator' => $paginator,
            'tickets' => $paginator->items
        ]);
    }

    #[Route(path: '/tickets/create', name: 'agent.tickets.create')]
    #[Authorize(permissions: [Permissions::CREATE_TICKETS->value])]
    public function create(): Response
    {
        // Agents can create tickets on behalf of customers
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

    #[Route(path: '/tickets/store', methods: ['POST'], name: 'agent.tickets.store')]
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
        // For agent, customerID is required (creating on behalf)
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
                'assignedTo' => $request->input('assignedTo', type: 'int') ?: $user->getAgentId(),
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

    #[Route('/tickets/view/int:id', name: 'agent.tickets.view')]
    public function show(int $id): Response
    {
        $user = Auth::user();
        $ticket = $this->ticketRepo->findByIdWithRelations($id);
        if (!$ticket || ($user->isAgent() && $ticket->assignedTo != $user->getAgentId())) {
            return $this->redirectWith('/' . prefix() . '/tickets', ['error' => 'Ticket not found or access denied.']);
        }
        return $this->view('account/view', ['title' => 'Ticket #' . $ticket->ticketID, 'ticket' => $ticket]);
    }

    #[Route('/tickets/reply/int:id', methods: ['POST'], name: 'agent.tickets.reply')]
    #[Authorize(permissions: [Permissions::COMMENT_TICKETS->value])]
    public function reply(int $id, Request $request): Response
    {
        $user = Auth::user();
        $ticket = $this->ticketRepo->findByIdWithRelations($id);
        if (!$ticket || ($user->isAgent() && $ticket->assignedTo != $user->getAgentId())) {
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

        // Optionally update ticket status if business rules apply
        return $this->redirectWith('/' . prefix() . "/tickets/view/$id", ['success' => 'Reply posted successfully.']);
    }

    #[Route('/tickets/close/int:id', methods: ['POST'], name: 'agent.tickets.close')]
    #[Authorize(permissions: [Permissions::CHANGE_STATUS->value])]
    public function close(int $id): Response
    {
        $user = Auth::user();
        $ticket = $this->ticketRepo->findByIdWithRelations($id);
        if (!$ticket || ($user->isAgent() && $ticket->assignedTo != $user->getAgentId())) {
            return $this->redirectWith('/' . prefix() . '/tickets', ['error' => 'Ticket not found.']);
        }

        $ticket->statusID = 3;
        $this->ticketRepo->save($ticket);

        return $this->redirectWith('/' . prefix() . "/tickets/view/$id", ['success' => 'Ticket closed successfully.']);
    }

    #[Route('/tickets/delete/int:id', methods: ['DELETE', 'POST'], name: 'agent.tickets.delete')]
    #[Authorize(permissions: [Permissions::DELETE_TICKETS->value])]
    public function delete(int $id): Response
    {
        $user = Auth::user();
        $ticket = $this->ticketRepo->findByIdWithRelations($id);
        if (!$ticket || ($user->isAgent() && $ticket->assignedTo != $user->getAgentId())) {
            return $this->redirectWith('/' . prefix() . '/tickets', ['error' => 'Ticket not found.']);
        }

        $this->ticketRepo->delete($ticket);
        return $this->redirectWith('/' . prefix() . '/tickets', ['success' => "Ticket #$id deleted successfully."]);
    }
}