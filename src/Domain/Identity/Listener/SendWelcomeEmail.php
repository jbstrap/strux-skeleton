<?php

declare(strict_types=1);

namespace App\Domain\Identity\Listener;

use App\Domain\Identity\Event\UserRegistered;
use Exception;
use Psr\Log\LoggerInterface;
use Strux\Component\Mail\Mailer;
use Strux\Component\Queue\Queueable;

use Strux\Component\Queue\ShouldQueue;

class SendWelcomeEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Mailer          $mailer,
        private readonly LoggerInterface $logger
    )
    {
    }

    /**
     * @throws Exception
     */
    public function handle(UserRegistered $event): void
    {
        $this->logger->info("[Async Listener] Sending welcome email to: {$event->user->email}");

        try {
            $this->mailer->to($event->user->email, $event->user->firstname)
                ->send('emails/welcome', [
                    'user' => $event->user,
                    'subject' => 'Welcome!'
                ]);

            $this->logger->info("Welcome email sent successfully.");
        } catch (Exception $e) {
            $this->logger->error("Failed to send email", ['error' => $e->getMessage()]);
            // Throwing exception here allows the Queue Worker to retry the job
            throw $e;
        }
    }
}