<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repository;

use App\Domain\Ticketing\Entity\Ticket;
use App\Domain\Ticketing\Repository\TicketRepositoryInterface;
use Strux\Component\Database\Paginator;
use Strux\Support\Collection;

class TicketRepository implements TicketRepositoryInterface
{
    public function findByIdWithRelations(int|string $id): ?Ticket
    {
        return Ticket::query()
            ->with('status', 'priority', 'department', 'customer', 'agent', 'comments', 'comments.attachments')
            ->where('ticketID', $id)
            ->first();
    }

    public function listRecent(int $limit = 5): Collection
    {
        return Ticket::query()
            ->with('status', 'priority', 'department', 'customer')
            ->orderBy('createdAt', 'DESC')
            ->limit($limit)
            ->all();
    }

    public function listForCustomer(int $customerId, int $page = 1, int $perPage = 20, array $query = [], string $path = '/'): Paginator
    {
        return Ticket::query()
            ->with('status', 'priority', 'department', 'customer', 'customer.user', 'agent', 'agent.user')
            ->where('tickets.customerID', $customerId)
            ->orderBy('tickets.createdAt', 'DESC')
            ->paginate($perPage);
    }

    public function listForAgent(int $agentId, int $page = 1, int $perPage = 20, array $query = [], string $path = '/'): Paginator
    {
        return Ticket::query()
            ->with('status', 'priority', 'department', 'customer', 'customer.user', 'agent', 'agent.user')
            ->where('tickets.assignedTo', $agentId)
            ->orderBy('tickets.createdAt', 'DESC')
            ->paginate($perPage);
    }

    public function listAll(int $page = 1, int $perPage = 20, array $query = [], string $path = '/'): Paginator
    {
        return Ticket::query()
            ->with('status', 'priority', 'department', 'customer', 'customer.user', 'agent', 'agent.user')
            ->orderBy('tickets.createdAt', 'DESC')
            ->paginate(perPage: $perPage, page: $page, path: $path, query: $query);
    }

    public function create(array $data): Ticket
    {
        $ticket = new Ticket();
        foreach ($data as $k => $v) {
            $ticket->{$k} = $v;
        }
        $ticket->save();
        return $ticket;
    }

    public function save(Ticket $ticket): void
    {
        $ticket->save();
    }

    public function delete(Ticket $ticket): void
    {
        $ticket->delete();
    }

    public function countAll(): int
    {
        return Ticket::query()->count();
    }

    public function countOpen(): int
    {
        return Ticket::query()
            ->join('ticket_status', 'tickets.statusID', '=', 'ticket_status.statusID')
            ->where('ticket_status.statusName', 'Open')
            ->count();
    }

    public function countAssignedTo(int $agentId): int
    {
        return Ticket::query()->where('assignedTo', $agentId)->count();
    }

    public function countForCustomer(int $customerId): int
    {
        return Ticket::query()->where('customerID', $customerId)->count();
    }

    public function countForCustomerActive(int $customerId): int
    {
        return Ticket::query()
            ->where('customerID', $customerId)
            ->join('ticket_status', 'tickets.statusID', '=', 'ticket_status.statusID')
            ->where('ticket_status.statusName', '!=', 'Closed')
            ->count();
    }

    public function countForAgentPending(int $agentId): int
    {
        return Ticket::query()
            ->where('assignedTo', $agentId)
            ->join('ticket_status', 'tickets.statusID', '=', 'ticket_status.statusID')
            ->where('ticket_status.statusName', 'In Progress')
            ->count();
    }

    public function countForAgentResolvedThisMonth(int $agentId): int
    {
        return Ticket::query()
            ->where('assignedTo', $agentId)
            ->join('ticket_status', 'tickets.statusID', '=', 'ticket_status.statusID')
            ->where('ticket_status.statusName', 'Closed')
            ->count();
    }
}