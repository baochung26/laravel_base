<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends ApiController
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    /**
     * Get all roles.
     */
    public function getRoles(): JsonResponse
    {
        $roles = Role::with('permissions')->get();

        return $this->successResponse([
            'roles' => RoleResource::collection($roles),
        ], 'Roles retrieved successfully');
    }

    /**
     * Get all permissions.
     */
    public function getPermissions(): JsonResponse
    {
        $permissions = Permission::all();

        return $this->successResponse([
            'permissions' => PermissionResource::collection($permissions),
        ], 'Permissions retrieved successfully');
    }

    /**
     * Assign role to user.
     */
    public function assignRole(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name',
        ]);

        $this->userService->assignRole($request->user_id, $request->role);
        $userModel = $this->userService->getModelByIdWithRelations($request->user_id);

        return $this->successResponse([
            'user' => new UserResource($userModel),
        ], 'Role assigned successfully');
    }

    /**
     * Remove role from user.
     */
    public function removeRole(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name',
        ]);

        $this->userService->removeRole($request->user_id, $request->role);
        $userModel = $this->userService->getModelByIdWithRelations($request->user_id);

        return $this->successResponse([
            'user' => new UserResource($userModel),
        ], 'Role removed successfully');
    }

    /**
     * Sync user roles.
     */
    public function syncRoles(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $this->userService->syncRoles($request->user_id, $request->roles);
        $userModel = $this->userService->getModelByIdWithRelations($request->user_id);

        return $this->successResponse([
            'user' => new UserResource($userModel),
        ], 'Roles synced successfully');
    }

    /**
     * Give permission to user.
     */
    public function givePermissionTo(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|exists:permissions,name',
        ]);

        $this->userService->givePermissionTo($request->user_id, $request->permission);
        $userModel = $this->userService->getModelByIdWithRelations($request->user_id);

        return $this->successResponse([
            'user' => new UserResource($userModel),
        ], 'Permission assigned successfully');
    }

    /**
     * Revoke permission from user.
     */
    public function revokePermissionFrom(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|exists:permissions,name',
        ]);

        $this->userService->revokePermissionFrom($request->user_id, $request->permission);
        $userModel = $this->userService->getModelByIdWithRelations($request->user_id);

        return $this->successResponse([
            'user' => new UserResource($userModel),
        ], 'Permission revoked successfully');
    }
}
