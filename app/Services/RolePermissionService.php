<?php

namespace App\Services;

use App\Repositories\Contracts\RolePermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RolePermissionService
{
    public function __construct(
        protected RolePermissionRepositoryInterface $rolePermissionRepository,
        protected UserService $userService
    ) {
    }

    /**
     * Get all roles with permissions.
     *
     * @return Collection<int, \Spatie\Permission\Models\Role>
     */
    public function getRoles(): Collection
    {
        return $this->rolePermissionRepository->getRolesWithPermissions();
    }

    /**
     * Get all permissions.
     *
     * @return Collection<int, \Spatie\Permission\Models\Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->rolePermissionRepository->getAllPermissions();
    }

    /**
     * Assign role to user. Returns user model with relations.
     */
    public function assignRoleToUser(int $userId, string $roleName)
    {
        $this->userService->assignRole($userId, $roleName);

        return $this->userService->getModelByIdWithRelations($userId);
    }

    /**
     * Remove role from user. Returns user model with relations.
     */
    public function removeRoleFromUser(int $userId, string $roleName)
    {
        $this->userService->removeRole($userId, $roleName);

        return $this->userService->getModelByIdWithRelations($userId);
    }

    /**
     * Sync roles for user. Returns user model with relations.
     */
    public function syncRolesForUser(int $userId, array $roleNames)
    {
        $this->userService->syncRoles($userId, $roleNames);

        return $this->userService->getModelByIdWithRelations($userId);
    }

    /**
     * Give permission to user. Returns user model with relations.
     */
    public function givePermissionToUser(int $userId, string $permissionName)
    {
        $this->userService->givePermissionTo($userId, $permissionName);

        return $this->userService->getModelByIdWithRelations($userId);
    }

    /**
     * Revoke permission from user. Returns user model with relations.
     */
    public function revokePermissionFromUser(int $userId, string $permissionName)
    {
        $this->userService->revokePermissionFrom($userId, $permissionName);

        return $this->userService->getModelByIdWithRelations($userId);
    }
}
