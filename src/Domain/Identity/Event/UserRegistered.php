<?php

declare(strict_types=1);

namespace App\Domain\Identity\Event;

use App\Domain\Identity\Entity\User;
use Psr\EventDispatcher\StoppableEventInterface;

class UserRegistered implements StoppableEventInterface
{
    private bool $propagationStopped = false;
    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Allows listeners to stop the event from propagating to other listeners.
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }
}
