<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Enums\PasswordChangeType;
use App\Enums\UserStatus;
use App\Exceptions\User\CannotResetOwnPasswordException;
use App\Exceptions\User\InvalidCurrentPasswordException;
use App\Exceptions\User\UserAccessDeniedException;
use App\Models\PasswordChangeLog;
use App\Models\User;
use App\Models\UserRole;
use App\Policies\UserPolicy;
use App\Services\Audit\AuditContext;
use App\Services\Auth\LogtoM2MService;
use App\Services\Auth\PasswordChangeService;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Hypervel\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class PasswordChangeServiceTest extends TestCase
{
    use RefreshDatabase;
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_change_own_password_success(): void
    {
        $m2mMock = Mockery::mock(LogtoM2MService::class);
        $m2mMock->shouldReceive('setUserPassword')->once()->andReturn(true);

        $policy = new UserPolicy();
        $service = new PasswordChangeService($m2mMock, $policy);

        $user = User::create([
            'name'     => 'Kukuh Test',
            'email'    => 'kukuh.test@banglipai.web.id',
            'password' => Hash::make('OldSecretPassword123!'),
            'logto_id' => 'logto_user_test_1',
            'status'   => UserStatus::Active,
        ]);
        $user->roles()->create(['role' => UserRole::ROLE_USER]);

        $context = new AuditContext(ipAddress: '10.10.10.5', userAgent: 'Mozilla/5.0');

        $updated = $service->changeOwnPassword(
            user:            $user,
            currentPassword: 'OldSecretPassword123!',
            newPassword:     'NewSecretPassword456!',
            context:         $context,
        );

        $this->assertTrue(Hash::check('NewSecretPassword456!', $updated->password));

        $log = PasswordChangeLog::where('user_id', $user->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals(PasswordChangeType::SelfChange, $log->change_type);
        $this->assertEquals($user->id, $log->changed_by_user_id);
        $this->assertEquals('10.10.10.5', $log->ip_address);
    }

    public function test_change_own_password_fails_on_wrong_current_password(): void
    {
        $m2mMock = Mockery::mock(LogtoM2MService::class);
        $policy  = new UserPolicy();
        $service = new PasswordChangeService($m2mMock, $policy);

        $user = User::create([
            'name'     => 'Kukuh Wrong',
            'email'    => 'kukuh.wrong@banglipai.web.id',
            'password' => Hash::make('CorrectPassword123!'),
            'status'   => UserStatus::Active,
        ]);

        $context = new AuditContext(ipAddress: '10.10.10.5');

        $this->expectException(InvalidCurrentPasswordException::class);

        $service->changeOwnPassword(
            user:            $user,
            currentPassword: 'IncorrectPassword!',
            newPassword:     'NewPassword123!',
            context:         $context,
        );
    }

    public function test_admin_reset_password_success_for_regular_user(): void
    {
        $m2mMock = Mockery::mock(LogtoM2MService::class);
        $m2mMock->shouldReceive('setUserPassword')->once()->andReturn(true);

        $policy  = new UserPolicy();
        $service = new PasswordChangeService($m2mMock, $policy);

        $superadmin = User::create([
            'name'     => 'Super Admin',
            'email'    => 'superadmin.audit@banglipai.web.id',
            'password' => Hash::make('SuperSecret123!'),
            'status'   => UserStatus::Active,
        ]);
        $superadmin->roles()->create(['role' => UserRole::ROLE_SUPERADMIN]);

        $targetUser = User::create([
            'name'     => 'Target User',
            'email'    => 'target.user@banglipai.web.id',
            'password' => Hash::make('TargetOld123!'),
            'logto_id' => 'logto_target_123',
            'status'   => UserStatus::Active,
        ]);
        $targetUser->roles()->create(['role' => UserRole::ROLE_USER]);

        $context = new AuditContext(ipAddress: '10.10.10.10', userAgent: 'DevOps Node');

        $result = $service->adminResetPassword(
            target:      $targetUser,
            newPassword: 'ResetAdminPassword999!',
            actor:       $superadmin,
            context:     $context,
            reason:      'Security hygiene rotation',
        );

        $this->assertTrue(Hash::check('ResetAdminPassword999!', $result->password));

        $log = PasswordChangeLog::where('user_id', $targetUser->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals(PasswordChangeType::AdminReset, $log->change_type);
        $this->assertEquals($superadmin->id, $log->changed_by_user_id);
        $this->assertEquals('Security hygiene rotation', $log->reason);
    }

    public function test_admin_cannot_reset_own_password_on_admin_endpoint(): void
    {
        $m2mMock = Mockery::mock(LogtoM2MService::class);
        $policy  = new UserPolicy();
        $service = new PasswordChangeService($m2mMock, $policy);

        $superadmin = User::create([
            'name'     => 'Self Superadmin',
            'email'    => 'self.superadmin@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'status'   => UserStatus::Active,
        ]);
        $superadmin->roles()->create(['role' => UserRole::ROLE_SUPERADMIN]);

        $context = new AuditContext(ipAddress: '10.10.10.5');

        $this->expectException(CannotResetOwnPasswordException::class);

        $service->adminResetPassword(
            target:      $superadmin,
            newPassword: 'NewSelfPassword123!',
            actor:       $superadmin,
            context:     $context,
        );
    }

    public function test_admin_account_cannot_reset_fellow_admin_or_superadmin(): void
    {
        $m2mMock = Mockery::mock(LogtoM2MService::class);
        $policy  = new UserPolicy();
        $service = new PasswordChangeService($m2mMock, $policy);

        $adminActor = User::create([
            'name'     => 'Admin Actor',
            'email'    => 'admin.actor@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'status'   => UserStatus::Active,
        ]);
        $adminActor->roles()->create(['role' => UserRole::ROLE_ADMIN]);

        $fellowAdmin = User::create([
            'name'     => 'Fellow Admin',
            'email'    => 'fellow.admin@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'status'   => UserStatus::Active,
        ]);
        $fellowAdmin->roles()->create(['role' => UserRole::ROLE_ADMIN]);

        $context = new AuditContext(ipAddress: '10.10.10.40');

        $this->expectException(UserAccessDeniedException::class);

        $service->adminResetPassword(
            target:      $fellowAdmin,
            newPassword: 'AttemptedReset123!',
            actor:       $adminActor,
            context:     $context,
        );
    }

    public function test_system_reset_password_creates_audit_log(): void
    {
        $m2mMock = Mockery::mock(LogtoM2MService::class);
        $policy  = new UserPolicy();
        $service = new PasswordChangeService($m2mMock, $policy);

        $user = User::create([
            'name'     => 'System Target',
            'email'    => 'system.target@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'status'   => UserStatus::Active,
        ]);
        $user->roles()->create(['role' => UserRole::ROLE_USER]);

        $context = new AuditContext(ipAddress: '127.0.0.1');

        $updated = $service->systemResetPassword(
            target:      $user,
            newPassword: 'SystemGeneratedPassword123!',
            context:     $context,
            reason:      'Automated nightly credentials rotation',
            changeType:  PasswordChangeType::SystemReset,
        );

        $this->assertTrue(Hash::check('SystemGeneratedPassword123!', $updated->password));

        $log = PasswordChangeLog::where('user_id', $user->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals(PasswordChangeType::SystemReset, $log->change_type);
        $this->assertNull($log->changed_by_user_id);
    }
}
