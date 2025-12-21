<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\Entity;

use Strux\Component\Database\Attributes\Column;
use Strux\Component\Database\Attributes\Id;
use Strux\Component\Database\Attributes\Table;
use Strux\Component\Database\Attributes\Unique;
use Strux\Component\Database\Types\Field;
use Strux\Component\Model\Attributes\HasMany;
use Strux\Component\Model\Model;
use Strux\Support\Collection;

#[Table(name: 'departments')]
class Department extends Model
{
    #[Id, Column(type: Field::bigInteger)]
    public ?int $departmentID;

    #[Column]
    #[Unique]
    public string $departmentName;

    #[HasMany(Ticket::class, 'departmentID', 'departmentID')]
    public Collection $tickets;

    public function __construct(array $attributes = [])
    {
        $this->tickets = new Collection();
        parent::__construct($attributes);
    }
}