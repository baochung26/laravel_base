<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\PasswordController;
use App\Http\Controllers\Api\V1\FileController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\RolePermissionController;
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

// Health check (public, more lenient rate limit)
Route::middleware('throttle:api-public')->group(function () {
    Route::get('/health', HealthController::class)->name('v1.health');
    Route::get('/health/live', [HealthController::class, 'live'])->name('v1.health.live');
    Route::get('/health/ready', [HealthController::class, 'ready'])->name('v1.health.ready');
});

// Authentication routes (with specific rate limiting)
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:register')
    ->name('v1.register');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('v1.login');

Route::post('/login/google', [AuthController::class, 'googleLogin'])
    ->middleware('throttle:login')
    ->name('v1.login.google');

// Password Reset (Public routes with rate limiting)
Route::prefix('password')->middleware('throttle:password-reset')->group(function () {
    Route::post('/forgot', [PasswordController::class, 'forgotPassword'])->name('v1.password.forgot');
    Route::post('/reset', [PasswordController::class, 'resetPassword'])->name('v1.password.reset');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout'])->name('v1.logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('v1.refresh');
    Route::get('/me', [AuthController::class, 'me'])->name('v1.me');

    // Profile Management (Authenticated user's own profile)
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('v1.profile.show');
        Route::put('/', [ProfileController::class, 'update'])->name('v1.profile.update');
        Route::post('/avatar', [ProfileController::class, 'uploadAvatar'])->name('v1.profile.avatar.upload');
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('v1.profile.avatar.delete');
    });

    // Password Management (Authenticated user)
    Route::prefix('password')->group(function () {
        Route::post('/change', [PasswordController::class, 'changePassword'])->name('v1.password.change');
    });

    // User Management (Admin only - requires permission)
    Route::prefix('users')->middleware(['permission:manage users'])->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('v1.users.index');
        Route::post('/', [UserController::class, 'store'])->name('v1.users.store');
        Route::get('/{id}', [UserController::class, 'show'])->name('v1.users.show');
        Route::put('/{id}', [UserController::class, 'update'])->name('v1.users.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('v1.users.destroy');
        Route::post('/{id}/avatar', [UserController::class, 'uploadAvatar'])->name('v1.users.avatar.upload');
        Route::delete('/{id}/avatar', [UserController::class, 'deleteAvatar'])->name('v1.users.avatar.delete');
    });

    // Role & Permission Management (Admin only)
    Route::prefix('roles-permissions')->middleware(['permission:manage roles'])->group(function () {
        Route::get('/roles', [RolePermissionController::class, 'getRoles'])->name('v1.roles.index');
        Route::get('/permissions', [RolePermissionController::class, 'getPermissions'])->name('v1.permissions.index');
        Route::post('/assign-role', [RolePermissionController::class, 'assignRole'])->name('v1.assign.role');
        Route::post('/remove-role', [RolePermissionController::class, 'removeRole'])->name('v1.remove.role');
        Route::post('/sync-roles', [RolePermissionController::class, 'syncRoles'])->name('v1.sync.roles');
        Route::post('/give-permission', [RolePermissionController::class, 'givePermissionTo'])->name('v1.give.permission');
        Route::post('/revoke-permission', [RolePermissionController::class, 'revokePermissionFrom'])->name('v1.revoke.permission');
    });

    // File management routes (authenticated + ownership/permission checks in service)
    Route::prefix('files')->group(function () {
        Route::post('/upload', [FileController::class, 'upload'])->name('v1.files.upload');
        Route::post('/upload-multiple', [FileController::class, 'uploadMultiple'])->name('v1.files.upload-multiple');
        Route::get('/download', [FileController::class, 'download'])->name('v1.files.download');
        Route::delete('/', [FileController::class, 'delete'])->name('v1.files.delete');
        Route::get('/list', [FileController::class, 'list'])->name('v1.files.list');
        Route::get('/stats', [FileController::class, 'stats'])->name('v1.files.stats');
    });
});
