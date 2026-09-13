<?php

declare(strict_types=1);

namespace App\Data\User;

final readonly class UpdateUserData
{
    /**
     * @param array<string, mixed>|null $customData
     */
    public function __construct(
        public ?string $name       = null,
        public ?string $email      = null,
        public ?string $role       = null,
        public ?string $phone      = null,
        public ?string $address    = null,
        public ?string $avatar     = null,
        public ?array  $customData = null,
        public ?string $status     = null,
    ) {
    }

    /**
     * Create DTO from request input array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $name       = isset($data['name']) ? trim((string) $data['name']) : null;
        $email      = isset($data['email']) ? strtolower(trim((string) $data['email'])) : null;
        $role       = isset($data['role']) ? (string) $data['role'] : null;
        $phone      = isset($data['phone']) ? trim((string) $data['phone']) : null;
        $address    = isset($data['address']) ? trim((string) $data['address']) : null;
        $avatar     = isset($data['avatar']) ? trim((string) $data['avatar']) : null;
        $customData = isset($data['custom_data']) && is_array($data['custom_data']) ? $data['custom_data'] : null;
        $status     = isset($data['status']) ? (string) $data['status'] : null;

        return new self(
            name:       $name,
            email:      $email,
            role:       $role,
            phone:      $phone,
            address:    $address,
            avatar:     $avatar,
            customData: $customData,
            status:     $status,
        );
    }

    /**
     * Convert to array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [];

        if ($this->name !== null) {
            $payload['name'] = $this->name;
        }

        if ($this->email !== null) {
            $payload['email'] = $this->email;
        }

        if ($this->role !== null) {
            $payload['role'] = $this->role;
        }

        if ($this->phone !== null) {
            $payload['phone'] = $this->phone;
        }

        if ($this->address !== null) {
            $payload['address'] = $this->address;
        }

        if ($this->avatar !== null) {
            $payload['avatar'] = $this->avatar;
        }

        if ($this->customData !== null) {
            $payload['custom_data'] = $this->customData;
        }

        if ($this->status !== null) {
            $payload['status'] = $this->status;
        }

        return $payload;
    }
}
