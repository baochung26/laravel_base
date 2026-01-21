<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    /**
     * Get all roles.
     */
    public function getRoles(): JsonResponse
    {
        $roles = Role::with('permissions')->get();

        return response()->json([
            'roles' => $roles,
        ]);
    }

    /**
     * Get all permissions.
     */
    public function getPermissions(): JsonResponse
    {
        $permissions = Permission::all();

        return response()->json([
            'permissions' => $permissions,
        ]);
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

        $user = \App\Models\User::findOrFail($request->user_id);
        $user->assignRole($request->role);

        return response()->json([
            'message' => 'Role assigned successfully',
            'user' => $user->load('roles', 'permissions'),
        ]);
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

        $user = \App\Models\User::findOrFail($request->user_id);
        $user->removeRole($request->role);

        return response()->json([
            'message' => 'Role removed successfully',
            'user' => $user->load('roles', 'permissions'),
        ]);
    }

    /**
     * Assign permission to user.
     */
    public function givePermissionTo(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|exists:permissions,name',
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        $user->givePermissionTo($request->permission);

        return response()->json([
            'message' => 'Permission assigned successfully',
            'user' => $user->load('roles', 'permissions'),
        ]);
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

        $user = \App\Models\User::findOrFail($request->user_id);
        $user->revokePermissionTo($request->permission);

        return response()->json([
            'message' => 'Permission revoked successfully',
            'user' => $user->load('roles', 'permissions'),
        ]);
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

        $user = \App\Models\User::findOrFail($request->user_id);
        $user->syncRoles($request->roles);

        return response()->json([
            'message' => 'Roles synced successfully',
            'user' => $user->load('roles', 'permissions'),
        ]);
    }
}
