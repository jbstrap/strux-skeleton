<?php

declare(strict_types=1);

namespace App\Domain\Identity\Entity;

use App\Domain\Ticketing\Entity\Ticket;
use Strux\Component\Database\Attributes\Column;
use Strux\Component\Database\Attributes\Id;
use Strux\Component\Database\Attributes\Table;
use Strux\Component\Database\Types\Field;
use Strux\Component\Database\Types\KeyAction;
use Strux\Component\Model\Attributes\BelongsTo;
use Strux\Component\Model\Attributes\HasMany;
use Strux\Component\Model\Model;
use Strux\Support\Collection;

#[Table(name: 'customers')]
class Customer extends Model
{
    #[Id, Column(type: Field::bigInteger)]
    public ?int $customerID = null;

    #[Column(type: Field::bigInteger)]
    public ?int $userID = null;

    #[Column]
    public ?string $customerName = null;

    #[Column]
    public ?string $phone = null;

    #[Column]
    public ?string $address = null;

    #[Column(name: 'accountStatus')]
    public ?string $status = null;

    #[BelongsTo(
        User::class, 'userID', 'userID',
        onDelete: KeyAction::CASCADE, onUpdate: KeyAction::NO_ACTION
    )]
    public ?User $user = null;

    #[HasMany(Ticket::class, 'customerID', 'customerID')]
    public Collection $tickets;

    public function __construct(array $attributes = [])
    {
        $this->tickets = new Collection();
        parent::__construct($attributes);
    }
}