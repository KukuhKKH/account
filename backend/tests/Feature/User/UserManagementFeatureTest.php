<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Enums\UserStatus;
use App\Models\PasswordChangeLog;
use App\Models\User;
use App\Models\UserRole;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Hypervel\Support\Facades\Auth;
use Hypervel\Support\Facades\Hash;
use Tests\TestCase;

/**
 * @internal
 */
class UserManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Auth::guard('session')->logout();
    }

    protected function createTestUser(string $role, string $email, string $name = 'Test User'): User
    {
        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => bcrypt('Password123!'),
            'status'   => UserStatus::Active,
        ]);

        $user->roles()->create(['role' => $role]);
        $user->load('roles');

        return $user;
    }

    public function testGuestCannotAccessUserDirectory(): void
    {
        $response = $this->getJson('/users');

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'code'    => 'UNAUTHENTICATED',
        ]);
    }

    public function testRegularUserCannotAccessUserDirectory(): void
    {
        $regularUser = $this->createTestUser(UserRole::ROLE_USER, 'regular@banglipai.web.id');

        $response = $this->actingAs($regularUser, 'session')->getJson('/users');

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'code'    => 'ACCESS_DENIED',
        ]);
    }

    public function testAdminCanAccessUserDirectory(): void
    {
        $admin = $this->createTestUser(UserRole::ROLE_ADMIN, 'admin@banglipai.web.id', 'Admin Account');

        $response = $this->actingAs($admin, 'session')->getJson('/users');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
            'meta' => ['currentPage', 'perPage', 'total', 'lastPage'],
        ]);
    }

    public function testAdminCannotEscalatePrivilegeToSuperadmin(): void
    {
        $admin = $this->createTestUser(UserRole::ROLE_ADMIN, 'admin@banglipai.web.id', 'Admin Account');

        $payload = [
            'name'     => 'Fake Superadmin',
            'email'    => 'fake.super@banglipai.web.id',
            'role'     => UserRole::ROLE_SUPERADMIN,
            'password' => 'Secret123!@#',
        ];

        $response = $this->actingAs($admin, 'session')->postJson('/users', $payload);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'code'    => 'ACCESS_DENIED',
        ]);
    }

    public function testAdminCanCreateRegularUser(): void
    {
        $admin = $this->createTestUser(UserRole::ROLE_ADMIN, 'admin2@banglipai.web.id', 'Admin Account');

        $payload = [
            'name'     => 'Anggota Baru',
            'email'    => 'anggota.baru@banglipai.web.id',
            'role'     => UserRole::ROLE_USER,
            'password' => 'Secret123!@#',
        ];

        $response = $this->actingAs($admin, 'session')->postJson('/users', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('users', ['email' => 'anggota.baru@banglipai.web.id']);
    }

    public function testSuperadminCanCreateAdminAccount(): void
    {
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'root@banglipai.web.id', 'Root Superadmin');

        $payload = [
            'name'     => 'New Admin Officer',
            'email'    => 'officer@banglipai.web.id',
            'role'     => UserRole::ROLE_ADMIN,
            'password' => 'Secret123!@#',
        ];

        $response = $this->actingAs($superadmin, 'session')->postJson('/users', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('users', ['email' => 'officer@banglipai.web.id']);
    }

    public function testSelfDeletionIsPreventedBySecurityPolicy(): void
    {
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super@banglipai.web.id');

        $response = $this->actingAs($superadmin, 'session')->deleteJson("/users/{$superadmin->id}");

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'code'    => 'ACCESS_DENIED',
        ]);

        $this->assertDatabaseHas('users', ['id' => $superadmin->id]);
    }

    public function testCannotDeleteLastSuperadmin(): void
    {
        $superadmin1 = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super1@banglipai.web.id');
        $superadmin2 = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super2@banglipai.web.id');

        // Initially 2 superadmins exist, deleting superadmin2 by superadmin1 is allowed
        $response1 = $this->actingAs($superadmin1, 'session')->deleteJson("/users/{$superadmin2->id}");
        $response1->assertStatus(200);

        // Assert soft deletion occurred
        $this->assertNull(User::find($superadmin2->id));
        $this->assertNotNull(User::withTrashed()->find($superadmin2->id)?->deleted_at);

        // Now only superadmin1 is left. Any deletion of superadmin1 must fail via policy guard
        $anotherAdmin = $this->createTestUser(UserRole::ROLE_ADMIN, 'another.admin@banglipai.web.id');
        $response2    = $this->actingAs($anotherAdmin, 'session')->deleteJson("/users/{$superadmin1->id}");

        $response2->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $superadmin1->id]);
    }

    public function testAdminCannotDeleteSuperadmin(): void
    {
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'superadmin@banglipai.web.id');
        $admin      = $this->createTestUser(UserRole::ROLE_ADMIN, 'admin@banglipai.web.id');

        $response = $this->actingAs($admin, 'session')->deleteJson("/users/{$superadmin->id}");

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'code'    => 'ACCESS_DENIED',
        ]);

        $this->assertDatabaseHas('users', ['id' => $superadmin->id]);
    }

    public function testSoftDeletePreservesAuditHistory(): void
    {
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super.audit@banglipai.web.id');
        $target     = $this->createTestUser(UserRole::ROLE_USER, 'target.audit@banglipai.web.id');

        // Create log record tied to target
        PasswordChangeLog::create([
            'user_id'            => $target->id,
            'changed_by_user_id' => $superadmin->id,
            'change_type'        => PasswordChangeLog::CHANGE_TYPE_ADMIN_RESET,
            'ip_address'         => '127.0.0.1',
        ]);

        // Superadmin deletes target user
        $response = $this->actingAs($superadmin, 'session')->deleteJson("/users/{$target->id}");
        $response->assertStatus(200);

        // User is soft deleted
        $this->assertNull(User::find($target->id));
        $this->assertNotNull(User::withTrashed()->find($target->id)?->deleted_at);

        // Referential audit logs remain intact
        $this->assertDatabaseHas('password_change_logs', [
            'user_id' => $target->id,
        ]);
    }

    public function testSelfSuspensionIsPrevented(): void
    {
        $admin = $this->createTestUser(UserRole::ROLE_ADMIN, 'admin.self@banglipai.web.id');

        $response = $this->actingAs($admin, 'session')->patchJson("/users/{$admin->id}/status", [
            'status' => 'suspended',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'code'    => 'ACCESS_DENIED',
        ]);
    }

    public function testSuperadminCannotBeSuspended(): void
    {
        $superadmin1 = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super1@banglipai.web.id');
        $superadmin2 = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super2@banglipai.web.id');

        $response = $this->actingAs($superadmin1, 'session')->patchJson("/users/{$superadmin2->id}/status", [
            'status' => 'suspended',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'code'    => 'ACCESS_DENIED',
        ]);
    }

    public function testChangeStatusSuspendsAndReactivatesRegularUserWithIdempotency(): void
    {
        $admin  = $this->createTestUser(UserRole::ROLE_ADMIN, 'admin.status@banglipai.web.id');
        $target = $this->createTestUser(UserRole::ROLE_USER, 'target.status@banglipai.web.id');

        // 1. Suspend target user
        $response1 = $this->actingAs($admin, 'session')->patchJson("/users/{$target->id}/status", [
            'status' => 'suspended',
            'reason' => 'Pelanggaran keamanan terdeteksi',
        ]);

        $response1->assertStatus(200);
        $response1->assertJson([
            'success' => true,
        ]);

        $target->refresh();
        $this->assertTrue($target->isSuspended());
        $this->assertSame(UserStatus::Suspended, $target->status);

        // 2. Idempotent repeat: Suspended again with no side-effects
        $responseRepeat = $this->actingAs($admin, 'session')->patchJson("/users/{$target->id}/status", [
            'status' => 'suspended',
        ]);

        $responseRepeat->assertStatus(200);
        $target->refresh();
        $this->assertTrue($target->isSuspended());

        // 3. Reactivate target user
        $response2 = $this->actingAs($admin, 'session')->patchJson("/users/{$target->id}/status", [
            'status' => 'active',
            'reason' => 'Verifikasi identitas selesai',
        ]);

        $response2->assertStatus(200);

        $target->refresh();
        $this->assertTrue($target->isActive());
        $this->assertSame(UserStatus::Active, $target->status);
    }

    public function testChangeStatusRejectsInvalidStatusValue(): void
    {
        $admin  = $this->createTestUser(UserRole::ROLE_ADMIN, 'admin.val@banglipai.web.id');
        $target = $this->createTestUser(UserRole::ROLE_USER, 'target.val@banglipai.web.id');

        $response = $this->actingAs($admin, 'session')->patchJson("/users/{$target->id}/status", [
            'status' => 'unknown_invalid_status',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'code'    => 'VALIDATION_ERROR',
        ]);
    }

    public function testSuperadminCanResetUserPassword(): void
    {
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super.reset@banglipai.web.id');
        $target     = $this->createTestUser(UserRole::ROLE_USER, 'target.reset@banglipai.web.id');

        $newPassword = 'SecretNewPassword123!';

        $response = $this->actingAs($superadmin, 'session')->postJson("/users/{$target->id}/reset-password", [
            'password' => $newPassword,
            'reason'   => 'Lupa kata sandi terverifikasi via tiket helpdesk',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $target->refresh();
        $this->assertTrue(Hash::check($newPassword, $target->password));

        $this->assertDatabaseHas('password_change_logs', [
            'user_id'            => $target->id,
            'changed_by_user_id' => $superadmin->id,
            'change_type'        => PasswordChangeLog::CHANGE_TYPE_ADMIN_RESET,
        ]);
    }

    public function testAdminCanResetRegularUserPassword(): void
    {
        $admin  = $this->createTestUser(UserRole::ROLE_ADMIN, 'admin.reset@banglipai.web.id');
        $target = $this->createTestUser(UserRole::ROLE_USER, 'target.user.reset@banglipai.web.id');

        $response = $this->actingAs($admin, 'session')->postJson("/users/{$target->id}/reset-password", [
            'password' => 'AnotherStrongPass99#',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $target->refresh();
        $this->assertTrue(Hash::check('AnotherStrongPass99#', $target->password));
    }

    public function testAdminCannotResetElevatedAccountPassword(): void
    {
        $admin       = $this->createTestUser(UserRole::ROLE_ADMIN, 'admin.actor@banglipai.web.id');
        $superadmin  = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super.target@banglipai.web.id');
        $fellowAdmin = $this->createTestUser(UserRole::ROLE_ADMIN, 'admin.fellow@banglipai.web.id');

        // Attempt on Superadmin
        $response1 = $this->actingAs($admin, 'session')->postJson("/users/{$superadmin->id}/reset-password", [
            'password' => 'HackAttemptPassword123!',
        ]);
        $response1->assertStatus(403);

        // Attempt on fellow Admin
        $response2 = $this->actingAs($admin, 'session')->postJson("/users/{$fellowAdmin->id}/reset-password", [
            'password' => 'HackAttemptPassword123!',
        ]);
        $response2->assertStatus(403);
    }

    public function testUserCannotResetOwnPasswordViaAdminEndpoint(): void
    {
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super.self@banglipai.web.id');

        // Superadmin attempts to reset their own password via admin endpoint
        $response = $this->actingAs($superadmin, 'session')->postJson("/users/{$superadmin->id}/reset-password", [
            'password' => 'NewSelfPassword123!',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'code'    => 'ACCESS_DENIED',
        ]);
    }

    public function testUserCanChangeOwnPasswordViaProfileEndpointWithCurrentPassword(): void
    {
        $user = $this->createTestUser(UserRole::ROLE_USER, 'profile.self@banglipai.web.id');

        $response = $this->actingAs($user, 'session')->postJson('/profile/change-password', [
            'current_password' => 'Password123!',
            'new_password'     => 'MyNewFreshPassword99#',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $user->refresh();
        $this->assertTrue(Hash::check('MyNewFreshPassword99#', $user->password));

        $this->assertDatabaseHas('password_change_logs', [
            'user_id'            => $user->id,
            'changed_by_user_id' => $user->id,
            'change_type'        => PasswordChangeLog::CHANGE_TYPE_SELF,
        ]);
    }

    public function testUserCannotChangeOwnPasswordWithWrongCurrentPassword(): void
    {
        $user = $this->createTestUser(UserRole::ROLE_USER, 'profile.wrong@banglipai.web.id');

        $response = $this->actingAs($user, 'session')->postJson('/profile/change-password', [
            'current_password' => 'WrongCurrentPassword999',
            'new_password'     => 'MyNewFreshPassword99#',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'code'    => 'INVALID_CURRENT_PASSWORD',
            'message' => 'Kata sandi lama yang Anda masukkan salah.',
        ]);
    }

    public function testShowUserDetailAuthorization(): void
    {
        $admin   = $this->createTestUser(UserRole::ROLE_ADMIN, 'admin.show@banglipai.web.id');
        $target  = $this->createTestUser(UserRole::ROLE_USER, 'target.show@banglipai.web.id');
        $regular = $this->createTestUser(UserRole::ROLE_USER, 'regular.show@banglipai.web.id');

        // 1. Admin can view target
        $resAdmin = $this->actingAs($admin, 'session')->getJson("/users/{$target->id}");
        $resAdmin->assertStatus(200);
        $resAdmin->assertJson([
            'success' => true,
            'data'    => [
                'id'    => $target->id,
                'email' => $target->email,
            ],
        ]);

        // 2. Regular user can view self
        $resSelf = $this->actingAs($regular, 'session')->getJson("/users/{$regular->id}");
        $resSelf->assertStatus(200);
        $resSelf->assertJson([
            'success' => true,
            'data'    => [
                'id' => $regular->id,
            ],
        ]);

        // 3. Regular user cannot view other
        $resOther = $this->actingAs($regular, 'session')->getJson("/users/{$target->id}");
        $resOther->assertStatus(403);

        // 4. Non-existent ID returns 404
        $res404 = $this->actingAs($admin, 'session')->getJson('/users/999999');
        $res404->assertStatus(404);
    }

    public function testUpdateUserEndpointAuthorizationAndValidation(): void
    {
        $superadmin = $this->createTestUser(UserRole::ROLE_SUPERADMIN, 'super.upd@banglipai.web.id');
        $admin      = $this->createTestUser(UserRole::ROLE_ADMIN, 'admin.upd@banglipai.web.id');
        $target     = $this->createTestUser(UserRole::ROLE_USER, 'target.upd@banglipai.web.id');

        // 1. Superadmin can update target
        $resSuper = $this->actingAs($superadmin, 'session')->putJson("/users/{$target->id}", [
            'name'    => 'Target Name Updated',
            'phone'   => '08123456789',
            'address' => 'Jakarta Pusat',
            'role'    => UserRole::ROLE_ADMIN,
        ]);
        $resSuper->assertStatus(200);
        $resSuper->assertJson([
            'success' => true,
            'message' => 'Data pengguna berhasil diperbarui.',
        ]);

        $target->refresh();
        $this->assertSame('Target Name Updated', $target->name);
        $this->assertSame(UserRole::ROLE_ADMIN, $target->roles->first()?->role);

        // 2. Admin cannot elevate or modify superadmin
        $resDenied = $this->actingAs($admin, 'session')->putJson("/users/{$superadmin->id}", [
            'name' => 'Hacked Name',
        ]);
        $resDenied->assertStatus(403);

        // 3. Validation error (invalid email format)
        $resVal = $this->actingAs($superadmin, 'session')->putJson("/users/{$target->id}", [
            'email' => 'invalid-email-format',
        ]);
        $resVal->assertStatus(422);
    }

    public function testUpdateProfileEndpoint(): void
    {
        $user = $this->createTestUser(UserRole::ROLE_USER, 'profile.updater@banglipai.web.id', 'Old Name');

        $response = $this->actingAs($user, 'session')->putJson('/profile', [
            'name'  => 'New Fresh Name',
            'phone' => '0877112233',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Profil akun berhasil diperbarui.',
            'user'    => [
                'name'  => 'New Fresh Name',
                'phone' => '0877112233',
            ],
        ]);

        $user->refresh();
        $this->assertSame('New Fresh Name', $user->name);
        $this->assertSame('0877112233', $user->phone);
    }
}

