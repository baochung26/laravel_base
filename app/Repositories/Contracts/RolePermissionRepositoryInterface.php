<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface RolePermissionRepositoryInterface
{
    /**
     * Get all roles with permissions eager loaded.
     *
     * @return Collection<int, \Spatie\Permission\Models\Role>
     */
    public function getRolesWithPermissions(): Collection;

    /**
     * Get all permissions.
     *
     * @return Collection<int, \Spatie\Permission\Models\Permission>
     */
    public function getAllPermissions(): Collection;
}
