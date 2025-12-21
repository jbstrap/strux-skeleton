<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repository;

use App\Domain\Ticketing\Entity\TicketComment;
use App\Domain\Ticketing\Repository\TicketCommentRepositoryInterface;
use Strux\Support\Collection;

class TicketCommentRepository implements TicketCommentRepositoryInterface
{
    public function createForTicket(string|int $ticketId, string $authorRole, string $message): TicketComment
    {
        $c = new TicketComment();
        $c->ticketID = $ticketId;
        $c->authorRole = $authorRole;
        $c->message = $message;
        $c->save();
        return $c;
    }

    public function recentOthersComments(int $limit = 5, ?int $customerId = null, ?int $agentId = null, ?string $excludeRole = null): Collection
    {
        $query = TicketComment::query()
            ->select('ticket_comments.*')
            ->with('ticket')
            ->with('ticket.customer')
            ->join('tickets', 'ticket_comments.ticketID', '=', 'tickets.ticketID')
            ->orderBy('ticket_comments.createdAt', 'DESC')
            ->limit($limit);

        if ($excludeRole !== null) {
            $query->where('ticket_comments.authorRole', '!=', $excludeRole);
        }
        if ($customerId !== null) {
            $query->where('tickets.customerID', $customerId);
        }
        if ($agentId !== null) {
            $query->where('tickets.assignedTo', $agentId);
        }

        return $query->all();
    }
}