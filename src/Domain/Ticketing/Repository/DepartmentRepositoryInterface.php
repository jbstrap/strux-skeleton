<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\Repository;

use App\Domain\Ticketing\Entity\Department;
use Strux\Support\Collection;

interface DepartmentRepositoryInterface
{
    /** @return Collection|Department[] */
    public function all(): Collection|Department;
}
