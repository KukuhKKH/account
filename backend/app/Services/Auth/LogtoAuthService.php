<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Data\Auth\BackchannelLogoutTokenData;
use App\Data\Auth\LogtoTokenResponseData;
use App\Data\Auth\LogtoUserClaimsData;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserSignInLog;
use App\Remote\LogtoHttpClient;
use Exception;
use Hypervel\Auth\Contracts\StatefulGuard;
use Hypervel\Support\Carbon;
use Hypervel\Support\Facades\Auth;
use Hypervel\Support\Facades\Cache;
use Hypervel\Support\Facades\Config;
use Hypervel\Support\Facades\Log;
use Hypervel\Support\Facades\Session;
use Hypervel\Support\Str;

class LogtoAuthService
{
    protected string $endpoint;
    protected string $appId;
    protected string $redirectUri;
    protected string $postLogoutRedirectUri;
    protected string $frontendUrl;
    protected string $scopes;

    public function __construct(
        protected LogtoHttpClient $httpClient,
    ) {
        $this->endpoint              = (string) Config::get('services.logto.endpoint', 'https://sso.home.test');
        $this->appId                 = (string) Config::get('services.logto.app_id', '');
        $this->redirectUri           = (string) Config::get('services.logto.redirect_uri', 'https://api-identity.home.test/auth/callback');
        $this->postLogoutRedirectUri = (string) Config::get('services.logto.post_logout_redirect_uri', 'https://identity.home.test');
        $this->frontendUrl           = (string) Config::get('services.logto.frontend_url', 'https://identity.home.test');
        $this->scopes                = (string) Config::get('services.logto.scopes', 'openid profile email phone offline_access roles');
    }

    /**
     * Generate Logto OIDC Authorization URL with PKCE and state.
     */
    public function generateAuthorizationUrl(): string
    {
        $state        = bin2hex(random_bytes(32));
        $codeVerifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $challenge    = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        Session::put('oauth_state', $state);
        Session::put('oauth_code_verifier', $codeVerifier);

        $endpoint    = (string) Config::get('services.logto.endpoint', 'https://sso.home.test');
        $appId       = (string) Config::get('services.logto.app_id', '');
        $redirectUri = (string) Config::get('services.logto.redirect_uri', 'https://identity.home.test/auth/callback');
        $scopes      = (string) Config::get('services.logto.scopes', 'openid profile email phone offline_access roles');

        $queryParams = [
            'client_id'             => $appId,
            'redirect_uri'          => $redirectUri,
            'response_type'         => 'code',
            'scope'                 => $scopes,
            'state'                 => $state,
            'code_challenge'        => $challenge,
            'code_challenge_method' => 'S256',
        ];

        return rtrim($endpoint, '/') . '/oidc/auth?' . http_build_query($queryParams);
    }

    /**
     * Handle OIDC Authorization Code callback.
     *
     * @throws Exception
     */
    public function handleCallback(
        string  $code,
        string  $state,
        ?string $storedState,
        ?string $codeVerifier,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): User {
        if (empty($state) || empty($storedState) || ! hash_equals($storedState, $state)) {
            throw new Exception('Invalid OAuth state parameter.');
        }

        if (empty($codeVerifier)) {
            throw new Exception('Missing PKCE code verifier from session.');
        }

        $tokens = $this->httpClient->exchangeAuthorizationCode($code, $codeVerifier);

        if (empty($tokens->idToken)) {
            throw new Exception('Missing id_token from Logto authentication response.');
        }

        $claims = LogtoUserClaimsData::fromIdToken($tokens->idToken);

        if (empty($claims->sub) || empty($claims->email)) {
            throw new Exception('Invalid user claims received from Logto ID token.');
        }

        $user = User::query()
            ->where('logto_id', '=', $claims->sub)
            ->first();

        if (! $user) {
            $user = new User();

            $user->logto_id = $claims->sub;
            $user->password = bcrypt(Str::random(32));
        }

        $user->name          = $claims->name;
        $user->email         = $claims->email;
        $user->phone         = $claims->phone;
        $user->avatar        = $claims->avatar;
        $user->address       = $claims->address;
        $user->custom_data   = $claims->customData;
        $user->last_login_at = Carbon::now();

        $user->save();

        $this->syncUserRoles($user, $claims->roles);

        $sessionId = Session::getId();
        $expiresIn = $tokens->expiresIn;

        Session::put([
            'logto_access_token'     => $tokens->accessToken,
            'logto_refresh_token'    => $tokens->refreshToken,
            'logto_token_expires_at' => Carbon::now()->addSeconds($expiresIn)->toIso8601String(),
            'logto_sub'              => $claims->sub,
            'logto_sid'              => $claims->sid,
        ]);

        $this->trackSessionForBackchannel($claims->sub, $claims->sid, $sessionId);

        $guard = Auth::guard('session');

        if ($guard instanceof StatefulGuard) {
            $guard->login($user);
        }

        $this->recordSignIn($user, $ipAddress, $userAgent);

        return $user;
    }

