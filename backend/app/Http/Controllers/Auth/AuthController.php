<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\AbstractController;
use App\Http\Requests\Auth\AuthCallbackRequest;
use App\Http\Requests\Auth\BackchannelLogoutRequest;
use App\Services\Auth\LogtoAuthService;
use Exception;
use Hypervel\Http\Request;
use Hypervel\Support\Facades\Log;
use Hypervel\Support\Facades\Session;
use Psr\Http\Message\ResponseInterface;

class AuthController extends AbstractController
{
    public function __construct(
        protected LogtoAuthService $authService,
    ) {
    }

    /**
     * Redirect user to Logto OIDC authorization endpoint.
     */
    public function login(Request $request): ResponseInterface
    {
        $authUrl = $this->authService->generateAuthorizationUrl();

        return redirect($authUrl);
    }

    /**
     * Handle OIDC authorization code callback from Logto.
     */
    public function callback(AuthCallbackRequest $request): ResponseInterface
    {
        $frontendUrl = $this->authService->getFrontendUrl();

        $error = $request->input('error');

        if (! empty($error)) {
            $errorDescription = $request->input('error_description', 'Authentication was cancelled or failed.');

            Log::warning('Logto callback received error', [
                'error'             => $error,
                'error_description' => $errorDescription,
            ]);

            return redirect($frontendUrl . '?error=' . urlencode((string) $errorDescription));
        }

        $code        = (string) $request->input('code', '');
        $state       = (string) $request->input('state', '');
        $storedState = Session::pull('oauth_state');
        $verifier    = Session::pull('oauth_code_verifier');

        if (empty($code)) {
            return redirect($frontendUrl . '?error=' . urlencode('No authorization code received.'));
        }

        try {
            $ipAddress = $request->header('x-forwarded-for') ?? $request->server('remote_addr');
            $userAgent = $request->header('user-agent');

            $this->authService->handleCallback(
                code:         $code,
                state:        $state,
                storedState:  is_string($storedState) ? $storedState : null,
                codeVerifier: is_string($verifier) ? $verifier : null,
                ipAddress:    is_string($ipAddress) ? $ipAddress : null,
                userAgent:    is_string($userAgent) ? $userAgent : null,
            );

            return redirect($frontendUrl);
        } catch (Exception $e) {
            Log::error('Logto authentication callback failed', [
                'message' => $e->getMessage(),
            ]);

            return redirect($frontendUrl . '?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Perform local session logout and redirect to Logto OIDC end-session endpoint.
     */
    public function logout(Request $request): ResponseInterface
    {
        $refreshToken = Session::get('logto_refresh_token');
        $logoutUrl    = $this->authService->logout(is_string($refreshToken) ? $refreshToken : null);

        $response = redirect($logoutUrl);

        return $response
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Expires', '0');
    }

    /**
     * Handle OIDC Back-Channel Logout 1.0 request from Logto.
     */
    public function backchannelLogout(BackchannelLogoutRequest $request): ResponseInterface
    {
        $logoutToken = (string) $request->input('logout_token', '');

        try {
            $this->authService->handleBackchannelLogout($logoutToken);

            return response('', 200)
                ->withHeader('Cache-Control', 'no-store')
                ->withHeader('Pragma', 'no-cache');
        } catch (Exception $e) {
            Log::warning('Back-channel logout processing failed', [
                'message' => $e->getMessage(),
            ]);

            return response('Invalid logout token', 400)
                ->withHeader('Cache-Control', 'no-store')
                ->withHeader('Pragma', 'no-cache');
        }
    }
}
