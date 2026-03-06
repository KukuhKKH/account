<?php

namespace App\Remote;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LogtoRemote
{
    protected string $endpoint;
    protected string $appId;
    protected string $appSecret;
    protected string $managementApiResource;
    protected string $managementApiIdentifier;
    protected string $managementAppId;
    protected string $managementAppSecret;

    protected const M2M_TOKEN_CACHE_KEY = 'logto_m2m_token';
    protected const M2M_TOKEN_CACHE_TTL = 3300;

    public function __construct()
    {
        $this->endpoint  = config('services.logto.endpoint');
        $this->appId     = config('services.logto.app_id');
        $this->appSecret = config('services.logto.app_secret');

        $this->managementApiResource   = config('services.logto.management_api_resource');
        $this->managementApiIdentifier = config('services.logto.management_api_identifier');
        $this->managementAppId         = config('services.logto.management_app_id');
        $this->managementAppSecret     = config('services.logto.management_app_secret');
    }

    protected function client(): PendingRequest
    {
        $http = Http::baseUrl($this->endpoint)
            ->timeout(10)
            ->connectTimeout(5)
            ->acceptJson();

        if (app()->environment('production')) {
            $http->withOptions(['verify' => true]);
        } else {
            $caPath = 'infra/certs/_wildcard.logto.test.pem';
            $http->withOptions([
                'verify' => file_exists($caPath) ? $caPath : false,
            ]);
        }

        return $http;
    }

    /**
     * @throws RequestException|ConnectionException
     */
    public function exchangeAuthorizationCode(string $code): array
    {
        $response = $this->client()
            ->asForm()
            ->post('/oidc/token', [
                'client_id'     => $this->appId,
                'client_secret' => $this->appSecret,
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => config('services.logto.redirect_uri'),
            ]);

        $response->throw();
        return $response->json();
    }

    /**
     * @throws RequestException|ConnectionException
     */
    public function revokeRefreshToken(string $refreshToken): void
    {
        $response = $this->client()
            ->asForm()
            ->post('/oidc/token/revocation', [
                'client_id'     => $this->appId,
                'client_secret' => $this->appSecret,
                'token'         => $refreshToken,
            ]);

        $response->throw();
    }

