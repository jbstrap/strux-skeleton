<?php
declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Identity\Entity\User;
use App\Domain\Identity\Enums\Permissions;
use App\Domain\Identity\Enums\Roles;
use App\Domain\Ticketing\Entity\Ticket;
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

#[Prefix('/customer')]
#[Middleware([AuthorizationMiddleware::class])]
#[Authorize(roles: [Roles::CUSTOMER->value])]
class CustomerTicketController extends Controller
{
    public function __construct(
        private readonly TicketRepositoryInterface           $ticketRepo,
        private readonly TicketCommentRepositoryInterface    $commentRepo,
        private readonly TicketAttachmentRepositoryInterface $attachmentRepo,
        private readonly DepartmentRepositoryInterface       $departmentRepo,
        private readonly PriorityRepositoryInterface         $priorityRepo
    )
    {
        parent::__construct();
    }

    #[Route(path: '/', name: 'customer.dashboard')]
    #[Route(path: '/dashboard', name: 'customer.dashboard')]
    public function index(): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $customerId = $user->getCustomerId();

        $stats = [
            'my_tickets' => $this->ticketRepo->countForCustomer($customerId),
            'active_tickets' => $this->ticketRepo->countForCustomerActive($customerId),
            'resolved_tickets' => Ticket::query()
                ->where('customerID', $customerId)
                ->join('ticket_status', 'tickets.statusID', '=', 'ticket_status.statusID')
                ->where('ticket_status.statusName', 'Closed')
                ->count(),
            'recent_activity' => 5,
        ];

        $recentTickets = $this->ticketRepo->listForCustomer($customerId, 5);

        $recentComments = $this->commentRepo->recentOthersComments(5, $customerId, null, $user->role);

