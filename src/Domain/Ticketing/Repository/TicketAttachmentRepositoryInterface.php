<?php
declare(strict_types=1);

namespace App\Domain\Ticketing\Repository;

use App\Domain\Ticketing\Entity\TicketAttachment;

interface TicketAttachmentRepositoryInterface
{
    public function createForComment(int $commentId, string $fileName, string $filePath): TicketAttachment;

    public function findByFileName(string $filename): ?TicketAttachment;

    public function findById(int $id): ?TicketAttachment;
}