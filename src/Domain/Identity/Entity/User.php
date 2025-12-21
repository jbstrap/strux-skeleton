<?php

declare(strict_types=1);

namespace App\Domain\Identity\Entity;

use DateTime;
use Strux\Auth\Traits\WillAuthenticate;
use Strux\Component\Database\Attributes\Column;
use Strux\Component\Database\Attributes\Id;
use Strux\Component\Database\Attributes\Table;
use Strux\Component\Database\Attributes\Unique;
use Strux\Component\Database\Types\Field;
use Strux\Component\Model\Attributes\BelongsToMany;
use Strux\Component\Model\Attributes\HasOne;
use Strux\Component\Model\Model;
use Strux\Support\Collection;
use Strux\Support\ContainerBridge;

#[Table('accounts')]
class User extends Model
{
    use WillAuthenticate;

    #[Id, Column(type: Field::bigInteger)]
    public ?int $userID = null;

    #[Column]
    public string $firstname;

    #[Column]
    public string $lastname;

    #[Column, Unique]
    public string $email;

    #[Column]
    public string $password;

    #[Column(
        type: Field::enum, default: 'Customer', enums: ['Admin', 'Agent', 'Customer']
    )]
    public ?string $role = 'Customer';

    #[Column(type: Field::timestamp, currentTimestamp: true)]
    public ?DateTime $createdAt;

    #[Column(type: Field::timestamp, currentTimestamp: true, onUpdateCurrentTimestamp: true)]
    public ?DateTime $updatedAt = null;

    #[BelongsToMany(related: Role::class)]
    public Collection $roles;

    #[HasOne(Customer::class, 'userID', 'userID')]
    public ?Customer $customer = null;

    #[HasOne(Agent::class, 'userID', 'userID')]
    public ?Agent $agent = null;

    public function __construct(array $attributes = [])
    {
        $this->roles = new Collection();
        parent::__construct($attributes);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('Customer');
    }

    public function isAgent(): bool
    {
        return $this->hasRole('Agent');
    }

    public function getCustomerId(): ?int
    {
        $userId = $this->getAuthIdentifier() ?? $this->userID;
        if ($this->isCustomer()) {
            /** @var Customer $model */
            try {
                $model = ContainerBridge::resolve(Customer::class);
                $customer = $model::query()->select('customerID')
                    ->where('userID', $userId)
                    ->first();
                return $customer->customerID ?? 0;
            } catch (\Throwable $e) {
                return 0;
            }
        }
        return 0;
    }

    public function getAgentId()
    {
        $userId = $this->getAuthIdentifier() ?? $this->userID;
        if ($this->isAgent()) {
            /** @var Agent $model */
            try {
                $model = ContainerBridge::resolve(Agent::class);
                $agent = $model::query()->select('agentID')
                    ->where('userID', $userId)
                    ->first();
                return $agent->agentID ?? 0;
            } catch (\Throwable $e) {
                return 0;
            }
        }
        return 0;
    }
}