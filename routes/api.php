<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Authentication routes (with rate limiting)
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:register')
    ->name('register');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('login');

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    
    // Get authenticated user
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'user' => $user,
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'roles' => $user->getRoleNames(),
        ]);
    })->name('user');

    // Profile Management (Authenticated user's own profile)
    Route::prefix('profile')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
        Route::put('/', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
        Route::post('/avatar', [\App\Http\Controllers\ProfileController::class, 'uploadAvatar'])->name('profile.avatar.upload');
        Route::delete('/avatar', [\App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    });

    // Password Management (Authenticated user)
    Route::prefix('password')->group(function () {
        Route::post('/change', [\App\Http\Controllers\PasswordController::class, 'changePassword'])->name('password.change');
    });

    // User Management (Admin only - requires permission)
    Route::prefix('users')->middleware(['permission:manage users'])->group(function () {
        Route::get('/', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::post('/', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::get('/{id}', [\App\Http\Controllers\UserController::class, 'show'])->name('users.show');
        Route::put('/{id}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
        Route::delete('/{id}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/{id}/avatar', [\App\Http\Controllers\UserController::class, 'uploadAvatar'])->name('users.avatar.upload');
        Route::delete('/{id}/avatar', [\App\Http\Controllers\UserController::class, 'deleteAvatar'])->name('users.avatar.delete');
    });

    // Role & Permission Management (Admin only)
    Route::prefix('roles-permissions')->middleware(['permission:manage roles'])->group(function () {
        Route::get('/roles', [\App\Http\Controllers\RolePermissionController::class, 'getRoles'])->name('roles.index');
        Route::get('/permissions', [\App\Http\Controllers\RolePermissionController::class, 'getPermissions'])->name('permissions.index');
        Route::post('/assign-role', [\App\Http\Controllers\RolePermissionController::class, 'assignRole'])->name('assign.role');
        Route::post('/remove-role', [\App\Http\Controllers\RolePermissionController::class, 'removeRole'])->name('remove.role');
        Route::post('/sync-roles', [\App\Http\Controllers\RolePermissionController::class, 'syncRoles'])->name('sync.roles');
        Route::post('/give-permission', [\App\Http\Controllers\RolePermissionController::class, 'givePermissionTo'])->name('give.permission');
        Route::post('/revoke-permission', [\App\Http\Controllers\RolePermissionController::class, 'revokePermissionFrom'])->name('revoke.permission');
    });
});

// Password Reset (Public routes)
Route::prefix('password')->group(function () {
    Route::post('/forgot', [\App\Http\Controllers\PasswordController::class, 'forgotPassword'])->name('password.forgot');
    Route::post('/reset', [\App\Http\Controllers\PasswordController::class, 'resetPassword'])->name('password.reset');
});
