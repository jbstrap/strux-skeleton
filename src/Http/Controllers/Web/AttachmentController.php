<?php
declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Ticketing\Repository\TicketAttachmentRepositoryInterface;
use App\Domain\Ticketing\Repository\TicketRepositoryInterface;
use Strux\Auth\Auth;
use Strux\Auth\Middleware\AuthorizationMiddleware;
use Strux\Component\Attributes\Middleware;
use Strux\Component\Attributes\Prefix;
use Strux\Component\Attributes\Route;
use Strux\Component\Http\Controller\Web\Controller;

#[Prefix('/attachments')]
#[Middleware([AuthorizationMiddleware::class, AuthorizationMiddleware::class])]
class AttachmentController extends Controller
{
    private TicketAttachmentRepositoryInterface $attachmentRepo;
    private TicketRepositoryInterface $ticketRepo;

    public function __construct(
        TicketAttachmentRepositoryInterface $attachmentRepo,
        TicketRepositoryInterface           $ticketRepo
    )
    {
        $this->attachmentRepo = $attachmentRepo;
        $this->ticketRepo = $ticketRepo;
        parent::__construct();
    }

    #[Route('/download/:filename', methods: ['GET'], name: 'attachments.download')]
    public function download(string $filename): void
    {
        $user = Auth::user();

        $attachment = $this->attachmentRepo->findByFileName($filename);
        if (!$attachment) {
            http_response_code(404);
            echo "File not found.";
            exit;
        }

        // Security check: ensure user can access the ticket this attachment belongs to.
        // We assume TicketAttachment has commentID -> TicketComment -> ticketID relationships.
        // If your ORM has direct relations we can eager-load; else fetch related models.

        $comment = \App\Domain\Ticketing\Entity\TicketComment::find($attachment->commentID);
        if (!$comment) {
            http_response_code(404);
            echo "File not found.";
            exit;
        }

        $ticket = $this->ticketRepo->findByIdWithRelations($comment->ticketID);
        if (!$ticket) {
            http_response_code(404);
            echo "File not found.";
            exit;
        }

        // Authorization: allow if admin, agent assigned, or owning customer
        $canAccess = false;
        if ($user->isAdmin()) {
            $canAccess = true;
        } elseif ($user->isAgent() && $ticket->assignedTo == $user->getAgentId()) {
            $canAccess = true;
        } elseif ($user->isCustomer() && $ticket->customerID == $user->getCustomerId()) {
            $canAccess = true;
        }

        if (!$canAccess) {
            http_response_code(403);
            echo "Forbidden.";
            exit;
        }

        $path = storage_path('app/web/attachments/' . $attachment->filePath);
        if (!file_exists($path)) {
            http_response_code(404);
            echo "File not found.";
            exit;
        }

        $mimeType = mime_content_type($path);
        $fileSize = filesize($path);
        if (ob_get_level()) ob_end_clean();

        header("Content-Type: $mimeType");
        header("Content-Length: $fileSize");
        header("Content-Disposition: inline; filename=\"" . basename($path) . "\"");
        header("Cache-Control: private, max-age=0, must-revalidate");
        header("Pragma: public");
        readfile($path);
        exit;
    }
}