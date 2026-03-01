<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LogtoService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Random\RandomException;

class LogtoController extends Controller
{
    public function __construct(
        protected LogtoService $logtoService,
    ) {}

    /**
     * @throws RandomException
     */
    public function redirect(): RedirectResponse
    {
        $state = bin2hex(random_bytes(32));
        Session::put('oauth_state', $state);

        $endpoint    = config('services.logto.endpoint');
        $appId       = config('services.logto.app_id');
        $redirectUri = config('services.logto.redirect_uri');
        $scopes      = 'openid profile email phone offline_access roles';

        $authorizeUrl = "$endpoint/oidc/auth?" . http_build_query([
                'client_id'     => $appId,
                'redirect_uri'  => $redirectUri,
                'response_type' => 'code',
                'scope'         => $scopes,
                'state'         => $state,
            ]);

        return redirect($authorizeUrl);
    }

    public function callback(Request $request): RedirectResponse
    {
        $state       = $request->query('state');
        $storedState = Session::pull('oauth_state');

        if (!$state || !$storedState || $state !== $storedState) {
            return redirect('/')->with('error', 'Invalid OAuth state parameter.');
        }

        $code = $request->query('code');
        if (!$code) {
            return redirect('/')->with('error', 'No authorization code received from BangLipai Secure Portal.');
        }

        try {
            $authResult = $this->logtoService->authenticateWithAuthorizationCode($code);
            $user       = $authResult['user'];
            $tokens     = $authResult['tokens'];

            Session::put([
                'logto_access_token'     => $tokens['access_token'],
                'logto_refresh_token'    => $tokens['refresh_token'] ?? null,
                'logto_token_expires_at' => now()->addSeconds($tokens['expires_in'] ?? 3600),
            ]);

            Auth::login($user, remember: true);
            $this->logtoService->recordSignIn($user);

            return redirect()->route('dashboard')->with('success', 'Welcome back, ' . $user->name . '!');
        } catch (Exception $e) {
            Log::error('Logto callback error: ' . $e->getMessage());
            return redirect('/')->with('error', 'Authentication failed. Please try again.');
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        $refreshToken = Session::get('logto_refresh_token');
        $this->logtoService->revokeRefreshToken($refreshToken);

        Auth::logout();

        Session::forget(['logto_access_token', 'logto_refresh_token', 'logto_token_expires_at']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $endpoint    = config('services.logto.endpoint');
        $appId       = config('services.logto.app_id');
        $redirectUri = url('/');

        $logoutUrl = "$endpoint/oidc/session/end?" . http_build_query([
                'client_id'                => $appId,
                'post_logout_redirect_uri' => $redirectUri,
            ]);

        return redirect($logoutUrl)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
