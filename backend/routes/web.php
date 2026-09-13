<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\UserController;
use Hypervel\Support\Facades\Config;
use Hypervel\Support\Facades\Route;

Route::get('/', function () {
    $frontendUrl = (string) Config::get('services.logto.frontend_url', 'https://identity.' . env('APP_DOMAIN', 'home.test'));

    return redirect($frontendUrl);
});

Route::get('/auth/login', [AuthController::class, 'login'], ['as' => 'auth.login']);
Route::get('/auth/callback', [AuthController::class, 'callback'], ['as' => 'auth.callback']);
Route::match(['GET', 'POST'], '/auth/logout', [AuthController::class, 'logout'], ['as' => 'auth.logout']);
Route::post('/auth/backchannel-logout', [AuthController::class, 'backchannelLogout'], ['as' => 'auth.backchannel-logout']);
Route::get('/auth/me', [AuthController::class, 'me'], ['as' => 'auth.me']);
Route::get('/me', [AuthController::class, 'me'], ['as' => 'me']);

// Protected User Management & Profile BFF Web Endpoints
Route::addGroup('', function (): void {
    // Profile Self-Management Endpoints
    Route::put('/profile', [ProfileController::class, 'updateProfile'], ['as' => 'profile.update']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'], ['as' => 'profile.change-password']);

    // User Management BFF Endpoints
    Route::get('/users', [UserController::class, 'index'], ['as' => 'users.index']);
    Route::post('/users', [UserController::class, 'store'], ['as' => 'users.store']);
    Route::get('/users/{id}', [UserController::class, 'show'], ['as' => 'users.show']);
    Route::put('/users/{id}', [UserController::class, 'update'], ['as' => 'users.update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy'], ['as' => 'users.destroy']);
    Route::patch('/users/{id}/status', [UserController::class, 'changeStatus'], ['as' => 'users.change-status']);
    Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword'], ['as' => 'users.reset-password']);
}, ['middleware' => ['auth:session']]);