    /**
     * Perform local logout and revoke refresh token.
     */
    public function logout(?string $refreshToken = null): string
    {
        if ($refreshToken !== null && $refreshToken !== '') {
            $this->httpClient->revokeRefreshToken($refreshToken);
        }

        $guard = Auth::guard('session');

        if ($guard instanceof StatefulGuard) {
            $guard->logout();
        }

        Session::forget([
            'logto_access_token',
            'logto_refresh_token',
            'logto_token_expires_at',
            'logto_sub',
            'logto_sid',
        ]);

        if (Session::isStarted()) {
            Session::invalidate();
            Session::regenerateToken();
        } else {
            Session::flush();
        }

        $endpoint              = (string) Config::get('services.logto.endpoint', 'https://sso.home.test');
        $appId                 = (string) Config::get('services.logto.app_id', '');
        $postLogoutRedirectUri = (string) Config::get('services.logto.post_logout_redirect_uri', 'https://identity.home.test');

        $queryParams = [
            'client_id'                => $appId,
            'post_logout_redirect_uri' => $postLogoutRedirectUri,
        ];

        return rtrim($endpoint, '/') . '/oidc/session/end?' . http_build_query($queryParams);
    }

    /**
     * Handle OIDC Back-Channel Logout 1.0 request.
     *
     * @throws Exception
     */
    public function handleBackchannelLogout(string $logoutToken): void
    {
        $endpoint = (string) Config::get('services.logto.endpoint', 'https://sso.home.test');
        $appId    = (string) Config::get('services.logto.app_id', '');

        $tokenData = BackchannelLogoutTokenData::fromToken($logoutToken);

        if (! $tokenData->isValid($endpoint, $appId)) {
            Log::warning('Invalid OIDC Back-Channel Logout token received', [
                'iss' => $tokenData->iss,
                'aud' => $tokenData->aud,
            ]);

            throw new Exception('Invalid Back-Channel Logout token.');
        }

        if (! empty($tokenData->sub)) {
            $this->invalidateUserSessionsBySub($tokenData->sub);
        }

        if (! empty($tokenData->sid)) {
            $this->invalidateUserSessionsBySid($tokenData->sid);
        }

        Log::info('OIDC Back-Channel Logout processed successfully', [
            'sub' => $tokenData->sub,
            'sid' => $tokenData->sid,
        ]);
    }

    /**
     * Track active session ID associated with Logto subject or session ID.
     */
    protected function trackSessionForBackchannel(string $sub, ?string $sid, string $sessionId): void
    {
        $cacheTtl = (int) Config::get('session.lifetime', 120) * 60;

        $subKey      = "auth:user_sessions:{$sub}";
        $subSessions = Cache::get($subKey, []);

        if (! is_array($subSessions)) {
            $subSessions = [];
        }

        if (! in_array($sessionId, $subSessions, true)) {
            $subSessions[] = $sessionId;
        }

        Cache::put($subKey, $subSessions, $cacheTtl);

        if (! empty($sid)) {
            $sidKey      = "auth:sid_sessions:{$sid}";
            $sidSessions = Cache::get($sidKey, []);

            if (! is_array($sidSessions)) {
                $sidSessions = [];
            }

            if (! in_array($sessionId, $sidSessions, true)) {
                $sidSessions[] = $sessionId;
            }

            Cache::put($sidKey, $sidSessions, $cacheTtl);
        }
    }