    /**
     * @return string
     * @throws RequestException|ConnectionException
     */
    public function getM2mToken(): string
    {
        $cachedToken = Cache::get(self::M2M_TOKEN_CACHE_KEY);
        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            $response = $this->client()
                ->asForm()
                ->withBasicAuth($this->managementAppId ?? '', $this->managementAppSecret ?? '')
                ->post('/oidc/token', [
                    'grant_type' => 'client_credentials',
                    'resource'   => $this->managementApiResource,
                    'scope'      => 'all',
                ]);

            $response->throw();
            $token = $response->json('access_token');

            if (!$token) {
                Log::error('Logto M2M token response missing access_token', [
                    'response' => $response->json(),
                    'endpoint' => $this->endpoint,
                ]);

                throw new Exception('No access_token in M2M token response');
            }

            Cache::put(self::M2M_TOKEN_CACHE_KEY, $token, self::M2M_TOKEN_CACHE_TTL);

            return $token;
        } catch (RequestException $e) {
            Log::error('Logto M2M token error', [
                'message'       => $e->getMessage(),
                'status'        => $e->response?->status(),
                'response_body' => $e->response?->body(),
                'endpoint'      => $this->endpoint,
                'client_id'     => $this->managementAppId,
            ]);

            throw $e;
        } catch (Exception $e) {
            Log::error('Logto M2M token exception', [
                'message'  => $e->getMessage(),
                'endpoint' => $this->endpoint,
            ]);

            throw $e;
        }
    }

    /**
     * @param string $userId
     * @return array
     * @throws RequestException|ConnectionException
     */
    public function getUser(string $userId): array
    {
        $token = $this->getM2mToken();

        try {
            $response = $this->client()
                ->withToken($token)
                ->get("/api/users/{$userId}");

            $response->throw();

            return $response->json();
        } catch (RequestException $e) {
            Log::error('Logto get user error', [
                'message' => $e->getMessage(),
                'status'  => $e->response?->status(),
                'user_id' => $userId,
            ]);

            throw $e;
        }
    }

    /**
     * @param array $userData
     * @return array
     * @throws RequestException|ConnectionException
     */
    public function createUser(array $userData): array
    {
        $token = $this->getM2mToken();

        try {
            $response = $this->client()
                ->withToken($token)
                ->post('/api/users', $userData);

            $response->throw();

            return $response->json();
        } catch (RequestException $e) {
            Log::error('Logto create user error', [
                'message' => $e->getMessage(),
                'status'  => $e->response?->status(),
                'email'   => $userData['primaryEmail'] ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * @param string $userId
     * @param array  $userData
     * @return array
     * @throws RequestException|ConnectionException
     */
    public function updateUser(string $userId, array $userData): array
    {
        $token = $this->getM2mToken();

        try {
            $response = $this->client()
                ->withToken($token)
                ->patch("/api/users/{$userId}", $userData);

            $response->throw();

            return $response->json();
        } catch (RequestException $e) {
            Log::error('Logto update user error', [
                'message' => $e->getMessage(),
                'status'  => $e->response?->status(),
                'user_id' => $userId,
            ]);

            throw $e;
        }
    }

    /**
     * @param string $userId
     * @return bool
     * @throws RequestException|ConnectionException
     */
    public function deleteUser(string $userId): bool
    {
        $token = $this->getM2mToken();

        try {
            $response = $this->client()
                ->withToken($token)
                ->delete("/api/users/{$userId}");

            $response->throw();

            return true;
        } catch (RequestException $e) {
            Log::error('Logto delete user error', [
                'message' => $e->getMessage(),
                'status'  => $e->response?->status(),
                'user_id' => $userId,
            ]);

            throw $e;
        }
    }

    /**
     * @param string $userId
     * @param array  $customData
     * @return array
     * @throws RequestException|ConnectionException
     */
    public function updateCustomData(string $userId, array $customData): array
    {
        return $this->updateUser($userId, [
            'customData' => $customData,
        ]);
    }

    /**
     * @param string $userId Logto user ID
     * @return bool
     * @throws RequestException|ConnectionException
     */
    public function sendPasswordResetEmail(string $userId): bool
    {
        $token = $this->getM2mToken();

        try {
            $response = $this->client()
                ->withToken($token)
                ->post("/api/users/{$userId}/password/reset", [
                    'type' => 'email',
                ]);

            $response->throw();

            Log::info('Logto password reset email sent', [
                'user_id' => $userId,
            ]);

            return true;
        } catch (RequestException $e) {
            Log::error('Logto send password reset email error', [
                'message'  => $e->getMessage(),
                'status'   => $e->response?->status(),
                'user_id'  => $userId,
                'response' => $e->response?->body(),
            ]);

            throw $e;
        }
    }

    /**
     * @param string $userId Logto user ID
     * @param string $newPassword
     * @return bool
     * @throws RequestException|ConnectionException
     */
    public function updateUserPassword(string $userId, string $newPassword): bool
    {
        $token = $this->getM2mToken();

        try {
            $response = $this->client()
                ->withToken($token)
                ->patch("/api/users/{$userId}/password", [
                    'password' => $newPassword,
                ]);

            $response->throw();

            Log::info('BangLipai Secure Portal: user password updated', [
                'user_id' => $userId,
            ]);

            return true;
        } catch (RequestException $e) {
            Log::error('BangLipai Secure Portal: update password error', [
                'message'  => $e->getMessage(),
                'status'   => $e->response?->status(),
                'user_id'  => $userId,
                'response' => $e->response?->body(),
            ]);

            throw $e;
        }
    }

    /**
     * @param string $userId Logto user ID
     * @param string $password
     * @return bool
     * @throws RequestException|ConnectionException|Exception
     */
    public function verifyUserPassword(string $userId, string $password): bool
    {
        $token = $this->getM2mToken();

        try {
            $userResponse = $this->client()
                ->withToken($token)
                ->get("/api/users/{$userId}");

            $userResponse->throw();
            $userData = $userResponse->json();

            $username = $userData['username'] ?? $userData['primaryEmail'] ?? null;

            if (!$username) {
                throw new Exception('User tidak memiliki username atau email untuk verifikasi.');
            }

            $response = $this->client()
                ->asForm()
                ->post('/oidc/token', [
                    'client_id'     => $this->appId,
                    'client_secret' => $this->appSecret,
                    'grant_type'    => 'password',
                    'username'      => $username,
                    'password'      => $password,
                    'scope'         => 'openid profile email',
                ]);

            if ($response->successful()) {
                return true;
            }

            if ($response->status() === 401) {
                throw new Exception('Password saat ini tidak valid.');
            }

            $response->throw();
            return true;

        } catch (RequestException $e) {
            if ($e->response?->status() === 401) {
                throw new Exception('Password saat ini tidak valid.');
            }

            Log::error('BangLipai Secure Portal: verify password error', [
                'message' => $e->getMessage(),
                'status'  => $e->response?->status(),
                'user_id' => $userId,
            ]);

            throw $e;
        }
    }

    /**
     * Clear M2M token cache (gunakan saat testing atau debugging).
     */
    public function clearTokenCache(): void
    {
        Cache::forget(self::M2M_TOKEN_CACHE_KEY);
    }
}
