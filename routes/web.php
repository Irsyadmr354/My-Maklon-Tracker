<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', [UserController::class, 'masuk'])
     ->name('login.form');
Route::get('/login', [UserController::class, 'masuk']);
Route::post('/login', [UserController::class, 'logika_masuk'])
     ->middleware('throttle:5,1')
     ->name('login');

Route::middleware(['auth', 'IsAdmin'])->group(function () {
    Route::get('/admin', [UserController::class, 'admin'])
         ->name('admin.index');
    Route::post('/progress/update', [UserController::class, 'updateProgress'])
         ->name('progress.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/order-tracker', [UserController::class, 'index'])
         ->name('tracker.index');
    Route::post('/logout', [UserController::class, 'logout'])
         ->name('logout');
});
