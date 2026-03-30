<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RolePermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionRepository implements RolePermissionRepositoryInterface
{
    /**
     * Get all roles with permissions eager loaded.
     *
     * @return Collection<int, Role>
     */
    public function getRolesWithPermissions(): Collection
    {
        return Role::with('permissions')->orderBy('name')->get();
    }

    /**
     * Get all permissions.
     *
     * @return Collection<int, Permission>
     */
    public function getAllPermissions(): Collection
    {
        return Permission::orderBy('name')->get();
    }
}
