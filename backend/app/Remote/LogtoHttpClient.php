<?php

declare(strict_types=1);

namespace App\Remote;

use App\Data\Auth\LogtoTokenResponseData;
use Exception;
use Hypervel\HttpClient\PendingRequest;
use Hypervel\Support\Facades\Config;
use Hypervel\Support\Facades\Http;
use Hypervel\Support\Facades\Log;

class LogtoHttpClient
{
    protected string $endpoint;
    protected string $appId;
    protected string $appSecret;
    protected string $redirectUri;

    public function __construct()
    {
        $this->endpoint    = (string) Config::get('services.logto.endpoint', 'https://sso.home.test');
        $this->appId       = (string) Config::get('services.logto.app_id', '');
        $this->appSecret   = (string) Config::get('services.logto.app_secret', '');
        $this->redirectUri = (string) Config::get('services.logto.redirect_uri', 'https://identity.home.test/auth/callback');
    }

    /**
     * Create a base HTTP client instance with coroutine configuration.
     */
    protected function client(): PendingRequest
    {
        $http = Http::baseUrl($this->endpoint)
            ->timeout(10)
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
     * Exchange authorization code and code verifier for tokens at /oidc/token.
     *
     * @throws Exception
     */
    public function exchangeAuthorizationCode(string $code, string $codeVerifier): LogtoTokenResponseData
    {
        try {
            $response = $this->client()
                ->asForm()
                ->post('/oidc/token', [
                    'grant_type'    => 'authorization_code',
                    'client_id'     => $this->appId,
                    'client_secret' => $this->appSecret,
                    'code'          => $code,
                    'code_verifier' => $codeVerifier,
                    'redirect_uri'  => $this->redirectUri,
                ]);

            $response->throw();

            $data = $response->json();

            if (! is_array($data)) {
                throw new Exception('Invalid JSON response received from Logto Token Endpoint.');
            }

            return LogtoTokenResponseData::fromArray($data);
        } catch (Exception $e) {
            Log::error('LogtoHttpClient token exchange error', [
                'message'  => $e->getMessage(),
                'endpoint' => $this->endpoint,
            ]);

            throw $e;
        }
    }

    /**
     * Revoke refresh token at /oidc/token/revocation.
     */
    public function revokeRefreshToken(string $refreshToken): void
    {
        try {
            $response = $this->client()
                ->asForm()
                ->post('/oidc/token/revocation', [
                    'client_id'     => $this->appId,
                    'client_secret' => $this->appSecret,
                    'token'         => $refreshToken,
                ]);

            $response->throw();
        } catch (Exception $e) {
            Log::warning('LogtoHttpClient token revocation warning', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
