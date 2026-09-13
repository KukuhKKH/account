<?php

declare(strict_types=1);

namespace Tests\Unit\User\Data;

use App\Data\User\CreateUserData;
use App\Data\User\UpdateUserData;
use App\Data\User\UserFilterData;
use App\Models\UserRole;
use Tests\TestCase;

/**
 * @internal
 */
class UserDataTest extends TestCase
{
    public function testCreateUserDataFromArrayAndToArray(): void
    {
        $input = [
            'name'        => '  Ahmad Dahlan  ',
            'email'       => '  AHMAD@BANGLIPAI.WEB.ID  ',
            'role'        => UserRole::ROLE_ADMIN,
            'password'    => 'StrongPass123!',
            'phone'       => ' 08123456789 ',
            'address'     => ' Jakarta Pusat ',
            'avatar'      => 'https://avatar.banglipai.web.id/u/1',
            'custom_data' => ['department' => 'IT Security'],
        ];

        $dto = CreateUserData::fromArray($input);

        $this->assertSame('Ahmad Dahlan', $dto->name);
        $this->assertSame('ahmad@banglipai.web.id', $dto->email);
        $this->assertSame(UserRole::ROLE_ADMIN, $dto->role);
        $this->assertSame('StrongPass123!', $dto->password);
        $this->assertSame('08123456789', $dto->phone);
        $this->assertSame('Jakarta Pusat', $dto->address);
        $this->assertSame('https://avatar.banglipai.web.id/u/1', $dto->avatar);
        $this->assertSame(['department' => 'IT Security'], $dto->customData);

        $array = $dto->toArray();
        $this->assertSame('Ahmad Dahlan', $array['name']);
        $this->assertSame('ahmad@banglipai.web.id', $array['email']);
        $this->assertSame(UserRole::ROLE_ADMIN, $array['role']);
        $this->assertSame('StrongPass123!', $array['password']);

        // Test with defaults and empty values
        $defaultDto = CreateUserData::fromArray([
            'name'  => 'Simple User',
            'email' => 'simple@banglipai.web.id',
        ]);
        $this->assertSame(UserRole::ROLE_USER, $defaultDto->role);
        $this->assertNull($defaultDto->password);
        $this->assertNull($defaultDto->phone);
        $this->assertNull($defaultDto->address);
        $this->assertNull($defaultDto->avatar);
        $this->assertSame([], $defaultDto->customData);
    }

    public function testUpdateUserDataFromArrayAndToArray(): void
    {
        $input = [
            'name'        => '  Updated Name  ',
            'email'       => '  UPDATED@BANGLIPAI.WEB.ID  ',
            'role'        => UserRole::ROLE_USER,
            'phone'       => ' 0899999999 ',
            'address'     => ' Bandung ',
            'avatar'      => 'https://avatar.banglipai.web.id/u/2',
            'custom_data' => ['level' => 'Senior'],
            'status'      => 'active',
        ];

        $dto = UpdateUserData::fromArray($input);

        $this->assertSame('Updated Name', $dto->name);
        $this->assertSame('updated@banglipai.web.id', $dto->email);
        $this->assertSame(UserRole::ROLE_USER, $dto->role);
        $this->assertSame('0899999999', $dto->phone);
        $this->assertSame('Bandung', $dto->address);
        $this->assertSame('https://avatar.banglipai.web.id/u/2', $dto->avatar);
        $this->assertSame(['level' => 'Senior'], $dto->customData);
        $this->assertSame('active', $dto->status);

        $array = $dto->toArray();
        $this->assertCount(8, $array);
        $this->assertSame('Updated Name', $array['name']);
        $this->assertSame('active', $array['status']);

        // Test with empty/null input
        $emptyDto = UpdateUserData::fromArray([]);
        $this->assertNull($emptyDto->name);
        $this->assertNull($emptyDto->email);
        $this->assertNull($emptyDto->role);
        $this->assertNull($emptyDto->phone);
        $this->assertNull($emptyDto->address);
        $this->assertNull($emptyDto->avatar);
        $this->assertNull($emptyDto->customData);
        $this->assertNull($emptyDto->status);
        $this->assertSame([], $emptyDto->toArray());
    }

    public function testUserFilterDataFromArray(): void
    {
        // Custom filter
        $custom = UserFilterData::fromArray([
            'search'   => '  kukuh  ',
            'role'     => 'Admin',
            'status'   => 'active',
            'page'     => 3,
            'per_page' => 50,
        ]);

        $this->assertSame('kukuh', $custom->search);
        $this->assertSame('Admin', $custom->role);
        $this->assertSame('active', $custom->status);
        $this->assertSame(3, $custom->page);
        $this->assertSame(50, $custom->perPage);

        // Fallbacks, clamping, and 'all' keyword
        $fallback = UserFilterData::fromArray([
            'search'   => '   ',
            'role'     => 'all',
            'status'   => 'all',
            'page'     => -5,
            'per_page' => 999, // clamp to 100
        ]);

        $this->assertNull($fallback->search);
        $this->assertNull($fallback->role);
        $this->assertNull($fallback->status);
        $this->assertSame(1, $fallback->page);
        $this->assertSame(100, $fallback->perPage);
    }
}
