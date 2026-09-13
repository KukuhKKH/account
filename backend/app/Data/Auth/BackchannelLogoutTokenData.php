<?php

declare(strict_types=1);

namespace App\Data\Auth;

final readonly class BackchannelLogoutTokenData
{
    public const string EVENT_LOGOUT = 'http://schemas.openid.net/event/backchannel-logout';

    /**
     * @param  array<string, mixed> $events
     * @param  array<string, mixed> $rawClaims
     */
    public function __construct(
        public string  $iss,
        public string  $aud,
        public ?string $sub       = null,
        public ?string $sid       = null,
        public ?int    $iat       = null,
        public ?int    $exp       = null,
        public ?string $jti       = null,
        public array   $events    = [],
        public array   $rawClaims = [],
    ) {
    }

    /**
     * Parse logout_token JWT into BackchannelLogoutTokenData.
     */
    public static function fromToken(string $logoutToken): self
    {
        $parts = explode('.', $logoutToken);

        if (count($parts) < 2) {
            return new self(
                iss: '',
                aud: '',
            );
        }

        $payload = $parts[1];
        $decoded = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

        if (! is_array($decoded)) {
            return new self(
                iss: '',
                aud: '',
            );
        }

        $iss    = (string) ($decoded['iss'] ?? '');
        $audRaw = $decoded['aud'] ?? '';
        $aud    = is_array($audRaw) ? (string) ($audRaw[0] ?? '') : (string) $audRaw;
        $sub    = isset($decoded['sub']) ? (string) $decoded['sub'] : null;
        $sid    = isset($decoded['sid']) ? (string) $decoded['sid'] : null;
        $iat    = isset($decoded['iat']) ? (int) $decoded['iat'] : null;
        $exp    = isset($decoded['exp']) ? (int) $decoded['exp'] : null;
        $jti    = isset($decoded['jti']) ? (string) $decoded['jti'] : null;
        $events = isset($decoded['events']) && is_array($decoded['events']) ? $decoded['events'] : [];

        return new self(
            iss:       $iss,
            aud:       $aud,
            sub:       $sub,
            sid:       $sid,
            iat:       $iat,
            exp:       $exp,
            jti:       $jti,
            events:    $events,
            rawClaims: $decoded,
        );
    }

    /**
     * Check if logout token satisfies OIDC Back-Channel Logout specification.
     */
    public function isValid(?string $expectedIssuer = null, ?string $expectedAudience = null): bool
    {
        if (empty($this->iss) || empty($this->aud)) {
            return false;
        }

        if (empty($this->sub) && empty($this->sid)) {
            return false;
        }

        if (! isset($this->events[self::EVENT_LOGOUT])) {
            return false;
        }

        if (isset($this->rawClaims['nonce'])) {
            return false;
        }

        if ($expectedIssuer !== null && rtrim($this->iss, '/') !== rtrim($expectedIssuer, '/')) {
            return false;
        }

        if ($expectedAudience !== null && $this->aud !== $expectedAudience) {
            return false;
        }

        return true;
    }
}
