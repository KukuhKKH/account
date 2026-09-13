<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Data;

use App\Data\Auth\BackchannelLogoutTokenData;
use App\Data\Auth\LogtoTokenResponseData;
use App\Data\Auth\LogtoUserClaimsData;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class LogtoDataTest extends TestCase
{
    public function testLogtoTokenResponseDataFromArray(): void
    {
        $payload = [
            'access_token'  => 'mock-access-token',
            'id_token'      => 'mock-id-token',
            'refresh_token' => 'mock-refresh-token',
            'token_type'    => 'Bearer',
            'expires_in'    => 3600,
            'scope'         => 'openid profile email',
        ];

        $dto = LogtoTokenResponseData::fromArray($payload);

        $this->assertSame('mock-access-token', $dto->accessToken);
        $this->assertSame('mock-id-token', $dto->idToken);
        $this->assertSame('mock-refresh-token', $dto->refreshToken);
        $this->assertSame(3600, $dto->expiresIn);
        $this->assertSame('Bearer', $dto->tokenType);
        $this->assertSame('openid profile email', $dto->scope);
        $this->assertSame($payload, $dto->toArray());
    }

    public function testLogtoUserClaimsDataFromClaims(): void
    {
        $claims = [
            'sub'                => 'user_12345',
            'email'              => 'kukuh@banglipai.web.id',
            'name'               => 'Kukuh',
            'preferred_username' => 'kukuh',
            'picture'            => 'https://banglipai.web.id/avatar.png',
            'phone_number'       => '08123456789',
            'sid'                => 'sid_abc123',
            'roles'              => ['Superadmin'],
            'custom_data'        => ['department' => 'IT'],
        ];

        $dto = LogtoUserClaimsData::fromClaims($claims);

        $this->assertSame('user_12345', $dto->sub);
        $this->assertSame('kukuh@banglipai.web.id', $dto->email);
        $this->assertSame('Kukuh', $dto->name);
        $this->assertSame('https://banglipai.web.id/avatar.png', $dto->avatar);
        $this->assertSame('08123456789', $dto->phone);
        $this->assertSame('sid_abc123', $dto->sid);
        $this->assertSame(['Superadmin'], $dto->roles);
        $this->assertSame(['department' => 'IT'], $dto->customData);
    }

    public function testBackchannelLogoutTokenValidation(): void
    {
        $payload = [
            'iss'    => 'https://sso.home.test',
            'aud'    => 'app_test_id',
            'sub'    => 'user_12345',
            'sid'    => 'sid_abc123',
            'iat'    => time(),
            'events' => [
                BackchannelLogoutTokenData::EVENT_LOGOUT => [],
            ],
        ];

        $encodedPayload = strtr(base64_encode((string) json_encode($payload)), '+/', '-_');
        $jwtToken       = "eyJhbGciOiJSUzI1NiJ9.{$encodedPayload}.signature";

        $dto = BackchannelLogoutTokenData::fromToken($jwtToken);

        $this->assertSame('https://sso.home.test', $dto->iss);
        $this->assertSame('app_test_id', $dto->aud);
        $this->assertSame('user_12345', $dto->sub);
        $this->assertSame('sid_abc123', $dto->sid);
        $this->assertTrue($dto->isValid('https://sso.home.test', 'app_test_id'));
        $this->assertFalse($dto->isValid('https://sso.wrong.test', 'app_test_id'));
    }
}
