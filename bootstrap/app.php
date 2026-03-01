<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckUserRole;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\VerifyLogtoWebhook;
use App\Services\ErrorPageService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web     : __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health  : '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth'                   => Authenticate::class,
            'role'                   => CheckUserRole::class,
            'verify.logto.webhook'   => VerifyLogtoWebhook::class,
        ]);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            if ($e instanceof HttpException) {
                $debugMessage = config('app.debug') ? $e->getMessage() : null;
                return ErrorPageService::render($e, $request, $debugMessage);
            }

            if ($e instanceof NotFoundHttpException) {
                return ErrorPageService::render(404, $request);
            }

            return null;
        });
    })->create();
