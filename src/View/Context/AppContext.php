<?php

declare(strict_types=1);

namespace App\View\Context;

use App\Domain\Identity\Entity\User;
use App\Domain\Ticketing\Entity\Ticket;
use Strux\Auth\Auth;
use Strux\Component\Config\Config;
use Strux\Component\View\Context\ContextInterface;

class AppContext implements ContextInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function process(): array
    {
        $ticketCount = 0;

        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();

            if ($user->isAdmin() || $user->isAgent()) {
                $ticketCount = Ticket::query()->count();
            } elseif ($user->isCustomer()) {
                $customerId = $user->getCustomerId();
                if ($customerId) {
                    $ticketCount = Ticket::query()->where('customerID', $customerId)->count();
                }
            }
        }

        return [
            'appName' => $this->config->get('app.name', 'Strux'),
            'appEnv' => $this->config->get('app.env', 'production'),
            'globalTicketCount' => $ticketCount
        ];
    }
}