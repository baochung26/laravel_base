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

/**
 * @OA\Tag(
 *     name="Roles & Permissions",
 *     description="Role and Permission management endpoints (Admin only)"
 * )
 */
class RolePermissionController extends ApiController
{
    public function __construct(
        protected UserService $userService
    ) {
    }
    /**
     * @OA\Get(
     *     path="/api/v1/roles-permissions/roles",
     *     summary="Get all roles",
     *     tags={"Roles & Permissions"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of roles",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="roles", type="array", @OA\Items(ref="#/components/schemas/RoleResource"))
     *             )
     *         )
     *     )
     * )
     */
    public function getRoles(): JsonResponse
    {
        $roles = Role::with('permissions')->get();

        return $this->successResponse([
            'roles' => RoleResource::collection($roles),
        ], 'Roles retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/roles-permissions/permissions",
     *     summary="Get all permissions",
     *     tags={"Roles & Permissions"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="permissions", type="array", @OA\Items(ref="#/components/schemas/PermissionResource"))
     *             )
     *         )
     *     )
     * )
     */
    public function getPermissions(): JsonResponse
    {
        $permissions = Permission::all();

        return $this->successResponse([
            'permissions' => PermissionResource::collection($permissions),
        ], 'Permissions retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/roles-permissions/assign-role",
     *     summary="Assign role to user",
     *     tags={"Roles & Permissions"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","role"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="role", type="string", example="admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role assigned successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/UserResource")
     *             )
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/v1/roles-permissions/remove-role",
     *     summary="Remove role from user",
     *     tags={"Roles & Permissions"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","role"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="role", type="string", example="admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role removed successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/UserResource")
     *             )
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/v1/roles-permissions/sync-roles",
     *     summary="Sync user roles",
     *     tags={"Roles & Permissions"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","roles"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"admin", "moderator"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roles synced successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Roles synced successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/UserResource")
     *             )
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/v1/roles-permissions/give-permission",
     *     summary="Assign permission to user",
     *     tags={"Roles & Permissions"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","permission"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="permission", type="string", example="edit users")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permission assigned successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/UserResource")
     *             )
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/v1/roles-permissions/revoke-permission",
     *     summary="Revoke permission from user",
     *     tags={"Roles & Permissions"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","permission"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="permission", type="string", example="edit users")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission revoked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permission revoked successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/UserResource")
     *             )
     *         )
     *     )
     * )
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
