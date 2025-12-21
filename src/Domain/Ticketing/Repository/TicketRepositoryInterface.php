<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\Repository;

use App\Domain\Ticketing\Entity\Ticket;
use Strux\Support\Collection;
use Strux\Support\Paginator;

interface TicketRepositoryInterface
{
    public function findByIdWithRelations(int|string $id): ?Ticket;

    public function listRecent(int $limit = 5): Collection;

    public function listForCustomer(int $customerId, int $page = 1, int $perPage = 20, array $query = [], string $path = '/'): \Strux\Component\Database\Paginator;

    public function listForAgent(int $agentId, int $page = 1, int $perPage = 20, array $query = [], string $path = '/'): \Strux\Component\Database\Paginator;

    public function listAll(int $page = 1, int $perPage = 20, array $query = [], string $path = '/'): \Strux\Component\Database\Paginator;

    public function create(array $data): Ticket;

    public function save(Ticket $ticket): void;

    public function delete(Ticket $ticket): void;

    public function countAll(): int;

    public function countOpen(): int;

    public function countAssignedTo(int $agentId): int;

    public function countForCustomer(int $customerId): int;

    public function countForCustomerActive(int $customerId): int;

    public function countForAgentPending(int $agentId): int;

    public function countForAgentResolvedThisMonth(int $agentId): int;

    /*public function paginateForCustomer(int $customerId, int $page = 1, int $perPage = 20, array $query = [], string $path = '/'): Paginator;

    public function paginateForAgent(int $agentId, int $page = 1, int $perPage = 20, array $query = [], string $path = '/'): Paginator;

    public function paginateAll(int $page = 1, int $perPage = 20, array $query = [], string $path = '/'): Paginator;*/
}