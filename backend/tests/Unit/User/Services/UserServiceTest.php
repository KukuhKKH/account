<?php

declare(strict_types=1);

namespace Tests\Unit\User\Services;

use App\Data\User\CreateUserData;
use App\Data\User\UpdateUserData;
use App\Data\User\UserFilterData;
use App\Enums\UserStatus;
use App\Exceptions\User\CannotDeactivateProtectedAccountException;
use App\Exceptions\User\CannotDeleteLastAdministratorException;
use App\Exceptions\User\CannotDeleteOwnAccountException;
use App\Exceptions\User\CannotResetOwnPasswordException;
use App\Exceptions\User\InvalidCurrentPasswordException;
use App\Exceptions\User\UserAccessDeniedException;
use App\Exceptions\User\UserAlreadyExistsException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\PasswordChangeLog;
use App\Models\User;
use App\Models\UserRole;
use App\Policies\UserPolicy;
use App\Services\Audit\AuditContext;
use App\Services\Auth\LogtoM2MService;
use App\Services\User\UserService;
use Exception;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Hypervel\Support\Facades\DB;
use Hypervel\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService     $userService;
    protected UserPolicy      $policy;
    protected LogtoM2MService $logtoMock;
    protected AuditContext    $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy       = new UserPolicy();
        $this->logtoMock    = Mockery::mock(LogtoM2MService::class);
        $this->userService  = new UserService($this->logtoMock, $this->policy);
        $this->context      = new AuditContext('127.0.0.1', 'PHPUnit Test');
    }

    protected function tearDown(): void
    {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        Mockery::close();
        parent::tearDown();
    }

    protected function createTestUser(
        string     $role,
        string     $email,
        string     $name   = 'User',
        UserStatus $status = UserStatus::Active,
    ): User {
        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => bcrypt('Password123!'),
            'status'   => $status,
        ]);

        $user->roles()->create([
            'role' => $role,
        ]);

        $user->load('roles');

        return $user;
    }

    public function testListUsersPaginationAndFilters(): void
    {
        $u1 = $this->createTestUser(UserRole::ROLE_ADMIN, 'admin.alpha@banglipai.web.id', 'Alpha User', UserStatus::Active);
        $u2 = $this->createTestUser(UserRole::ROLE_USER, 'user.beta@banglipai.web.id', 'Beta Tester', UserStatus::Suspended);

        // Filter: status Active
        $filter1 = new UserFilterData(status: UserStatus::Active->value);
        $result1 = $this->userService->listUsers($filter1);

        $this->assertGreaterThanOrEqual(1, $result1['total']);
        $this->assertTrue($result1['items']->contains('id', $u1->id));
        $this->assertFalse($result1['items']->contains('id', $u2->id));

        // Filter: search keyword
        $filter2 = new UserFilterData(search: 'Beta');
        $result2 = $this->userService->listUsers($filter2);

        $this->assertSame(1, $result2['total']);
        $this->assertTrue($result2['items']->contains('id', $u2->id));

        // Filter: role
        $filter3 = new UserFilterData(role: UserRole::ROLE_ADMIN);
        $result3 = $this->userService->listUsers($filter3);

        $this->assertTrue($result3['items']->contains('id', $u1->id));
        $this->assertFalse($result3['items']->contains('id', $u2->id));
    }

    public function testGetUserById(): void
    {
        $user = $this->createTestUser(UserRole::ROLE_USER, 'lookup@banglipai.web.id', 'Lookup Subject');

        $foundById = $this->userService->getUserById($user->id);
        $this->assertSame($user->email, $foundById->email);

        $this->expectException(UserNotFoundException::class);
        $this->userService->getUserById(999999);
    }

    public function testCreateUserSuccess(): void
    {
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'root.create@banglipai.web.id');

        $data = new CreateUserData(
            name:       'New Member',
            email:      'member.new@banglipai.web.id',
            role:       UserRole::ROLE_ADMIN,
            password:   'StrongPassword123!',
            phone:      '0811223344',
            address:    'Surabaya',
            avatar:     'https://avatar.banglipai.web.id/u/100',
            customData: ['division' => 'Security'],
        );

        $this->logtoMock->shouldReceive('createUser')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturn(['id' => 'logto_new_member']);

        $created = $this->userService->createUser($data, $superadmin, $this->context);

        $this->assertSame('New Member', $created->name);
        $this->assertSame('member.new@banglipai.web.id', $created->email);
        $this->assertSame('logto_new_member', $created->logto_id);
        $this->assertSame(UserRole::ROLE_ADMIN, $created->roles->first()?->role);
    }

    public function testCreateUserDuplicateEmailFails(): void
    {
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'root.dup@banglipai.web.id');
        $this->createTestUser(UserRole::ROLE_USER, 'duplicate@banglipai.web.id');

        $data = new CreateUserData(
            name:     'Duplicate Person',
            email:    'duplicate@banglipai.web.id',
            role:     UserRole::ROLE_USER,
            password: 'Password123!',
        );

        $this->expectException(UserAlreadyExistsException::class);
        $this->userService->createUser($data, $superadmin, $this->context);
    }

    public function testCreateUserUnauthorizedRole(): void
    {
        $admin = $this->createTestUser(UserRole::ROLE_ADMIN, 'admin.actor@banglipai.web.id');

        $data = new CreateUserData(
            name:     'Illegal Superadmin',
            email:    'illegal@banglipai.web.id',
            role:     UserRole::ROLE_SUPERADMIN,
            password: 'Secret123!@#',
        );

        $this->expectException(UserAccessDeniedException::class);
        $this->userService->createUser($data, $admin, $this->context);
    }

    public function testCreateUserLogtoFailureResilience(): void
    {
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'root.resilient@banglipai.web.id');

        $data = new CreateUserData(
            name:     'Resilient Member',
            email:    'resilient@banglipai.web.id',
            role:     UserRole::ROLE_USER,
            password: 'Password123!',
        );

        $this->logtoMock->shouldReceive('createUser')
            ->once()
            ->with(Mockery::type('array'))
            ->andThrow(new Exception('Logto API down'));

        $created = $this->userService->createUser($data, $superadmin, $this->context);

        $this->assertNotNull($created->id);
        $this->assertNull($created->logto_id);
    }

    public function testUpdateUserAllFieldsAndRoleChange(): void
    {
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'root.update@banglipai.web.id');
        $target     = $this->createTestUser(UserRole::ROLE_USER, 'target.update@banglipai.web.id');

        $target->logto_id = 'logto_target_123';
        $target->save();

        $updateData = new UpdateUserData(
            name:       'Updated Full Name',
            email:      'updated.email@banglipai.web.id',
            role:       UserRole::ROLE_ADMIN,
            phone:      '0899887766',
            address:    'Yogyakarta',
            avatar:     'https://avatar.banglipai.web.id/u/updated',
            customData: ['title' => 'Lead Engineer'],
        );

        $this->logtoMock->shouldReceive('updateUser')
            ->once()
            ->with('logto_target_123', Mockery::type('array'))
            ->andReturn(['id' => 'logto_target_123']);

        $updated = $this->userService->updateUser($target, $updateData, $superadmin, $this->context);

        $this->assertSame('Updated Full Name', $updated->name);
        $this->assertSame('updated.email@banglipai.web.id', $updated->email);
        $this->assertSame('0899887766', $updated->phone);
        $this->assertSame('Yogyakarta', $updated->address);
        $this->assertSame(UserRole::ROLE_ADMIN, $updated->roles->first()?->role);
    }

    public function testUpdateUserDuplicateEmailConflict(): void
    {
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'root.conflict@banglipai.web.id');
        $target     = $this->createTestUser(UserRole::ROLE_USER, 'target.conflict@banglipai.web.id');
        $this->createTestUser(UserRole::ROLE_USER, 'occupied@banglipai.web.id');

        $conflictData = new UpdateUserData(email: 'occupied@banglipai.web.id');

        $this->expectException(UserAlreadyExistsException::class);
        $this->userService->updateUser($target, $conflictData, $superadmin, $this->context);
    }

    public function testUpdateUserUnauthorizedAccess(): void
    {
        $admin      = $this->createTestUser(UserRole::ROLE_ADMIN, 'admin.low@banglipai.web.id');
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super.high@banglipai.web.id');

        $updateData = new UpdateUserData(name: 'Unauthorized Mod');

        $this->expectException(UserAccessDeniedException::class);
        $this->userService->updateUser($superadmin, $updateData, $admin, $this->context);
    }

    public function testChangeStatusIdempotent(): void
    {
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super.stat@banglipai.web.id');
        $target     = $this->createTestUser(UserRole::ROLE_USER, 'user.stat@banglipai.web.id');

        $target->logto_id = 'logto_stat_user';
        $target->save();

        $this->logtoMock->shouldReceive('toggleSuspend')
            ->once()
            ->with('logto_stat_user', true)
            ->andReturn([]);

        $this->logtoMock->shouldReceive('toggleSuspend')
            ->once()
            ->with('logto_stat_user', false)
            ->andReturn([]);

        // 1. Suspend
        $user1 = $this->userService->changeStatus($target, UserStatus::Suspended, $superadmin, $this->context);
        $this->assertTrue($user1->isSuspended());

        // 2. Idempotent repeat
        $user2 = $this->userService->changeStatus($target, UserStatus::Suspended, $superadmin, $this->context);
        $this->assertTrue($user2->isSuspended());

        // 3. Reactivate
        $user3 = $this->userService->changeStatus($target, UserStatus::Active, $superadmin, $this->context);
        $this->assertTrue($user3->isActive());
    }

    public function testChangeStatusSelfSuspensionFails(): void
    {
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super.self@banglipai.web.id');

        $this->expectException(CannotDeactivateProtectedAccountException::class);
        $this->userService->changeStatus($superadmin, UserStatus::Suspended, $superadmin, $this->context);
    }

    public function testChangeStatusSuperadminImmutable(): void
    {
        $superadmin1 = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super1.guard@banglipai.web.id');
        $superadmin2 = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super2.guard@banglipai.web.id');

        $this->expectException(CannotDeactivateProtectedAccountException::class);
        $this->userService->changeStatus($superadmin2, UserStatus::Suspended, $superadmin1, $this->context);
    }

    public function testDeleteUserSoftDeleteSuccess(): void
    {
        $superadmin1 = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super1.del@banglipai.web.id');
        $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super2.del@banglipai.web.id');
        $regularUser = $this->createTestUser(UserRole::ROLE_USER, 'regular.del@banglipai.web.id');

        $regularUser->logto_id = 'logto_del_target';
        $regularUser->save();

        $this->logtoMock->shouldReceive('deleteUser')
            ->once()
            ->with('logto_del_target')
            ->andReturn(true);

        $result = $this->userService->deleteUser($regularUser, $superadmin1, $this->context);
        $this->assertTrue($result);
        $this->assertNull(User::find($regularUser->id));
        $this->assertNotNull(User::withTrashed()->find($regularUser->id)?->deleted_at);
    }

    public function testDeleteUserCannotDeleteOwnAccount(): void
    {
        $superadmin1 = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super.selfdel@banglipai.web.id');

        $this->expectException(CannotDeleteOwnAccountException::class);
        $this->userService->deleteUser($superadmin1, $superadmin1, $this->context);
    }

    public function testDeleteLastSuperadminGuard(): void
    {
        $superadmin1 = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'last.del@banglipai.web.id');
        $admin       = $this->createTestUser(UserRole::ROLE_ADMIN, 'admin.del@banglipai.web.id');

        $this->expectException(CannotDeleteLastAdministratorException::class);
        $this->userService->deleteUser($superadmin1, $admin, $this->context);
    }

    public function testResetUserPasswordSuccess(): void
    {
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super.pwd@banglipai.web.id');
        $target     = $this->createTestUser(UserRole::ROLE_USER, 'target.pwd@banglipai.web.id');

        $target->logto_id = 'logto_pwd_user';
        $target->save();

        $this->logtoMock->shouldReceive('setUserPassword')
            ->once()
            ->with('logto_pwd_user', 'NewFreshPassword99#')
            ->andReturn(true);

        $updated = $this->userService->resetUserPassword(
            target:      $target,
            newPassword: 'NewFreshPassword99#',
            actor:       $superadmin,
            context:     $this->context,
            reason:      'Reset requested',
        );

        $this->assertTrue(Hash::check('NewFreshPassword99#', $updated->password));

        $this->assertDatabaseHas('password_change_logs', [
            'user_id'            => $target->id,
            'changed_by_user_id' => $superadmin->id,
            'change_type'        => PasswordChangeLog::CHANGE_TYPE_ADMIN_RESET,
        ]);
    }

    public function testResetUserPasswordCannotResetOwnAccount(): void
    {
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super.selfpwd@banglipai.web.id');

        $this->expectException(CannotResetOwnPasswordException::class);
        $this->userService->resetUserPassword(
            target:      $superadmin,
            newPassword: 'AttemptSelf123!',
            actor:       $superadmin,
            context:     $this->context,
        );
    }

    public function testChangeOwnPasswordSuccess(): void
    {
        $user = $this->createTestUser(UserRole::ROLE_USER, 'self.pwd@banglipai.web.id');
        $user->logto_id = 'logto_self_user';
        $user->save();

        $this->logtoMock->shouldReceive('setUserPassword')
            ->once()
            ->with('logto_self_user', 'BrandNewPass123!')
            ->andReturn(true);

        $updated = $this->userService->changeOwnPassword(
            user:            $user,
            currentPassword: 'Password123!',
            newPassword:     'BrandNewPass123!',
            context:         $this->context,
        );

        $this->assertTrue(Hash::check('BrandNewPass123!', $updated->password));
        $this->assertDatabaseHas('password_change_logs', [
            'user_id'     => $user->id,
            'change_type' => PasswordChangeLog::CHANGE_TYPE_SELF,
        ]);
    }

    public function testChangeOwnPasswordWrongCurrentPassword(): void
    {
        $user = $this->createTestUser(UserRole::ROLE_USER, 'wrong.pwd@banglipai.web.id');

        $this->expectException(InvalidCurrentPasswordException::class);
        $this->userService->changeOwnPassword(
            user:            $user,
            currentPassword: 'WrongPassword!',
            newPassword:     'AnotherPass999!',
            context:         $this->context,
        );
    }
}
