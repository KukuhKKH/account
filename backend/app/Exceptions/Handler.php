<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\User\UserDomainException;
use Hyperf\HttpMessage\Exception\HttpException as HyperfHttpException;
use Hypervel\Auth\Access\AuthorizationException;
use Hypervel\Auth\AuthenticationException;
use Hypervel\Database\Eloquent\ModelNotFoundException;
use Hypervel\Foundation\Exceptions\Handler as ExceptionHandler;
use Hypervel\Http\Request;
use Hypervel\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected array $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Enforce JSON rendering for API, user management, profile, and auth routes
        $this->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            $path = $request->path();

            return $request->expectsJson()
                || str_starts_with($path, 'api')
                || str_starts_with($path, 'users')
                || str_starts_with($path, 'profile')
                || str_starts_with($path, 'auth')
                || str_starts_with($path, 'me');
        });

        // 1. User Domain Exceptions
        $this->renderable(function (UserDomainException $e, Request $request) {
            return response()->json([
                'success' => false,
                'code'    => $e->getErrorCode(),
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        });

        // 2. Authentication Exceptions (HTTP 401)
        $this->renderable(function (AuthenticationException $e, Request $request) {
            return response()->json([
                'success' => false,
                'code'    => 'UNAUTHENTICATED',
                'message' => 'Sesi login tidak valid atau telah berakhir.',
            ], 401);
        });

        // 3. Authorization / Policy Exceptions (HTTP 403)
        $this->renderable(function (AuthorizationException $e, Request $request) {
            return response()->json([
                'success' => false,
                'code'    => 'ACCESS_DENIED',
                'message' => $e->getMessage() ?: 'Anda tidak memiliki hak akses untuk melakukan tindakan ini.',
            ], 403);
        });

        // 4. Model Not Found Exceptions (HTTP 404)
        $this->renderable(function (ModelNotFoundException $e, Request $request) {
            return response()->json([
                'success' => false,
                'code'    => 'USER_NOT_FOUND',
                'message' => 'Pengguna tidak ditemukan dalam sistem.',
            ], 404);
        });

        // 5. Validation Exceptions (HTTP 422)
        $this->renderable(function (ValidationException $e, Request $request) {
            return response()->json([
                'success' => false,
                'code'    => 'VALIDATION_ERROR',
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        });

        $this->reportable(function (Throwable $e) {
        });
    }

    /**
     * Prepare a centralized JSON response for unhandled exceptions, protecting sensitive internals.
     */
    protected function prepareJsonResponse(Request $request, Throwable $e): ResponseInterface
    {
        $status = $e instanceof HyperfHttpException ? $e->getStatusCode() : 500;

        $code = match ($status) {
            401     => 'UNAUTHENTICATED',
            403     => 'ACCESS_DENIED',
            404     => 'NOT_FOUND',
            422     => 'VALIDATION_ERROR',
            default => 'INTERNAL_SERVER_ERROR',
        };

        $message = match ($status) {
            401     => 'Sesi login tidak valid atau telah berakhir.',
            403     => 'Anda tidak memiliki hak akses untuk melakukan tindakan ini.',
            404     => 'Sumber daya tidak ditemukan.',
            422     => 'Data yang dikirim tidak valid.',
            default => 'Terjadi kesalahan pada server.',
        };

        $payload = [
            'success' => false,
            'code'    => $code,
            'message' => $message,
        ];

        return response()->json($payload, $status);
    }
}
