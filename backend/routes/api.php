<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use Hypervel\Support\Facades\Config;
use Hypervel\Support\Facades\Route;

Route::any('/', function () {
    $frontendUrl = (string) Config::get('services.logto.frontend_url', 'https://identity.' . env('APP_DOMAIN', 'home.test'));

    return redirect($frontendUrl);
});

Route::get('/auth/me', [AuthController::class, 'me'], ['as' => 'api.auth.me']);
Route::get('/me', [AuthController::class, 'me'], ['as' => 'api.me']);

