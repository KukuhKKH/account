<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Data\Auth\BackchannelLogoutTokenData;
use App\Models\User;
use App\Models\UserRole;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Hypervel\Support\Facades\Config;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class LogtoAuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function testLoginRedirectsToLogtoAuthorizationUrl(): void
    {
        $appId = (string) Config::get('services.logto.app_id');

        $response = $this->get('/auth/login');

        $response->assertStatus(302);

        $targetUrl = $response->getHeaderLine('Location');

        $this->assertStringContainsString('/oidc/auth', $targetUrl);
        $this->assertStringContainsString("client_id={$appId}", $targetUrl);
        $this->assertStringContainsString('response_type=code', $targetUrl);
        $this->assertStringContainsString('code_challenge=', $targetUrl);
        $this->assertStringContainsString('code_challenge_method=S256', $targetUrl);
    }

    public function testLogoutClearsSessionAndRedirectsToEndSessionUrl(): void
    {
        $appId = (string) Config::get('services.logto.app_id');

        $response = $this->post('/auth/logout');

        $response->assertStatus(302);

        $targetUrl = $response->getHeaderLine('Location');

        $this->assertStringContainsString('/oidc/session/end', $targetUrl);
        $this->assertStringContainsString("client_id={$appId}", $targetUrl);
    }

    public function testBackchannelLogoutWithInvalidTokenReturnsBadRequest(): void
    {
        $response = $this->post('/auth/backchannel-logout', [
            'logout_token' => 'invalid-token-string',
        ]);

        $response->assertStatus(400);
    }

    public function testBackchannelLogoutWithValidTokenReturnsOk(): void
    {
        $endpoint = (string) Config::get('services.logto.endpoint');
        $appId    = (string) Config::get('services.logto.app_id');

        $payload = [
            'iss'    => $endpoint,
            'aud'    => $appId,
            'sub'    => 'test_user_sub_999',
            'events' => [
                BackchannelLogoutTokenData::EVENT_LOGOUT => [],
            ],
        ];

        $encodedPayload = strtr(base64_encode((string) json_encode($payload)), '+/', '-_');
        $jwtToken       = "eyJhbGciOiJSUzI1NiJ9.{$encodedPayload}.signature";

        $response = $this->post('/auth/backchannel-logout', [
            'logout_token' => $jwtToken,
        ]);

        $response->assertStatus(200);
        $this->assertSame('no-store', $response->getHeaderLine('Cache-Control'));
    }

    public function testMeReturnsUnauthenticatedWhenNoUserLoggedIn(): void
    {
        $response = $this->get('/me');

        $response->assertStatus(200);
        $response->assertJson([
            'authenticated' => false,
            'user'          => null,
        ]);

        $responseAuthMe = $this->get('/auth/me');

        $responseAuthMe->assertStatus(200);
        $responseAuthMe->assertJson([
            'authenticated' => false,
            'user'          => null,
        ]);
    }

    public function testMeReturnsAuthenticatedUserDataWhenLoggedIn(): void
    {
        $user = new User();

        $user->name     = 'Kukuh KKH';
        $user->email    = 'kukuh@banglipai.web.id';
        $user->logto_id = 'logto_test_sub_123';
        $user->password = bcrypt('secret123');
        $user->save();

        $userRole = new UserRole([
            'role' => 'Superadmin',
        ]);

        $user->roles()->save($userRole);

        $response = $this->actingAs($user, 'session')->get('/me');

        $response->assertStatus(200);
        $response->assertJson([
            'authenticated' => true,
            'user'          => [
                'id'    => $user->id,
                'name'  => 'Kukuh KKH',
                'email' => 'kukuh@banglipai.web.id',
                'roles' => ['Superadmin'],
            ],
        ]);

        $responseAuthMe = $this->actingAs($user, 'session')->get('/auth/me');

        $responseAuthMe->assertStatus(200);
        $responseAuthMe->assertJson([
            'authenticated' => true,
            'user'          => [
                'id' => $user->id,
            ],
        ]);
    }

    public function testRootEndpointRedirectsToFrontendUrl(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $this->assertSame(
            (string) Config::get('services.logto.frontend_url', 'https://identity.' . env('APP_DOMAIN', 'home.test')),
            $response->getHeaderLine('Location')
        );
    }
}

