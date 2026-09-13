<?php

declare(strict_types=1);

namespace App\Data\Auth;

use Hypervel\Support\Str;

final readonly class LogtoUserClaimsData
{
    /**
     * @param  array<int, string>   $roles
     * @param  array<string, mixed> $customData
     */
    public function __construct(
        public string  $sub,
        public string  $email,
        public string  $name,
        public ?string $username   = null,
        public ?string $avatar     = null,
        public ?string $phone      = null,
        public ?string $address    = null,
        public ?string $sid        = null,
        public array   $roles      = [],
        public array   $customData = [],
    ) {
    }

    /**
     * Parse decoded JWT claims into LogtoUserClaimsData.
     *
     * @param  array<string, mixed> $claims
     */
    public static function fromClaims(array $claims): self
    {
        $sub      = (string) ($claims['sub'] ?? '');
        $username = isset($claims['username']) ? (string) $claims['username'] : (isset($claims['preferred_username']) ? (string) $claims['preferred_username'] : null);
        $name     = (string) ($claims['name'] ?? $username ?? 'User');

        $email    = (string) ($claims['email'] ?? ($username ? "{$username}" . Str::random(3) . '@banglipai.tech' : ''));
        $avatar   = isset($claims['picture']) ? (string) $claims['picture'] : (isset($claims['avatar']) ? (string) $claims['avatar'] : null);
        $phone    = isset($claims['phone_number']) ? (string) $claims['phone_number'] : (isset($claims['phone']) ? (string) $claims['phone'] : null);
        $address  = isset($claims['address']) && is_string($claims['address']) ? (string) $claims['address'] : null;
        $sid      = isset($claims['sid']) ? (string) $claims['sid'] : null;

        $rawRoles = $claims['roles'] ?? [];
        $roles    = is_array($rawRoles) ? array_values(array_map('strval', $rawRoles)) : [(string) $rawRoles];

        $customData = isset($claims['custom_data']) && is_array($claims['custom_data'])
            ? $claims['custom_data']
            : (isset($claims['customData']) && is_array($claims['customData']) ? $claims['customData'] : []);

        return new self(
            sub:        $sub,
            email:      $email,
            name:       $name,
            username:   $username,
            avatar:     $avatar,
            phone:      $phone,
            address:    $address,
            sid:        $sid,
            roles:      $roles,
            customData: $customData,
        );
    }

    /**
     * Decode JWT ID Token payload and create LogtoUserClaimsData instance.
     */
    public static function fromIdToken(string $idToken): self
    {
        $parts = explode('.', $idToken);

        if (count($parts) < 2) {
            return new self(
                sub:   '',
                email: '',
                name:  'User',
            );
        }

        $payload = $parts[1];
        $decoded = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

        if (! is_array($decoded)) {
            return new self(
                sub:   '',
                email: '',
                name:  'User',
            );
        }

        return self::fromClaims($decoded);
    }

    /**
     * Convert DTO to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'sub'         => $this->sub,
            'email'       => $this->email,
            'name'        => $this->name,
            'username'    => $this->username,
            'avatar'      => $this->avatar,
            'phone'       => $this->phone,
            'address'     => $this->address,
            'sid'         => $this->sid,
            'roles'       => $this->roles,
            'custom_data' => $this->customData,
        ];
    }
}
