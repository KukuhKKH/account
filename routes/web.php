<?php

use App\Http\Controllers\Auth\LogtoController;
use App\Http\Controllers\UserController;
use App\Models\UserRole;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::controller(LogtoController::class)->group(function () {
    Route::get('/auth/login', 'redirect')->name('auth.login');
    Route::get('/auth/callback', 'callback')->name('auth.callback');
    Route::post('/auth/logout', 'logout')->name('auth.logout');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [UserController::class, 'profile'])->name('profile.show');

    $roles = [UserRole::ROLE_SUPERADMIN, UserRole::ROLE_ADMIN];
    Route::middleware("role:" . implode(',', $roles))->group(function () {
        Route::resource('users', UserController::class);
    });
});
