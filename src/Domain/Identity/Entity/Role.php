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

#[Table('roles')]
class Role extends Model
{
    #[Id, Column(type: Field::bigInteger)]
    public ?int $roleID = null;

    #[Column]
    public string $name;

    #[Column, Unique]
    public string $slug;

    #[Column(nullable: true)]
    public ?string $description = null;

    #[BelongsToMany(related: User::class)]
    public Collection $users;

    #[BelongsToMany(related: Permission::class)]
    public Collection $permissions;

    public function __construct(array $attributes = [])
    {
        $this->users = new Collection();
        $this->permissions = new Collection();
        parent::__construct($attributes);
    }
}