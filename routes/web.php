<?php

use App\Http\Controllers\Auth\LogtoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebhookController;
use App\Models\UserRole;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::controller(LogtoController::class)->group(function () {
    Route::get('/auth/login', 'redirect')->name('auth.login');
    Route::get('/auth/callback', 'callback')->name('auth.callback');
    Route::post('/auth/logout', 'logout')->name('auth.logout');
});

Route::post('/webhooks/logto', [WebhookController::class, 'handleLogtoWebhook'])
    ->middleware('verify.logto.webhook')
    ->withoutMiddleware('web')
    ->name('webhooks.logto');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [UserController::class, 'profile'])->name('profile.show');
    Route::post('/profile/password/change', [UserController::class, 'changePassword'])->name('profile.password.change');

    $roles = [UserRole::ROLE_SUPERADMIN, UserRole::ROLE_ADMIN];
    Route::middleware("role:" . implode(',', $roles))->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/users/{user}/reset-password', [UserController::class, 'showResetPasswordForm'])->name('users.reset-password.form');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetUserPassword'])->name('users.reset-password');
    });
});
