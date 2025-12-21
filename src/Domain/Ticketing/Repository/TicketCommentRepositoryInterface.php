<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\Repository;

use App\Domain\Ticketing\Entity\TicketComment;
use Strux\Support\Collection;

interface TicketCommentRepositoryInterface
{
    public function createForTicket(string|int $ticketId, string $authorRole, string $message): TicketComment;

    public function recentOthersComments(int $limit = 5, ?int $customerId = null, ?int $agentId = null, ?string $excludeRole = null): Collection;
}