        return $this->view('account/dashboard', [
            'title' => 'Dashboard',
            'user' => $user,
            'stats' => $stats,
            'recentTickets' => $recentTickets->items,
            'recentComments' => $recentComments
        ]);
    }

    #[Route('/tickets', name: 'customer.tickets')]
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
        $paginator = $this->ticketRepo->listForCustomer($user->getCustomerId(), $page, $perPage, $filters, $path);

        return $this->view('account/index', [
            'title' => 'Tickets',
            'paginator' => $paginator,
            'tickets' => $paginator->items
        ]);
    }

    #[Route(path: '/tickets/create', name: 'customer.tickets.create')]
    #[Authorize(permissions: [Permissions::CREATE_TICKETS->value])]
    public function create(): Response
    {
        $departments = $this->departmentRepo->all();
        $priorities = $this->priorityRepo->all();
        return $this->view('account/create', [
            'title' => 'Create Ticket',
            'departments' => $departments,
            'priorities' => $priorities,
            'customers' => []
        ]);
    }

    #[Route(path: '/tickets/store', methods: ['POST'], name: 'customer.tickets.store')]
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

        if (!$user->isCustomer()) {
            $validator->add('customerID', [new Required()]);
        }

        if (!$validator->isValid()) {
            return $this->redirectWith($this->request->getRefer(), [
                "errors" => $validator->getErrors()
            ]);
        }

        try {
            $ticketData = [
                'ticketID' => Utils::generateId(upperChars: false, lowerChars: false),
                'customerID' => ($user->isAdmin() && $request->input('customerID', type: 'int')) ? $request->input("customerID", type: 'int') : $user->getCustomerId(),
                'subject' => $request->input('subject'),
                'departmentID' => $request->input('departmentID', type: 'int'),
                'priorityID' => $request->input('priorityID', type: 'int'),
                'assignedTo' => $request->input('assignedTo', type: 'int') ?: null,
                'statusID' => 1
            ];

            $ticket = $this->ticketRepo->create($ticketData);

            // create initial comment
            $authorRole = $user->hasRole('Admin') ? 'Admin' : $user->role;
            $comment = $this->commentRepo->createForTicket($ticket->ticketID, $authorRole, $request->input('replyMessage'));

            // attachments
            $files = $request->file('attachment');
            if (!is_array($files)) $files = $files ? [$files] : [];
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('attachments', 'web');
                    $this->attachmentRepo->createForComment($comment->commentID, $file->getClientOriginalName(), $path);
                }
            }

            return $this->redirectWith('/' . prefix() . '/tickets/success/' . $ticket->ticketID, [
                "success" => "Ticket created successfully."
            ]);
        } catch (\Throwable $e) {
            $this->flash->set("error", "System Error: " . $e->getMessage());
            return $this->view('account/create', ['title' => 'Create Ticket']);
        }
    }

    #[Route('/tickets/view/int:id', name: 'customer.tickets.view')]
    public function show(int $id): Response
    {
        $user = Auth::user();
        $ticket = $this->ticketRepo->findByIdWithRelations($id);
        if (!$ticket || ($user->isCustomer() && $ticket->customerID != $user->getCustomerId())) {
            return $this->redirectWith('/' . prefix() . '/tickets', ["error" => "Ticket not found or access denied."]);
        }
        return $this->view('account/view', ['title' => 'Ticket #' . $ticket->ticketID, 'ticket' => $ticket]);
    }

    #[Route('/tickets/reply/int:id', methods: ['POST'], name: 'customer.tickets.reply')]
    #[Authorize(permissions: [Permissions::COMMENT_TICKETS->value])]
    public function reply(int $id, Request $request): Response
    {
        $user = Auth::user();
        $ticket = $this->ticketRepo->findByIdWithRelations($id);
        if (!$ticket || ($user->isCustomer() && $ticket->customerID != $user->getCustomerId())) {
            return $this->redirectWith('/' . prefix() . '/tickets', ["error" => "Ticket not found."]);
        }

        $validator = new Validator($request->all());
        $validator->add('replyMessage', [new Required()]);
        if (!$validator->isValid()) {
            return $this->redirectWith('/' . prefix() . "/tickets/view/$id", ["error" => "Message cannot be empty."]);
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

        if ($user->isCustomer() && $ticket->statusID === 3) {
            $ticket->statusID = 1;
            $this->ticketRepo->save($ticket);
        }

        return $this->redirectWith('/' . prefix() . "/tickets/view/$id", ["success" => "Reply posted successfully."]);
    }

    #[Route('/tickets/close/int:id', methods: ['POST'], name: 'agent.tickets.close')]
    #[Authorize(permissions: [Permissions::CHANGE_STATUS->value])]
    public function close(int $id): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $ticket = $this->ticketRepo->findByIdWithRelations($id);
        if (!$ticket || ($user->isCustomer() && $ticket->customerID != $user->getCustomerId())) {
            return $this->redirectWith('/' . prefix() . '/tickets', ['error' => 'Ticket not found.']);
        }

        $ticket->statusID = 3;
        $this->ticketRepo->save($ticket);

        return $this->redirectWith('/' . prefix() . "/tickets/view/$id", ['success' => 'Ticket closed successfully.']);
    }

    #[Route('/tickets/delete/int:id', methods: ['DELETE', 'POST'], name: 'customer.tickets.delete')]
    #[Authorize(permissions: [Permissions::DELETE_TICKETS->value])]
    public function delete(int $id): Response
    {
        $user = Auth::user();
        $ticket = $this->ticketRepo->findByIdWithRelations($id);
        if (!$ticket || ($user->isCustomer() && $ticket->customerID != $user->getCustomerId())) {
            return $this->redirectWith('/' . prefix() . '/tickets', ["error" => "Ticket not found."]);
        }
        $this->ticketRepo->delete($ticket);
        return $this->redirectWith('/' . prefix() . '/tickets', ["success" => "Ticket #$id deleted successfully."]);
    }

    #[Route('/tickets/success/int:id')]
    public function success(int $id): Response
    {
        return $this->view('success', ['title' => 'Success']);
    }
}