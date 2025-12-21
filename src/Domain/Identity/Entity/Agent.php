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

#[Table(name: 'agents')]
class Agent extends Model
{
    #[Id, Column(type: Field::bigInteger)]
    public ?int $agentID = null;

    #[Column(type: Field::bigInteger)]
    public ?int $userID = null;

    #[Column]
    public ?string $agentName = null;

    #[Column]
    public ?string $skillset = null;

    #[Column]
    public ?string $availability = null;

    #[BelongsTo(
        User::class, 'userID', 'userID',
        onDelete: KeyAction::CASCADE, onUpdate: KeyAction::NO_ACTION
    )]
    public ?User $user = null;

    #[HasMany(Ticket::class, 'assignedTo', 'agentID')]
    public Collection $tickets;

    public function __construct(array $attributes = [])
    {
        $this->tickets = new Collection();
        parent::__construct($attributes);
    }
}