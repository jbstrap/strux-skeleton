<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repository;

use App\Domain\Ticketing\Entity\TicketPriority;
use App\Domain\Ticketing\Repository\PriorityRepositoryInterface;
use Strux\Support\Collection;

class PriorityRepository implements PriorityRepositoryInterface
{
    public function all(): Collection|TicketPriority
    {
        return TicketPriority::query()->all();
    }
}