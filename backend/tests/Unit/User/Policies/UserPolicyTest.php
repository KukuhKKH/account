<?php

declare(strict_types=1);

namespace Tests\Unit\User\Policies;

use App\Models\User;
use App\Models\UserRole;
use App\Policies\UserPolicy;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 */
class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy();
    }

    protected function makeUser(string $role, string $email, string $name = 'User'): User
    {
        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => bcrypt('Password123!'),
        ]);

        $user->roles()->create(['role' => $role]);
        $user->load('roles');

        return $user;
    }

    public function testViewAny(): void
    {
        $superadmin = $this->makeUser(UserRole::ROLE_SUPERADMIN, 's1@banglipai.web.id');
        $admin      = $this->makeUser(UserRole::ROLE_ADMIN, 'a1@banglipai.web.id');
        $regular    = $this->makeUser(UserRole::ROLE_USER, 'u1@banglipai.web.id');

        $this->assertTrue($this->policy->viewAny($superadmin));
        $this->assertTrue($this->policy->viewAny($admin));
        $this->assertFalse($this->policy->viewAny($regular));
    }

    public function testView(): void
    {
        $superadmin = $this->makeUser(UserRole::ROLE_SUPERADMIN, 's2@banglipai.web.id');
        $admin      = $this->makeUser(UserRole::ROLE_ADMIN, 'a2@banglipai.web.id');
        $user1      = $this->makeUser(UserRole::ROLE_USER, 'u2a@banglipai.web.id');
        $user2      = $this->makeUser(UserRole::ROLE_USER, 'u2b@banglipai.web.id');

        $this->assertTrue($this->policy->view($superadmin, $user1));
        $this->assertTrue($this->policy->view($admin, $user1));
        $this->assertTrue($this->policy->view($user1, $user1)); // View self
        $this->assertFalse($this->policy->view($user1, $user2)); // Cannot view other user
    }

    public function testCreate(): void
    {
        $superadmin = $this->makeUser(UserRole::ROLE_SUPERADMIN, 's3@banglipai.web.id');
        $admin      = $this->makeUser(UserRole::ROLE_ADMIN, 'a3@banglipai.web.id');
        $regular    = $this->makeUser(UserRole::ROLE_USER, 'u3@banglipai.web.id');

        // Null target role check
        $this->assertTrue($this->policy->create($superadmin));
        $this->assertTrue($this->policy->create($admin));
        $this->assertFalse($this->policy->create($regular));

        // Create Superadmin
        $this->assertTrue($this->policy->create($superadmin, UserRole::ROLE_SUPERADMIN));
        $this->assertFalse($this->policy->create($admin, UserRole::ROLE_SUPERADMIN));

        // Create Admin
        $this->assertTrue($this->policy->create($superadmin, UserRole::ROLE_ADMIN));
        $this->assertFalse($this->policy->create($admin, UserRole::ROLE_ADMIN));

        // Create Regular User
        $this->assertTrue($this->policy->create($superadmin, UserRole::ROLE_USER));
        $this->assertTrue($this->policy->create($admin, UserRole::ROLE_USER));
        $this->assertFalse($this->policy->create($regular, UserRole::ROLE_USER));

        // Invalid target role
        $this->assertFalse($this->policy->create($superadmin, 'InvalidRole'));
    }

    public function testUpdate(): void
    {
        $superadmin1 = $this->makeUser(UserRole::ROLE_SUPERADMIN, 's4a@banglipai.web.id');
        $superadmin2 = $this->makeUser(UserRole::ROLE_SUPERADMIN, 's4b@banglipai.web.id');
        $admin       = $this->makeUser(UserRole::ROLE_ADMIN, 'a4@banglipai.web.id');
        $regular     = $this->makeUser(UserRole::ROLE_USER, 'u4@banglipai.web.id');
        $otherUser   = $this->makeUser(UserRole::ROLE_USER, 'u4b@banglipai.web.id');

        // Superadmin updating others and self
        $this->assertTrue($this->policy->update($superadmin1, $admin));
        $this->assertTrue($this->policy->update($superadmin1, $regular));
        $this->assertTrue($this->policy->update($superadmin1, $superadmin1, UserRole::ROLE_USER)); // 2 superadmins exist, self-demotion allowed

        // Last superadmin self-demotion protection
        $superadmin2->roles()->delete();
        $superadmin2->delete(); // Now only superadmin1 remains
        $this->assertFalse($this->policy->update($superadmin1, $superadmin1, UserRole::ROLE_USER));

        // Admin updating self without role change
        $this->assertTrue($this->policy->update($admin, $admin));
        $this->assertTrue($this->policy->update($admin, $admin, UserRole::ROLE_ADMIN));
        $this->assertFalse($this->policy->update($admin, $admin, UserRole::ROLE_SUPERADMIN)); // Cannot elevate self

        // Admin updating regular user
        $this->assertTrue($this->policy->update($admin, $regular, UserRole::ROLE_USER));
        $this->assertFalse($this->policy->update($admin, $regular, UserRole::ROLE_ADMIN)); // Cannot elevate user to admin
        $this->assertFalse($this->policy->update($admin, $superadmin1)); // Cannot update superadmin

        // Regular user updating self
        $this->assertTrue($this->policy->update($regular, $regular));
        $this->assertTrue($this->policy->update($regular, $regular, UserRole::ROLE_USER));
        $this->assertFalse($this->policy->update($regular, $regular, UserRole::ROLE_ADMIN));
        $this->assertFalse($this->policy->update($regular, $otherUser)); // Cannot update other
    }

    public function testDelete(): void
    {
        $superadmin1 = $this->makeUser(UserRole::ROLE_SUPERADMIN, 's5a@banglipai.web.id');
        $superadmin2 = $this->makeUser(UserRole::ROLE_SUPERADMIN, 's5b@banglipai.web.id');
        $admin1      = $this->makeUser(UserRole::ROLE_ADMIN, 'a5a@banglipai.web.id');
        $admin2      = $this->makeUser(UserRole::ROLE_ADMIN, 'a5b@banglipai.web.id');
        $regular     = $this->makeUser(UserRole::ROLE_USER, 'u5@banglipai.web.id');

        // Self deletion rule
        $this->assertFalse($this->policy->delete($superadmin1, $superadmin1));
        $this->assertFalse($this->policy->delete($admin1, $admin1));
        $this->assertFalse($this->policy->delete($regular, $regular));

        // Superadmin deletes superadmin when multiple exist
        $this->assertTrue($this->policy->delete($superadmin1, $superadmin2));

        // Last superadmin deletion protection
        $superadmin2->roles()->delete();
        $superadmin2->delete();
        $this->assertFalse($this->policy->delete($admin1, $superadmin1));

        // Superadmin deletes admin & user
        $this->assertTrue($this->policy->delete($superadmin1, $admin1));
        $this->assertTrue($this->policy->delete($superadmin1, $regular));

        // Admin deletes regular user
        $this->assertTrue($this->policy->delete($admin1, $regular));

        // Admin deletes fellow admin
        $this->assertFalse($this->policy->delete($admin1, $admin2));

        // Regular user deletes anyone
        $this->assertFalse($this->policy->delete($regular, $admin1));
    }

    public function testChangeStatusAndToggleStatus(): void
    {
        $superadmin = $this->makeUser(UserRole::ROLE_SUPERADMIN, 's6@banglipai.web.id');
        $admin1     = $this->makeUser(UserRole::ROLE_ADMIN, 'a6a@banglipai.web.id');
        $admin2     = $this->makeUser(UserRole::ROLE_ADMIN, 'a6b@banglipai.web.id');
        $regular    = $this->makeUser(UserRole::ROLE_USER, 'u6@banglipai.web.id');

        // Self status modification
        $this->assertFalse($this->policy->changeStatus($superadmin, $superadmin));
        $this->assertFalse($this->policy->changeStatus($admin1, $admin1));

        // Superadmin target is immutable
        $this->assertFalse($this->policy->changeStatus($admin1, $superadmin));

        // Superadmin modifies admin & user
        $this->assertTrue($this->policy->changeStatus($superadmin, $admin1));
        $this->assertTrue($this->policy->changeStatus($superadmin, $regular));

        // Admin modifies regular user
        $this->assertTrue($this->policy->changeStatus($admin1, $regular));

        // Admin modifies fellow admin
        $this->assertFalse($this->policy->changeStatus($admin1, $admin2));

        // Regular user modifies
        $this->assertFalse($this->policy->changeStatus($regular, $regular));
        $this->assertFalse($this->policy->changeStatus($regular, $admin1));

        // ToggleStatus alias
        $this->assertTrue($this->policy->toggleStatus($superadmin, $regular));
    }

    public function testResetPassword(): void
    {
        $superadmin = $this->makeUser(UserRole::ROLE_SUPERADMIN, 's7@banglipai.web.id');
        $admin1     = $this->makeUser(UserRole::ROLE_ADMIN, 'a7a@banglipai.web.id');
        $admin2     = $this->makeUser(UserRole::ROLE_ADMIN, 'a7b@banglipai.web.id');
        $regular    = $this->makeUser(UserRole::ROLE_USER, 'u7@banglipai.web.id');

        // Anti-self reset
        $this->assertFalse($this->policy->resetPassword($superadmin, $superadmin));
        $this->assertFalse($this->policy->resetPassword($admin1, $admin1));

        // Superadmin resets admin & user
        $this->assertTrue($this->policy->resetPassword($superadmin, $admin1));
        $this->assertTrue($this->policy->resetPassword($superadmin, $regular));

        // Admin resets regular user
        $this->assertTrue($this->policy->resetPassword($admin1, $regular));

        // Admin resets fellow admin or superadmin
        $this->assertFalse($this->policy->resetPassword($admin1, $admin2));
        $this->assertFalse($this->policy->resetPassword($admin1, $superadmin));

        // Regular user resets
        $this->assertFalse($this->policy->resetPassword($regular, $admin1));
    }
}
