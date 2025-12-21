<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\Repository;

use App\Domain\Ticketing\Entity\TicketPriority;
use Strux\Support\Collection;

interface PriorityRepositoryInterface
{
    /** @return Collection|TicketPriority[] */
    public function all(): Collection|TicketPriority;
}