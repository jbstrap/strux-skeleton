<?php

declare(strict_types=1);

namespace App\Domain\Identity\Entity;

use Strux\Component\Database\Attributes\Column;
use Strux\Component\Database\Attributes\Id;
use Strux\Component\Database\Attributes\Table;
use Strux\Component\Database\Attributes\Unique;
use Strux\Component\Database\Types\Field;
use Strux\Component\Model\Attributes\BelongsToMany;
use Strux\Component\Model\Model;
use Strux\Support\Collection;

#[Table('permissions')]
class Permission extends Model
{
    #[Id, Column(type: Field::bigInteger)]
    public ?int $permissionID = null;

    #[Column]
    public string $name;

    #[Column, Unique]
    public string $slug;

    #[BelongsToMany(related: Role::class)]
    public Collection $roles;

    public function __construct(array $attributes = [])
    {
        $this->roles = new Collection();
        parent::__construct($attributes);
    }
}