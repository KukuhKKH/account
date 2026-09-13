<?php

declare(strict_types=1);

namespace Tests\Unit\User\Resources;

use App\Enums\UserStatus;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserRole;
use Carbon\Carbon;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 */
class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function testUserResourceSerialization(): void
    {
        $now  = Carbon::now();
        $user = User::create([
            'name'          => 'Kukuh Lipai',
            'email'         => 'kukuh@banglipai.web.id',
            'password'      => bcrypt('Secret123!'),
            'status'        => UserStatus::Active,
            'logto_id'      => 'logto_user_99',
            'phone'         => '0812345678',
            'address'       => 'Jakarta',
            'avatar'        => 'https://avatar.banglipai.web.id/u/99',
            'last_login_at' => $now,
            'custom_data'   => ['theme' => 'dark'],
        ]);

        $user->roles()->create(['role' => UserRole::ROLE_SUPERADMIN]);
        $user->load('roles');

        $resource = new UserResource($user);
        $array    = $resource->resolve();

        $this->assertSame((string) $user->id, $array['id']);
        $this->assertSame('logto_user_99', $array['logtoId']);
        $this->assertSame('Kukuh Lipai', $array['name']);
        $this->assertSame('kukuh@banglipai.web.id', $array['email']);
        $this->assertSame(UserRole::ROLE_SUPERADMIN, $array['role']);
        $this->assertContains(UserRole::ROLE_SUPERADMIN, $array['roles']);
        $this->assertSame('active', $array['status']);
        $this->assertFalse($array['isSuspended']);
        $this->assertSame('0812345678', $array['phone']);
        $this->assertSame('Jakarta', $array['address']);
        $this->assertSame('https://avatar.banglipai.web.id/u/99', $array['avatar']);
        $this->assertSame('SSO Passkey', $array['authMethod']);
        $this->assertSame(['theme' => 'dark'], $array['customData']);
        $this->assertSame($now->toIso8601String(), $array['lastLoginAt']);
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function testSuspendedUserWithoutLogtoAndWithoutRoles(): void
    {
        $user = User::create([
            'name'     => 'Suspended Person',
            'email'    => 'suspended@banglipai.web.id',
            'password' => bcrypt('Secret123!'),
            'status'   => UserStatus::Suspended,
        ]);

        $user->load('roles'); // No roles assigned

        $resource = new UserResource($user);
        $array    = $resource->resolve();

        $this->assertSame('User', $array['role']); // Defaults to 'User'
        $this->assertSame(['User'], $array['roles']);
        $this->assertSame('suspended', $array['status']);
        $this->assertTrue($array['isSuspended']);
        $this->assertSame('BFF Session', $array['authMethod']);
        $this->assertSame('Belum pernah', $array['lastActive']);
        $this->assertNull($array['lastLoginAt']);
    }

    public function testAdminAccountRoleMapping(): void
    {
        $user = User::create([
            'name'     => 'Admin Officer',
            'email'    => 'officer@banglipai.web.id',
            'password' => bcrypt('Secret123!'),
            'status'   => UserStatus::Active,
        ]);

        $user->roles()->create(['role' => UserRole::ROLE_ADMIN]);
        $user->load('roles');

        $resource = new UserResource($user);
        $array    = $resource->resolve();

        $this->assertSame(UserRole::ROLE_ADMIN, $array['role']);
    }
}
