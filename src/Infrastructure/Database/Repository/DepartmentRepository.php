<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repository;

use App\Domain\Ticketing\Entity\Department;
use App\Domain\Ticketing\Repository\DepartmentRepositoryInterface;
use Strux\Support\Collection;

class DepartmentRepository implements DepartmentRepositoryInterface
{
    public function all(): Collection|Department
    {
        return Department::query()->all();
    }
}