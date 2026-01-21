<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Check if user exists by email.
     */
    public function existsByEmail(string $email): bool;

    /**
     * Get users with roles and permissions.
     */
    public function withRolesAndPermissions(int $id): ?User;

    /**
     * Get all users with roles.
     */
    public function getAllWithRoles(array $columns = ['*']);

    /**
     * Search users by name or email.
     */
    public function search(string $keyword, int $perPage = 15);
}
