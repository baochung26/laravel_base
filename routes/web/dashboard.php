<?php

use App\Http\Controllers\Web\Dashboard\DashboardController;
use App\Http\Controllers\Web\Dashboard\DashboardUserController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/users', [DashboardUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [DashboardUserController::class, 'create'])->name('users.create');
    Route::post('/users', [DashboardUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [DashboardUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [DashboardUserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/status', [DashboardUserController::class, 'updateStatus'])->name('users.status.update');
    Route::delete('/users/{user}', [DashboardUserController::class, 'destroy'])->name('users.destroy');
});
