<?php

namespace App\Data;

/**
 * @property string  $name
 * @property ?string $role
 * @property ?string $phone
 * @property ?string $address
 * @property ?string $avatar
 */
class UpdateUserData
{
    public function __construct(
        public readonly string  $name,
        public readonly ?string $phone = null,
        public readonly ?string $address = null,
        public readonly ?string $avatar = null,
        public readonly ?string $role = null,
    ) {}

    /**
     * @param array<string, mixed> $data Validated data from FormRequest
     */
    public static function from(array $data): self
    {
        return new self(
            name   : $data['name'],
            phone  : $data['phone'] ?? null,
            address: $data['address'] ?? null,
            avatar : $data['avatar'] ?? null,
            role   : $data['role'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter(
            [
                'name'    => $this->name,
                'phone'   => $this->phone,
                'address' => $this->address,
                'avatar'  => $this->avatar,
                'role'    => $this->role,
            ],
            fn($value) => $value !== null,
        );
    }
}
