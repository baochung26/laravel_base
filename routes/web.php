<?php

use App\Http\Controllers\StorageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Storage download route (for private files)
Route::get('/storage/download/{path}', [StorageController::class, 'download'])
    ->where('path', '.*')
    ->middleware('auth:sanctum')
    ->name('storage.download');