    /**
     * Invalidate all sessions tracked for a Logto user subject.
     */
    protected function invalidateUserSessionsBySub(string $sub): void
    {
        $subKey   = "auth:user_sessions:{$sub}";
        $sessions = Cache::get($subKey, []);

        if (is_array($sessions)) {
            foreach ($sessions as $sessionId) {
                if (is_string($sessionId) && ! empty($sessionId)) {
                    Cache::forget($sessionId);
                }
            }
        }

        Cache::forget($subKey);
    }

    /**
     * Invalidate all sessions tracked for a Logto session ID (sid).
     */
    protected function invalidateUserSessionsBySid(string $sid): void
    {
        $sidKey   = "auth:sid_sessions:{$sid}";
        $sessions = Cache::get($sidKey, []);

        if (is_array($sessions)) {
            foreach ($sessions as $sessionId) {
                if (is_string($sessionId) && ! empty($sessionId)) {
                    Cache::forget($sessionId);
                }
            }
        }

        Cache::forget($sidKey);
    }

    /**
     * Sync user roles between Logto claims and local UserRole records.
     *
     * @param  array<int, string> $logtoRoles
     */
    protected function syncUserRoles(User $user, array $logtoRoles): void
    {
        $existingRoles = $user->roles()->pluck('role')->toArray();
        $rolesToAdd    = array_diff($logtoRoles, $existingRoles);
        $rolesToRemove = array_diff($existingRoles, $logtoRoles);

        $newRoles = [];

        foreach ($rolesToAdd as $role) {
            if (! empty($role)) {
                $newRoles[] = new UserRole([
                    'role' => $role,
                ]);
            }
        }

        if (! empty($newRoles)) {
            $user->roles()->saveMany($newRoles);
        }

        if (! empty($rolesToRemove)) {
            $user->roles()->whereIn('role', $rolesToRemove)->delete();
        }
    }

    /**
     * Record sign-in event into user_sign_in_logs.
     */
    public function recordSignIn(User $user, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        try {
            $signInLog = new UserSignInLog();

            $signInLog->user_id      = $user->id;
            $signInLog->ip_address   = $ipAddress;
            $signInLog->user_agent   = $userAgent;
            $signInLog->device_info  = $this->parseDeviceInfo($userAgent);
            $signInLog->signed_in_at = Carbon::now();

            $signInLog->save();
        } catch (Exception $e) {
            Log::error('Failed to record sign-in log', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Parse device and browser details from User-Agent string.
     *
     * @return array<string, string|null>
     */
    public function parseDeviceInfo(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return [
                'user_agent' => null,
                'browser'    => 'Unknown',
                'os'         => 'Unknown',
            ];
        }

        return [
            'user_agent' => $userAgent,
            'browser'    => $this->extractBrowser($userAgent),
            'os'         => $this->extractOS($userAgent),
        ];
    }

    /**
     * Extract browser family from User-Agent string.
     */
    public function extractBrowser(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        return match (true) {
            str_contains($ua, 'edg/')     => 'Edge',
            str_contains($ua, 'chrome/')  => 'Chrome',
            str_contains($ua, 'firefox/') => 'Firefox',
            str_contains($ua, 'safari/')  => 'Safari',
            default                       => 'Unknown',
        };
    }

    /**
     * Extract OS platform from User-Agent string.
     */
    public function extractOS(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        return match (true) {
            str_contains($ua, 'windows') => 'Windows',
            str_contains($ua, 'android') => 'Android',
            str_contains($ua, 'iphone'),
            str_contains($ua, 'ipad')    => 'iOS',
            str_contains($ua, 'mac os')  => 'macOS',
            str_contains($ua, 'linux')   => 'Linux',
            default                      => 'Unknown',
        };
    }

    /**
     * Get target frontend dashboard URL.
     */
    public function getFrontendUrl(): string
    {
        return (string) Config::get('services.logto.frontend_url', $this->frontendUrl);
    }
}
