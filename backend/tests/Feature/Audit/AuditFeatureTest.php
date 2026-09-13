<?php

declare(strict_types=1);

namespace Tests\Feature\Audit;

use App\Enums\PasswordChangeType;
use App\Enums\UserStatus;
use App\Models\PasswordChangeLog;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserSignInLog;
use Carbon\Carbon;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Hypervel\Support\Facades\Auth;
use Hypervel\Support\Facades\Hash;
use Tests\TestCase;

class AuditFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Auth::guard('session')->logout();

        $this->superadmin = User::create([
            'name'     => 'Superadmin Feature',
            'email'    => 'super.feat@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'status'   => UserStatus::Active,
        ]);
        $this->superadmin->roles()->create(['role' => UserRole::ROLE_SUPERADMIN]);

        $this->admin = User::create([
            'name'     => 'Admin Feature',
            'email'    => 'admin.feat@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'status'   => UserStatus::Active,
        ]);
        $this->admin->roles()->create(['role' => UserRole::ROLE_ADMIN]);

        $this->user = User::create([
            'name'     => 'User Feature',
            'email'    => 'user.feat@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'status'   => UserStatus::Active,
        ]);
        $this->user->roles()->create(['role' => UserRole::ROLE_USER]);
    }

    public function test_unauthenticated_request_to_audit_logs_returns_401(): void
    {
        $response = $this->getJson('/audit-logs/passwords');
        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'code'    => 'UNAUTHENTICATED',
        ]);
    }

    public function test_regular_user_cannot_access_audit_logs(): void
    {
        $response = $this->actingAs($this->user, 'session')->getJson('/audit-logs/passwords');
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'code'    => 'ACCESS_DENIED',
        ]);
    }

    public function test_superadmin_can_view_password_audit_logs(): void
    {
        PasswordChangeLog::create([
            'user_id'            => $this->user->id,
            'changed_by_user_id' => $this->user->id,
            'change_type'        => PasswordChangeType::SelfChange,
            'ip_address'         => '10.10.10.5',
            'user_agent'         => 'Mozilla/5.0 Test',
            'reason'             => 'User changed own password',
            'via_logto_api'      => true,
        ]);

        $response = $this->actingAs($this->superadmin, 'session')->getJson('/audit-logs/passwords');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'userId',
                    'userName',
                    'userEmail',
                    'userRole',
                    'changeType',
                    'changeTypeLabel',
                    'isSelf',
                    'isAdmin',
                    'ipAddress',
                    'description',
                    'timeAgo',
                ],
            ],
            'meta' => [
                'currentPage',
                'perPage',
                'total',
                'lastPage',
            ],
        ]);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_admin_cannot_see_superadmin_password_logs_due_to_isolation(): void
    {
        // 1. Log for regular user
        PasswordChangeLog::create([
            'user_id'            => $this->user->id,
            'changed_by_user_id' => $this->user->id,
            'change_type'        => PasswordChangeType::SelfChange,
            'ip_address'         => '10.10.10.5',
            'reason'             => 'User change',
        ]);

        // 2. Log for superadmin
        PasswordChangeLog::create([
            'user_id'            => $this->superadmin->id,
            'changed_by_user_id' => $this->superadmin->id,
            'change_type'        => PasswordChangeType::SelfChange,
            'ip_address'         => '10.10.10.5',
            'reason'             => 'Superadmin secret rotation',
        ]);

        // Admin request: should only see log for regular user (1 log)
        $adminResponse = $this->actingAs($this->admin, 'session')->getJson('/audit-logs/passwords');
        $adminResponse->assertStatus(200);
        $this->assertEquals(1, count($adminResponse->json('data')));
        $this->assertEquals($this->user->email, $adminResponse->json('data.0.userEmail'));

        // Superadmin request: should see both logs (2 logs)
        $superResponse = $this->actingAs($this->superadmin, 'session')->getJson('/audit-logs/passwords');
        $superResponse->assertStatus(200);
        $this->assertEquals(2, count($superResponse->json('data')));
    }

    public function test_password_audit_logs_can_be_filtered_by_change_type_and_search(): void
    {
        PasswordChangeLog::create([
            'user_id'            => $this->user->id,
            'changed_by_user_id' => $this->user->id,
            'change_type'        => PasswordChangeType::SelfChange,
            'ip_address'         => '10.10.10.55',
            'reason'             => 'Self update',
        ]);

        PasswordChangeLog::create([
            'user_id'            => $this->user->id,
            'changed_by_user_id' => $this->superadmin->id,
            'change_type'        => PasswordChangeType::AdminReset,
            'ip_address'         => '10.10.10.10',
            'reason'             => 'Admin forced reset',
        ]);

        // Filter by change_type = admin_reset
        $resType = $this->actingAs($this->superadmin, 'session')->getJson('/audit-logs/passwords?change_type=admin_reset');
        $resType->assertStatus(200);
        $this->assertEquals(1, count($resType->json('data')));
        $this->assertEquals('admin_reset', $resType->json('data.0.changeType'));

        // Search by IP
        $resSearch = $this->actingAs($this->superadmin, 'session')->getJson('/audit-logs/passwords?search=10.10.10.55');
        $resSearch->assertStatus(200);
        $this->assertEquals(1, count($resSearch->json('data')));
    }

    public function test_superadmin_and_admin_can_view_sign_in_logs(): void
    {
        UserSignInLog::create([
            'user_id'      => $this->user->id,
            'ip_address'   => '10.10.10.5',
            'user_agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0',
            'device_info'  => ['browser' => 'Chrome', 'os' => 'Windows'],
            'signed_in_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->superadmin, 'session')->getJson('/audit-logs/sign-ins');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'userId',
                    'userName',
                    'userEmail',
                    'userRole',
                    'ipAddress',
                    'browser',
                    'os',
                    'signedInAt',
                    'timeAgo',
                ],
            ],
            'meta',
        ]);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_audit_stats_endpoint_returns_metrics(): void
    {
        $response = $this->actingAs($this->superadmin, 'session')->getJson('/audit-logs/stats');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'defenseScore',
                'signInsLast24h',
                'passwordRotations',
                'adminResetsTotal',
                'selfChangesTotal',
                'activeIpsLast24h',
                'totalAccounts',
                'encryptionStandard',
                'auditDigestStatus',
            ],
        ]);
        $this->assertEquals(99.8, $response->json('data.defenseScore'));
    }
}
