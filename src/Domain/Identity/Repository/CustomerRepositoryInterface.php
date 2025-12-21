<?php

declare(strict_types=1);

namespace App\Domain\Identity\Repository;

use App\Domain\Identity\Entity\Customer;
use Strux\Support\Collection;

interface CustomerRepositoryInterface
{
    /** @return Collection|Customer[] */
    public function listAll(): Collection|Customer;

    public function create(array $data): Customer;

    public function save(Customer $customer): void;

    public function delete(Customer $customer): void;
}