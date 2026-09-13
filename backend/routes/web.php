<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
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
