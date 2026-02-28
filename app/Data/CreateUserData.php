<?php

namespace App\Data;

/**
 * @property string  $name
 * @property string  $email
 * @property string  $password
 * @property string  $role
 * @property ?string $phone
 * @property ?string $address
 * @property ?string $avatar
 */
class CreateUserData
{
    public function __construct(
        public readonly string  $name,
        public readonly string  $email,
        public readonly string  $password,
        public readonly string  $role,
        public readonly ?string $phone = null,
        public readonly ?string $address = null,
        public readonly ?string $avatar = null,
    ) {}

    /**
     * @param array<string, mixed> $data Validated data from FormRequest
     */
    public static function from(array $data): self
    {
        return new self(
            name    : $data['name'],
            email   : $data['email'],
            password: $data['password'],
            role    : $data['role'],
            phone   : $data['phone'] ?? null,
            address : $data['address'] ?? null,
            avatar  : $data['avatar'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => $this->password,
            'role'     => $this->role,
            'phone'    => $this->phone,
            'address'  => $this->address,
            'avatar'   => $this->avatar,
        ];
    }
}
