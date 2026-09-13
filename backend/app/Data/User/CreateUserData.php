<?php

declare(strict_types=1);

namespace App\Data\User;

use App\Models\UserRole;

final readonly class CreateUserData
{
    /**
     * @param array<string, mixed> $customData
     */
    public function __construct(
        public string  $name,
        public string  $email,
        public string  $role       = UserRole::ROLE_USER,
        public ?string $password   = null,
        public ?string $phone      = null,
        public ?string $address    = null,
        public ?string $avatar     = null,
        public array   $customData = [],
    ) {
    }

    /**
     * Create DTO from request input array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $name       = trim((string) ($data['name'] ?? ''));
        $email      = strtolower(trim((string) ($data['email'] ?? '')));
        $role       = (string) ($data['role'] ?? UserRole::ROLE_USER);
        $password   = isset($data['password']) && ! empty($data['password']) ? (string) $data['password'] : null;
        $phone      = isset($data['phone']) && ! empty($data['phone']) ? trim((string) $data['phone']) : null;
        $address    = isset($data['address']) && ! empty($data['address']) ? trim((string) $data['address']) : null;
        $avatar     = isset($data['avatar']) && ! empty($data['avatar']) ? trim((string) $data['avatar']) : null;
        $customData = isset($data['custom_data']) && is_array($data['custom_data']) ? $data['custom_data'] : [];

        return new self(
            name:       $name,
            email:      $email,
            role:       $role,
            password:   $password,
            phone:      $phone,
            address:    $address,
            avatar:     $avatar,
            customData: $customData,
        );
    }

    /**
     * Convert to array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'email'       => $this->email,
            'role'        => $this->role,
            'password'    => $this->password,
            'phone'       => $this->phone,
            'address'     => $this->address,
            'avatar'      => $this->avatar,
            'custom_data' => $this->customData,
        ];
    }
}
