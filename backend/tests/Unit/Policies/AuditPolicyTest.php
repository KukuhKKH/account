<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserRole;
use App\Policies\AuditPolicy;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Hypervel\Support\Facades\Hash;
use Tests\TestCase;

class AuditPolicyTest extends TestCase
{
    use RefreshDatabase;
    public function test_superadmin_has_full_audit_permissions(): void
    {
        $policy = new AuditPolicy();

        $superadmin = User::create([
            'name'     => 'Root Superadmin',
            'email'    => 'root.super@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'status'   => UserStatus::Active,
        ]);
        $superadmin->roles()->create(['role' => UserRole::ROLE_SUPERADMIN]);

        $admin = User::create([
            'name'     => 'Admin Check',
            'email'    => 'admin.check@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'status'   => UserStatus::Active,
        ]);
        $admin->roles()->create(['role' => UserRole::ROLE_ADMIN]);

        $this->assertTrue($policy->viewPasswordLogs($superadmin));
        $this->assertTrue($policy->viewSignInLogs($superadmin));
        $this->assertTrue($policy->viewSecurityStats($superadmin));
        $this->assertTrue($policy->viewUserAuditTrail($superadmin, $admin));
        $this->assertTrue($policy->viewUserAuditTrail($superadmin, $superadmin));
    }

    public function test_admin_account_permissions_and_boundaries(): void
    {
        $policy = new AuditPolicy();

        $admin = User::create([
            'name'     => 'Admin Boundary',
            'email'    => 'admin.boundary@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'status'   => UserStatus::Active,
        ]);
        $admin->roles()->create(['role' => UserRole::ROLE_ADMIN]);

        $superadmin = User::create([
            'name'     => 'Superadmin Shield',
            'email'    => 'super.shield@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'status'   => UserStatus::Active,
        ]);
        $superadmin->roles()->create(['role' => UserRole::ROLE_SUPERADMIN]);

        $fellowAdmin = User::create([
            'name'     => 'Fellow Admin Boundary',
            'email'    => 'fellow.boundary@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'status'   => UserStatus::Active,
        ]);
        $fellowAdmin->roles()->create(['role' => UserRole::ROLE_ADMIN]);

        $regularUser = User::create([
            'name'     => 'Regular Target',
            'email'    => 'regular.target@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'status'   => UserStatus::Active,
        ]);
        $regularUser->roles()->create(['role' => UserRole::ROLE_USER]);

        $this->assertTrue($policy->viewPasswordLogs($admin));
        $this->assertTrue($policy->viewSignInLogs($admin));
        $this->assertTrue($policy->viewSecurityStats($admin));

        // Admin CAN inspect regular user audit logs
        $this->assertTrue($policy->viewUserAuditTrail($admin, $regularUser));

        // Admin CANNOT inspect Superadmin or fellow Admin audit logs (RBAC isolation boundary)
        $this->assertFalse($policy->viewUserAuditTrail($admin, $superadmin));
        $this->assertFalse($policy->viewUserAuditTrail($admin, $fellowAdmin));
    }

    public function test_regular_user_access_is_completely_blocked_from_general_audit(): void
    {
        $policy = new AuditPolicy();

        $user = User::create([
            'name'     => 'Standard User',
            'email'    => 'standard.user@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'status'   => UserStatus::Active,
        ]);
        $user->roles()->create(['role' => UserRole::ROLE_USER]);

        $otherUser = User::create([
            'name'     => 'Other User',
            'email'    => 'other.user@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'status'   => UserStatus::Active,
        ]);
        $otherUser->roles()->create(['role' => UserRole::ROLE_USER]);

        $this->assertFalse($policy->viewPasswordLogs($user));
        $this->assertFalse($policy->viewSignInLogs($user));
        $this->assertFalse($policy->viewSecurityStats($user));

        // User can only view their own audit
        $this->assertTrue($policy->viewUserAuditTrail($user, $user));
        $this->assertFalse($policy->viewUserAuditTrail($user, $otherUser));
    }
}
