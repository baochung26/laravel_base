<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\CrudRepositoryInterface;
use App\Repositories\Contracts\CriteriaRepositoryInterface;

interface UserRepositoryInterface extends CrudRepositoryInterface, CriteriaRepositoryInterface
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
    public function getAllWithRoles(array $columns = ['*']): Collection;

    /**
     * Get all users with roles and permissions.
     */
    public function getAllWithRelations(array $columns = ['*']): Collection;

    /**
     * Get paginated users with roles and permissions.
     */
    public function paginateWithRelations(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Search users by name or email.
     */
    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator;

    /**
     * Paginate users for dashboard with filters.
     *
     * @param array{q:string,role:string,status:string,sort_by:string,sort_dir:string} $filters
     */
    public function paginateForDashboard(array $filters, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get dashboard user stats.
     *
     * @return array{total:int,active:int,inactive:int,admin:int}
     */
    public function dashboardStats(): array;
}
