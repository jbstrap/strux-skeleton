<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repository;

use App\Domain\Identity\Entity\Customer;
use App\Domain\Identity\Repository\CustomerRepositoryInterface;
use Strux\Support\Collection;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function listAll(): Collection|Customer
    {
        return Customer::query()
            ->join('accounts', 'accounts.userID', '=', 'customers.userID')
            ->where('accounts.role', 'Customer')
            ->orderBy('firstname')
            ->all();
    }

    public function create(array $data): Customer
    {
        $customer = new Customer();
        foreach ($data as $k => $v) {
            $customer->{$k} = $v;
        }
        $this->save($customer);
        return $customer;
    }

    public function save(Customer $customer): void
    {
        $customer->save();
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}