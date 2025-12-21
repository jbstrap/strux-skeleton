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
use Strux\Component\Model\Model;

#[Table(name: 'ticket_attachments')]
class TicketAttachment extends Model
{
    #[Id, Column(type: Field::bigInteger)]
    public ?int $attachmentID = null;

    #[Column(type: Field::bigInteger)]
    public int $commentID;

    #[Column(type: Field::string, nullable: true)]
    public ?string $fileName = null;

    #[Column(type: Field::string, length: 500, nullable: true)]
    public ?string $filePath = null;

    #[Column(
        type: Field::timestamp,
        nullable: true,
        currentTimestamp: true
    )]
    public ?DateTime $uploadedAt = null;

    #[BelongsTo(
        TicketComment::class, 'commentID', 'commentID',
        onDelete: KeyAction::CASCADE, onUpdate: KeyAction::NO_ACTION
    )]
    public ?TicketComment $comment = null;
}