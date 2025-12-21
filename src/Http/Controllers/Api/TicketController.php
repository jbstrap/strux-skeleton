<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Ticketing\Entity\Ticket;
use App\Http\Request\TicketCreateRequest;
use Strux\Component\Attributes\ApiController;
use Strux\Component\Attributes\ApiRoute;
use Strux\Component\Attributes\Cache;
use Strux\Component\Attributes\Consumes;
use Strux\Component\Attributes\Middleware;
use Strux\Component\Attributes\Prefix;
use Strux\Component\Attributes\Produces;
use Strux\Component\Attributes\RequestBody;
use Strux\Component\Attributes\ResponseHeader;
use Strux\Component\Attributes\ResponseStatus;
use Strux\Component\Http\ApiResponse;
use Strux\Component\Http\Controller\Api\Controller;
use Strux\Component\Middleware\ApiAuthMiddleware;

#[ApiController]
#[Prefix('/api/tickets')]
#[Produces('application/json')]
#[Consumes('application/json')]
#[Middleware([ApiAuthMiddleware::class])]
class TicketController extends Controller
{
    #[ApiRoute('/', methods: ['GET'], name: 'api.tickets.index')]
    #[ResponseHeader('X-App-Version', '1.5.0')]
    public function index(): ApiResponse
    {
        $tickets = Ticket::query()
            ->with('status')
            ->with('priority')
            ->with('department')
            ->with('customer')
            ->with('agent')
            ->with('comments')
            ->latest()
            ->limit(3)
            ->all();
        return $this->Ok($tickets);
    }

    #[ApiRoute('/int:id', methods: ['GET'], name: 'api.tickets.show')]
    #[ResponseHeader('X-App-Version', '1.5.0')]
    #[Cache(ttl: 60)]
    public function show(int $id): ApiResponse
    {
        $ticket = Ticket::query()
            ->with('status')
            ->with('priority')
            ->with('department')
            ->with('customer')
            ->with('agent')
            ->with('comments')
            ->find($id);

        if (!$ticket) {
            return $this->NotFound('Ticket not found.');
        }
        return $this->Ok($ticket);
    }

    #[ApiRoute('/', methods: ['POST'], name: 'api.tickets.store')]
    #[ResponseStatus(201)] // Override default 200 OK with 201 Created on success
    #[ResponseHeader('X-App-Version', '1.5.0')]
    public function store(#[RequestBody] TicketCreateRequest $request): ApiResponse
    {
        /*$data = request()->getJson();

        $ticket = new Ticket();
        $ticket->subject = $data->subject;
        $ticket->save();*/

        $ticket = new Ticket();
        $ticket->subject = $request->subject;
        $ticket->description = $request->description;
        $ticket->save();

        return $this->Created($request, 'Ticket created successfully.');
    }
}