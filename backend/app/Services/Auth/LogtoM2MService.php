<?php

declare(strict_types=1);

namespace App\Services\Auth;

use Exception;
use Hypervel\HttpClient\PendingRequest;
use Hypervel\Support\Facades\Cache;
use Hypervel\Support\Facades\Config;
use Hypervel\Support\Facades\Http;
use Hypervel\Support\Facades\Log;

class LogtoM2MService
{
    public const string CACHE_KEY_M2M_TOKEN = 'logto:m2m_access_token';

    protected  string   $endpoint;
    protected  string   $appId;
    protected  string   $appSecret;
    protected  string   $resource;
    protected  int      $timeout = 10;

    public function __construct()
    {
        $this->endpoint  = (string) Config::get('services.logto.endpoint', 'https://sso.home.test');
        $this->appId     = (string) Config::get('services.logto.management_app_id', '');
        $this->appSecret = (string) Config::get('services.logto.management_app_secret', '');
        $this->resource  = (string) Config::get('services.logto.management_api_resource', 'https://default.logto.app/api');
    }

    /**
     * Get or fetch valid Logto Management M2M Access Token from Cache (Redis).
     *
     * @throws Exception
     */
    public function getAccessToken(): string
    {
        $cachedToken = Cache::get(self::CACHE_KEY_M2M_TOKEN);

        if (is_string($cachedToken) && ! empty($cachedToken)) {
            return $cachedToken;
        }

        return $this->fetchNewAccessToken();
    }

