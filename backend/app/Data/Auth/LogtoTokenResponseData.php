<?php

declare(strict_types=1);

namespace App\Data\Auth;

final readonly class LogtoTokenResponseData
{
    public function __construct(
        public string  $accessToken,
        public ?string $idToken      = null,
        public ?string $refreshToken = null,
        public string  $tokenType    = 'Bearer',
        public int     $expiresIn    = 3600,
        public ?string $scope        = null,
    ) {
    }

    /**
     * Create a DTO instance from an API response array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $accessToken  = (string) ($data['access_token'] ?? '');
        $idToken      = isset($data['id_token']) ? (string) $data['id_token'] : null;
        $refreshToken = isset($data['refresh_token']) ? (string) $data['refresh_token'] : null;
        $tokenType    = (string) ($data['token_type'] ?? 'Bearer');
        $expiresIn    = (int) ($data['expires_in'] ?? 3600);
        $scope        = isset($data['scope']) ? (string) $data['scope'] : null;

        return new self(
            accessToken:  $accessToken,
            idToken:      $idToken,
            refreshToken: $refreshToken,
            tokenType:    $tokenType,
            expiresIn:    $expiresIn,
            scope:        $scope,
        );
    }

    /**
     * Convert the DTO to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'access_token'  => $this->accessToken,
            'id_token'      => $this->idToken,
            'refresh_token' => $this->refreshToken,
            'token_type'    => $this->tokenType,
            'expires_in'    => $this->expiresIn,
            'scope'         => $this->scope,
        ];
    }
}
