<?php

declare(strict_types=1);

namespace App\Domain\Identity\Repository;

use App\Domain\Identity\Entity\User;
use Strux\Component\Database\Paginator;

interface UserRepositoryInterface
{
    /**
     * Find a user by their unique ID.
     */
    public function find(int $id): ?User;

    /**
     * Find a user by their email address (useful for authentication).
     */
    public function findByEmail(string $email): ?User;

    public function listAll(int $page = 1, int $perPage = 20, array $query = [], string $path = '/'): Paginator;

    public function create(array $data): User;

    /**
     * Save a User (handles both Create and Update).
     * * In DDD, we usually persist the whole object.
     */
    public function save(User $user): void;

    /**
     * Remove a user from the system.
     */
    public function delete(User $user): void;

    /**
     * Check if a user exists by email without loading the whole object.
     */
    public function emailExists(string $email, ?int $ignoreUserId = null): bool;
}