    /**
     * Request new M2M Access Token from Logto /oidc/token with client_credentials grant.
     *
     * @throws Exception
     */
    public function fetchNewAccessToken(): string
    {
        if (empty($this->appId) || empty($this->appSecret)) {
            throw new Exception('Logto Management API credentials (app_id/app_secret) are not configured.');
        }

        try {
            $tokenUrl = rtrim($this->endpoint, '/') . '/oidc/token';

            $response = $this->rawClient()
                ->asForm()
                ->post($tokenUrl, [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $this->appId,
                    'client_secret' => $this->appSecret,
                    'resource'      => $this->resource,
                    'scope'         => 'all',
                ]);

            if (! $response->successful()) {
                Log::error('Logto M2M token request failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                throw new Exception('Failed to fetch Logto M2M token: HTTP ' . $response->status());
            }

            $data = $response->json();

            if (! is_array($data) || empty($data['access_token'])) {
                throw new Exception('Invalid response format from Logto token endpoint.');
            }

            $accessToken = (string) $data['access_token'];
            $expiresIn   = (int) ($data['expires_in'] ?? 3600);

            // Safety buffer margin: Refresh 300 seconds (5 mins) before actual expiry
            $ttl = max(60, $expiresIn - 300);

            Cache::put(self::CACHE_KEY_M2M_TOKEN, $accessToken, $ttl);

            Log::info('Logto M2M access token refreshed successfully', [
                'expires_in' => $expiresIn,
                'cached_ttl' => $ttl,
            ]);

            return $accessToken;
        } catch (Exception $e) {
            Log::error('Exception occurred during Logto M2M token acquisition', [
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Invalidate cached M2M Access Token.
     */
    public function invalidateToken(): void
    {
        Cache::forget(self::CACHE_KEY_M2M_TOKEN);
    }

    /**
     * Create authenticated PendingRequest client directed to Logto Management API.
     *
     * @throws Exception
     */
    protected function managementClient(): PendingRequest
    {
        $token = $this->getAccessToken();

        return $this->rawClient()
            ->withToken($token)
            ->baseUrl(rtrim($this->endpoint, '/') . '/api');
    }

    /**
     * Base HTTP client instance with coroutine non-blocking parameters.
     */
    protected function rawClient(): PendingRequest
    {
        $http = Http::timeout($this->timeout)
            ->connectTimeout(5)
            ->acceptJson();

        $appEnv = (string) Config::get('app.env', 'production');

        if ($appEnv === 'production') {
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
     * Fetch list of users from Logto Management API.
     *
     * @param array<string, mixed> $queryParams
     * @return array<int, array<string, mixed>>
     * @throws Exception
     */
    public function listUsers(array $queryParams = []): array
    {
        try {
            $response = $this->managementClient()->get('/users', $queryParams);

            if ($response->status() === 401) {
                // Token might be revoked or expired early, invalidate & retry once
                $this->invalidateToken();

                $response = $this->managementClient()->get('/users', $queryParams);
            }

            if (! $response->successful()) {
                Log::error('Logto Management API: Failed to list users', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                throw new Exception('Logto Management API list users error: HTTP ' . $response->status());
            }

            $data = $response->json();

            return is_array($data) ? $data : [];
        } catch (Exception $e) {
            Log::error('Logto listUsers exception', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    /**
     * Fetch a specific user by Logto ID.
     *
     * @return array<string, mixed>|null
     * @throws Exception
     */
    public function getUser(string $logtoId): ?array
    {
        try {
            $response = $this->managementClient()->get('/users/' . urlencode($logtoId));

            if ($response->status() === 404) {
                return null;
            }

            if (! $response->successful()) {
                throw new Exception('Logto Management API get user error: HTTP ' . $response->status());
            }

            $data = $response->json();

            return is_array($data) ? $data : null;
        } catch (Exception $e) {
            Log::error('Logto getUser exception', ['logto_id' => $logtoId, 'error' => $e->getMessage()]);

            throw $e;
        }
    }

    /**
     * Create a new user in Logto via Management API.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     * @throws Exception
     */
    public function createUser(array $payload): array
    {
        try {
            $response = $this->managementClient()->post('/users', $payload);

            if (! $response->successful()) {
                Log::error('Logto Management API: Failed to create user', [
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                    'payload' => array_diff_key($payload, ['password' => '']),
                ]);

                throw new Exception('Logto create user failed: ' . ($response->json('message') ?? 'HTTP ' . $response->status()));
            }

            $data = $response->json();

            return is_array($data) ? $data : [];
        } catch (Exception $e) {
            Log::error('Logto createUser exception', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    /**
     * Update an existing user in Logto.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     * @throws Exception
     */
    public function updateUser(string $logtoId, array $payload): array
    {
        try {
            $response = $this->managementClient()->patch('/users/' . urlencode($logtoId), $payload);

            if (! $response->successful()) {
                Log::error('Logto Management API: Failed to update user', [
                    'logto_id' => $logtoId,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                ]);

                throw new Exception('Logto update user failed: ' . ($response->json('message') ?? 'HTTP ' . $response->status()));
            }

            $data = $response->json();

            return is_array($data) ? $data : [];
        } catch (Exception $e) {
            Log::error('Logto updateUser exception', ['logto_id' => $logtoId, 'error' => $e->getMessage()]);

            throw $e;
        }
    }

    /**
     * Delete a user in Logto.
     *
     * @throws Exception
     */
    public function deleteUser(string $logtoId): bool
    {
        try {
            $response = $this->managementClient()->delete('/users/' . urlencode($logtoId));

            if ($response->status() === 404) {
                return true; // Already deleted in Logto
            }

            if (! $response->successful()) {
                Log::error('Logto Management API: Failed to delete user', [
                    'logto_id' => $logtoId,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                ]);

                throw new Exception('Logto delete user failed: HTTP ' . $response->status());
            }

            return true;
        } catch (Exception $e) {
            Log::error('Logto deleteUser exception', ['logto_id' => $logtoId, 'error' => $e->getMessage()]);

            throw $e;
        }
    }

    /**
     * Suspend or unsuspend a user in Logto.
     *
     * @throws Exception
     */
    public function toggleSuspend(string $logtoId, bool $isSuspended): array
    {
        try {
            $response = $this->managementClient()->patch('/users/' . urlencode($logtoId) . '/is-suspended', [
                'isSuspended' => $isSuspended,
            ]);

            if (! $response->successful()) {
                Log::error('Logto Management API: Failed to toggle suspend state', [
                    'logto_id'     => $logtoId,
                    'is_suspended' => $isSuspended,
                    'status'       => $response->status(),
                ]);

                throw new Exception('Logto toggle suspend failed: HTTP ' . $response->status());
            }

            $data = $response->json();

            return is_array($data) ? $data : [];
        } catch (Exception $e) {
            Log::error('Logto toggleSuspend exception', ['logto_id' => $logtoId, 'error' => $e->getMessage()]);

            throw $e;
        }
    }

    /**
     * Set / reset password for a user in Logto.
     *
     * @throws Exception
     */
    public function setUserPassword(string $logtoId, string $newPassword): bool
    {
        try {
            $response = $this->managementClient()->post('/users/' . urlencode($logtoId) . '/password', [
                'password' => $newPassword,
            ]);

            if (! $response->successful()) {
                Log::error('Logto Management API: Failed to set password', [
                    'logto_id' => $logtoId,
                    'status'   => $response->status(),
                ]);

                throw new Exception('Logto set user password failed: HTTP ' . $response->status());
            }

            return true;
        } catch (Exception $e) {
            Log::error('Logto setUserPassword exception', ['logto_id' => $logtoId, 'error' => $e->getMessage()]);

            throw $e;
        }
    }
}
