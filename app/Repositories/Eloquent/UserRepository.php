<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Specify Model class name.
     */
    protected function model(): string
    {
        return User::class;
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findBy('email', $email);
    }

    /**
     * Check if user exists by email.
     */
    public function existsByEmail(string $email): bool
    {
        return $this->model->where('email', $email)->exists();
    }

    /**
     * Get user with roles and permissions.
     */
    public function withRolesAndPermissions(int $id): ?User
    {
        return $this->with(['roles', 'permissions'])
            ->find($id);
    }

    /**
     * Get all users with roles.
     */
    public function getAllWithRoles(array $columns = ['*'])
    {
        return $this->with(['roles'])
            ->all($columns);
    }

    /**
     * Get all users with roles and permissions.
     */
    public function getAllWithRelations(array $columns = ['*'])
    {
        return $this->with(['roles', 'permissions'])
            ->all($columns);
    }

    /**
     * Get paginated users with roles.
     */
    public function paginateWithRoles(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->with(['roles'])
            ->paginate($perPage, $columns);
    }

    /**
     * Get paginated users with roles and permissions.
     */
    public function paginateWithRelations(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->with(['roles', 'permissions'])
            ->paginate($perPage, $columns);
    }

    /**
     * Search users by name or email.
     */
    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            })
            ->with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
