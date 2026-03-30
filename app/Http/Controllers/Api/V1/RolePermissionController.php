<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\RolePermission\AssignRoleRequest;
use App\Http\Requests\RolePermission\GivePermissionRequest;
use App\Http\Requests\RolePermission\RemoveRoleRequest;
use App\Http\Requests\RolePermission\RevokePermissionRequest;
use App\Http\Requests\RolePermission\SyncRolesRequest;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Services\RolePermissionService;
use Illuminate\Http\JsonResponse;

class RolePermissionController extends ApiController
{
    public function __construct(
        protected RolePermissionService $rolePermissionService
    ) {
    }

    /**
     * Get all roles.
     */
    public function getRoles(): JsonResponse
    {
        $roles = $this->rolePermissionService->getRoles();

        return $this->successResponse([
            'roles' => RoleResource::collection($roles),
        ], 'Roles retrieved successfully');
    }

    /**
     * Get all permissions.
     */
    public function getPermissions(): JsonResponse
    {
        $permissions = $this->rolePermissionService->getPermissions();

        return $this->successResponse([
            'permissions' => PermissionResource::collection($permissions),
        ], 'Permissions retrieved successfully');
    }

    /**
     * Assign role to user.
     */
    public function assignRole(AssignRoleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->rolePermissionService->assignRoleToUser(
            (int) $data['user_id'],
            $data['role']
        );

        return $this->successResponse([
            'user' => new UserResource($user),
        ], 'Role assigned successfully');
    }

    /**
     * Remove role from user.
     */
    public function removeRole(RemoveRoleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->rolePermissionService->removeRoleFromUser(
            (int) $data['user_id'],
            $data['role']
        );

        return $this->successResponse([
            'user' => new UserResource($user),
        ], 'Role removed successfully');
    }

    /**
     * Sync user roles.
     */
    public function syncRoles(SyncRolesRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->rolePermissionService->syncRolesForUser(
            (int) $data['user_id'],
            $data['roles']
        );

        return $this->successResponse([
            'user' => new UserResource($user),
        ], 'Roles synced successfully');
    }

    /**
     * Give permission to user.
     */
    public function givePermissionTo(GivePermissionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->rolePermissionService->givePermissionToUser(
            (int) $data['user_id'],
            $data['permission']
        );

        return $this->successResponse([
            'user' => new UserResource($user),
        ], 'Permission assigned successfully');
    }

    /**
     * Revoke permission from user.
     */
    public function revokePermissionFrom(RevokePermissionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->rolePermissionService->revokePermissionFromUser(
            (int) $data['user_id'],
            $data['permission']
        );

        return $this->successResponse([
            'user' => new UserResource($user),
        ], 'Permission revoked successfully');
    }
}
