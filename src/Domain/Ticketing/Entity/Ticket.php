<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\Entity;

use App\Domain\Identity\Entity\Agent;
use App\Domain\Identity\Entity\Customer;
use DateTime;
use Strux\Component\Database\Attributes\Column;
use Strux\Component\Database\Attributes\Id;
use Strux\Component\Database\Attributes\OrderBy;
use Strux\Component\Database\Attributes\SoftDelete;
use Strux\Component\Database\Attributes\Table;
use Strux\Component\Database\Types\Field;
use Strux\Component\Database\Types\KeyAction;
use Strux\Component\Model\Attributes\BelongsTo;
use Strux\Component\Model\Attributes\HasMany;
use Strux\Component\Model\Model;
use Strux\Support\Collection;

#[Table(name: 'tickets')]
#[SoftDelete(column: 'deletedAt')]
class Ticket extends Model
{
    #[Id, Column(type: Field::bigInteger)]
    public ?int $ticketID = null;

    #[Column(type: Field::bigInteger, nullable: true)]
    public ?int $customerID = null;

    #[Column(type: Field::string, length: 255)]
    public string $subject;

    #[Column(type: Field::text)]
    public ?string $description = null;

    #[Column(type: Field::bigInteger, nullable: true)]
    public ?int $statusID = null;

    #[Column(type: Field::bigInteger, nullable: true)]
    public ?int $priorityID = null;

    #[Column(type: Field::bigInteger, nullable: true)]
    public ?int $assignedTo = null;

    #[Column(type: Field::bigInteger, nullable: true)]
    public ?int $departmentID = null;

    #[Column(
        type: Field::timestamp,
        nullable: true,
        currentTimestamp: true
    )]
    public ?DateTime $createdAt;

    #[Column(
        type: Field::timestamp,
        nullable: true,
        currentTimestamp: true,
        onUpdateCurrentTimestamp: true
    )]
    public ?DateTime $updatedAt = null;

    #[HasMany(TicketComment::class, 'ticketID', 'ticketID')]
    #[OrderBy('createdAt', 'DESC')]
    public Collection $comments;

    #[BelongsTo(
        Department::class, 'departmentID', 'departmentID',
        onDelete: KeyAction::CASCADE, onUpdate: KeyAction::NO_ACTION
    )]
    public ?Department $department = null;

    #[BelongsTo(
        TicketPriority::class, 'priorityID', 'priorityID',
        onDelete: KeyAction::CASCADE, onUpdate: KeyAction::NO_ACTION
    )]
    public ?TicketPriority $priority = null;

    #[BelongsTo(
        TicketStatus::class, 'statusID', 'statusID',
        onDelete: KeyAction::CASCADE, onUpdate: KeyAction::NO_ACTION
    )]
    public ?TicketStatus $status = null;

    #[BelongsTo(
        Customer::class, 'customerID', 'customerID',
        onDelete: KeyAction::CASCADE, onUpdate: KeyAction::NO_ACTION
    )]
    public ?Customer $customer = null;

    #[BelongsTo(
        Agent::class, 'assignedTo', 'agentID',
        onDelete: KeyAction::SET_NULL, onUpdate: KeyAction::NO_ACTION
    )]
    public ?Agent $agent = null;

    public function __construct(array $attributes = [])
    {
        $this->comments = new Collection();
        $this->createdAt = new DateTime('now');
        parent::__construct($attributes);
    }
}