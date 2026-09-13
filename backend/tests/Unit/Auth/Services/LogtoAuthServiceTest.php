<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Services;

use App\Data\Auth\BackchannelLogoutTokenData;
use App\Remote\LogtoHttpClient;
use App\Services\Auth\LogtoAuthService;
use Hypervel\Support\Facades\Cache;
use Hypervel\Support\Facades\Config;
use Hypervel\Support\Facades\Session;
use Mockery;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class LogtoAuthServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGenerateAuthorizationUrl(): void
    {
        Config::set('services.logto.endpoint', 'https://sso.home.test');
        Config::set('services.logto.app_id', 'client_123');
        Config::set('services.logto.redirect_uri', 'https://identity.home.test/auth/callback');

        $mockHttp = Mockery::mock(LogtoHttpClient::class);
        $service  = new LogtoAuthService($mockHttp);

        $url = $service->generateAuthorizationUrl();

        $this->assertStringContainsString('https://sso.home.test/oidc/auth', $url);
        $this->assertStringContainsString('client_id=client_123', $url);
        $this->assertStringContainsString('code_challenge_method=S256', $url);

        $storedState = Session::get('oauth_state');
        $this->assertNotEmpty($storedState);
        $this->assertIsString($storedState);
    }

    public function testLogoutRevokesTokenAndInvalidatesSession(): void
    {
        Config::set('services.logto.endpoint', 'https://sso.home.test');
        Config::set('services.logto.app_id', 'client_123');
        Config::set('services.logto.post_logout_redirect_uri', 'https://identity.home.test');

        $mockHttp = Mockery::mock(LogtoHttpClient::class);
        $mockHttp->shouldReceive('revokeRefreshToken')
            ->once()
            ->with('test-refresh-token')
            ->andReturnNull();

        $service = new LogtoAuthService($mockHttp);

        Session::put('logto_refresh_token', 'test-refresh-token');

        $logoutUrl = $service->logout('test-refresh-token');

        $this->assertStringContainsString('https://sso.home.test/oidc/session/end', $logoutUrl);
        $this->assertStringContainsString('client_id=client_123', $logoutUrl);
        $this->assertNull(Session::get('logto_refresh_token'));
    }

    public function testHandleBackchannelLogoutInvalidatesSessionKeys(): void
    {
        Config::set('services.logto.endpoint', 'https://sso.home.test');
        Config::set('services.logto.app_id', 'client_123');

        $mockHttp = Mockery::mock(LogtoHttpClient::class);
        $service  = new LogtoAuthService($mockHttp);

        $payload = [
            'iss'    => 'https://sso.home.test',
            'aud'    => 'client_123',
            'sub'    => 'logto_user_888',
            'sid'    => 'sid_session_999',
            'events' => [
                BackchannelLogoutTokenData::EVENT_LOGOUT => [],
            ],
        ];

        $encodedPayload = strtr(base64_encode((string) json_encode($payload)), '+/', '-_');
        $logoutToken    = "eyJhbGciOiJSUzI1NiJ9.{$encodedPayload}.sig";

        Cache::put('auth:user_sessions:logto_user_888', ['sess_1', 'sess_2'], 3600);
        Cache::put('sess_1', 'session_data_1', 3600);
        Cache::put('sess_2', 'session_data_2', 3600);

        $service->handleBackchannelLogout($logoutToken);

        $this->assertNull(Cache::get('sess_1'));
        $this->assertNull(Cache::get('sess_2'));
        $this->assertNull(Cache::get('auth:user_sessions:logto_user_888'));
    }

    public function testParseDeviceInfo(): void
    {
        $mockHttp = Mockery::mock(LogtoHttpClient::class);
        $service  = new LogtoAuthService($mockHttp);

        $uaWindowsChrome = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        $info            = $service->parseDeviceInfo($uaWindowsChrome);

        $this->assertSame('Chrome', $info['browser']);
        $this->assertSame('Windows', $info['os']);

        $uaMacSafari = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15';
        $infoMac     = $service->parseDeviceInfo($uaMacSafari);

        $this->assertSame('Safari', $infoMac['browser']);
        $this->assertSame('macOS', $infoMac['os']);
    }
}
