<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\Security;

use App\Domain\Identity\Entity\User;
use App\Domain\Ticketing\Entity\Ticket;

class TicketAuthority
{
    /**
     * Determine if the user can view the ticket.
     */
    public function canView(User $user, Ticket $ticket): bool
    {
        // A user can view a ticket if they are an admin or if it's their own ticket.
        return $user->isAdmin() || $user->getCustomerId() === $ticket->customerID;
    }

    /**
     * Determine if the user can delete the ticket.
     */
    public function canDelete(User $user, Ticket $ticket): bool
    {
        // Only admins can delete tickets.
        return $user->isAdmin();
    }
}