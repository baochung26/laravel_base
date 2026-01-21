<?php

use App\Http\Controllers\Api\V1\Auth\AuthController as V1AuthController;
use App\Http\Controllers\Api\V1\ProfileController as V1ProfileController;
use App\Http\Controllers\Api\V1\PasswordController as V1PasswordController;
use App\Http\Controllers\Api\V1\UserController as V1UserController;
use App\Http\Controllers\Api\V1\RolePermissionController as V1RolePermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for version 1. These routes
| are loaded by the RouteServiceProvider and will be assigned the "api/v1"
| prefix and "api" middleware group.
|
*/

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Authentication routes (with rate limiting)
Route::post('/register', [V1AuthController::class, 'register'])
    ->middleware('throttle:register')
    ->name('v1.register');

Route::post('/login', [V1AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('v1.login');

// Password Reset (Public routes)
Route::prefix('password')->group(function () {
    Route::post('/forgot', [V1PasswordController::class, 'forgotPassword'])->name('v1.password.forgot');
    Route::post('/reset', [V1PasswordController::class, 'resetPassword'])->name('v1.password.reset');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::post('/logout', [V1AuthController::class, 'logout'])->name('v1.logout');
    Route::post('/refresh', [V1AuthController::class, 'refresh'])->name('v1.refresh');
    Route::get('/me', [V1AuthController::class, 'me'])->name('v1.me');

    // Profile Management (Authenticated user's own profile)
    Route::prefix('profile')->group(function () {
        Route::get('/', [V1ProfileController::class, 'show'])->name('v1.profile.show');
        Route::put('/', [V1ProfileController::class, 'update'])->name('v1.profile.update');
        Route::post('/avatar', [V1ProfileController::class, 'uploadAvatar'])->name('v1.profile.avatar.upload');
        Route::delete('/avatar', [V1ProfileController::class, 'deleteAvatar'])->name('v1.profile.avatar.delete');
    });

    // Password Management (Authenticated user)
    Route::prefix('password')->group(function () {
        Route::post('/change', [V1PasswordController::class, 'changePassword'])->name('v1.password.change');
    });

    // User Management (Admin only - requires permission)
    Route::prefix('users')->middleware(['permission:manage users'])->group(function () {
        Route::get('/', [V1UserController::class, 'index'])->name('v1.users.index');
        Route::post('/', [V1UserController::class, 'store'])->name('v1.users.store');
        Route::get('/{id}', [V1UserController::class, 'show'])->name('v1.users.show');
        Route::put('/{id}', [V1UserController::class, 'update'])->name('v1.users.update');
        Route::delete('/{id}', [V1UserController::class, 'destroy'])->name('v1.users.destroy');
        Route::post('/{id}/avatar', [V1UserController::class, 'uploadAvatar'])->name('v1.users.avatar.upload');
        Route::delete('/{id}/avatar', [V1UserController::class, 'deleteAvatar'])->name('v1.users.avatar.delete');
    });

    // Role & Permission Management (Admin only)
    Route::prefix('roles-permissions')->middleware(['permission:manage roles'])->group(function () {
        Route::get('/roles', [V1RolePermissionController::class, 'getRoles'])->name('v1.roles.index');
        Route::get('/permissions', [V1RolePermissionController::class, 'getPermissions'])->name('v1.permissions.index');
        Route::post('/assign-role', [V1RolePermissionController::class, 'assignRole'])->name('v1.assign.role');
        Route::post('/remove-role', [V1RolePermissionController::class, 'removeRole'])->name('v1.remove.role');
        Route::post('/sync-roles', [V1RolePermissionController::class, 'syncRoles'])->name('v1.sync.roles');
        Route::post('/give-permission', [V1RolePermissionController::class, 'givePermissionTo'])->name('v1.give.permission');
        Route::post('/revoke-permission', [V1RolePermissionController::class, 'revokePermissionFrom'])->name('v1.revoke.permission');
    });
});
