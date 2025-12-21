<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repository;

use App\Domain\Ticketing\Entity\TicketAttachment;
use App\Domain\Ticketing\Repository\TicketAttachmentRepositoryInterface;

class TicketAttachmentRepository implements TicketAttachmentRepositoryInterface
{
    public function createForComment(int $commentId, string $fileName, string $filePath): TicketAttachment
    {
        $a = new TicketAttachment();
        $a->commentID = $commentId;
        $a->fileName = $fileName;
        $a->filePath = $filePath;
        $a->save();
        return $a;
    }

    public function findByFileName(string $filename): ?TicketAttachment
    {
        return TicketAttachment::query()->where('fileName', $filename)->first();
    }

    public function findById(int $id): ?TicketAttachment
    {
        return TicketAttachment::find($id);
    }
}