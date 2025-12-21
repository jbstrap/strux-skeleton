<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\Entity;

use DateTime;
use Strux\Component\Database\Attributes\Column;
use Strux\Component\Database\Attributes\Id;
use Strux\Component\Database\Attributes\Table;
use Strux\Component\Database\Types\Field;
use Strux\Component\Database\Types\KeyAction;
use Strux\Component\Model\Attributes\BelongsTo;
use Strux\Component\Model\Attributes\HasMany;
use Strux\Component\Model\Model;
use Strux\Support\Collection;

#[Table(name: 'ticket_comments')]
class TicketComment extends Model
{
    #[Id, Column(type: Field::bigInteger)]
    public ?int $commentID = null;

    #[Column(type: Field::bigInteger)]
    public int $ticketID;

    #[Column(
        type: Field::enum, length: 10, default: 'Customer', enums: ['Admin', 'Agent', 'Customer']
    )]
    public ?string $authorRole = 'Agent';

    #[Column(type: Field::bigInteger, nullable: true)]
    public ?int $parentCommentID = null;

    #[Column(type: Field::text)]
    public string $message;

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

    #[BelongsTo(
        Ticket::class, 'ticketID', 'ticketID',
        onDelete: KeyAction::CASCADE, onUpdate: KeyAction::NO_ACTION
    )]
    public ?Ticket $ticket = null;

    #[HasMany(TicketAttachment::class, 'commentID', 'commentID')]
    public Collection $attachments;

    public function __construct(array $attributes = [])
    {
        $this->attachments = new Collection();
        parent::__construct($attributes);
    }
}