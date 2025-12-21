<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repository;

use App\Domain\Identity\Entity\User;
use App\Domain\Identity\Repository\UserRepositoryInterface;
use Strux\Component\Database\Paginator;

class UserRepository implements UserRepositoryInterface
{
    public function find(int $id): ?User
    {
        return User::find($id, with: ['customer', 'agent', 'roles']);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()
            ->with('customer', 'agent', 'roles')
            ->where('email', strtolower($email))->first();
    }

    public function listAll(int $page = 1, int $perPage = 20, array $query = [], string $path = '/'): Paginator
    {
        return User::query()
            ->with('roles')
            ->orderBy('createdAt', 'DESC')
            ->paginate(perPage: $perPage, page: $page, path: $path, query: $query);
    }

    public function create(array $data): User
    {
        $user = new User();
        foreach ($data as $k => $v) {
            $user->{$k} = $v;
        }
        $this->save($user);
        return $user;
    }

    public function save(User $user): void
    {
        $user->save();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function emailExists(string $email, ?int $ignoreUserId = null): bool
    {
        $query = User::query()
            ->where('email', strtolower($email));

        if ($ignoreUserId !== null) {
            $query->where('userID', '!=', $ignoreUserId);
        }

        return $query->first() !== null;
    }
}