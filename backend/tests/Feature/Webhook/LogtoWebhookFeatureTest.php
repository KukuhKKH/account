<?php

declare(strict_types=1);

namespace Tests\Feature\Webhook;

use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserSignInLog;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Hypervel\Support\Facades\Config;
use Hypervel\Support\Facades\Hash;
use Tests\TestCase;

class LogtoWebhookFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected string $signingKey = 'test_webhook_secret_key_12345';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.logto.webhook_signing_key', $this->signingKey);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $payloadArray = [
            'event' => 'User.Created',
            'data'  => [
                'id'           => 'logto_unauthorized_1',
                'primaryEmail' => 'unauthorized@banglipai.web.id',
                'name'         => 'Unauthorized User',
            ],
        ];

        $response = $this->postJson(
            '/api/webhooks/logto',
            $payloadArray,
            ['logto-signature-sha256' => 'invalid_sha256_hash']
        );

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Tanda tangan webhook kriptografis tidak valid.',
        ]);
    }

    public function test_webhook_handles_user_created_event(): void
    {
        $payloadArray = [
            'event'     => 'User.Created',
            'hookId'    => 'hook_evt_001',
            'createdAt' => '2026-09-13T14:00:00.000Z',
            'data'      => [
                'id'           => 'logto_sync_user_001',
                'primaryEmail' => 'webhook.new@banglipai.web.id',
                'name'         => 'Webhook New User',
                'primaryPhone' => '081234567890',
                'avatar'       => 'https://avatar.banglipai.web.id/new.png',
                'customData'   => ['source' => 'external_portal'],
            ],
        ];

        $payloadJson = json_encode($payloadArray) ?: '';
        $signature   = hash_hmac('sha256', $payloadJson, $this->signingKey);

        $response = $this->postJson(
            '/api/webhooks/logto',
            $payloadArray,
            ['logto-signature-sha256' => $signature]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        /** @var User|null $createdUser */
        $createdUser = User::where('logto_id', 'logto_sync_user_001')->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals('webhook.new@banglipai.web.id', $createdUser->email);
        $this->assertEquals('Webhook New User', $createdUser->name);
        $this->assertTrue($createdUser->hasRole(UserRole::ROLE_USER));
    }

    public function test_webhook_handles_user_updated_event(): void
    {
        $user = User::create([
            'name'     => 'Original Name',
            'email'    => 'sync.update@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'logto_id' => 'logto_sync_update_99',
            'status'   => UserStatus::Active,
        ]);

        $payloadArray = [
            'event'  => 'User.Data.Updated',
            'hookId' => 'hook_evt_002',
            'data'   => [
                'id'           => 'logto_sync_update_99',
                'primaryEmail' => 'sync.update@banglipai.web.id',
                'name'         => 'Updated Name Via Webhook',
                'primaryPhone' => '0899999999',
            ],
        ];

        $payloadJson = json_encode($payloadArray) ?: '';
        $signature   = hash_hmac('sha256', $payloadJson, $this->signingKey);

        $response = $this->postJson(
            '/api/webhooks/logto',
            $payloadArray,
            ['logto-signature-sha256' => $signature]
        );

        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals('Updated Name Via Webhook', $user->name);
        $this->assertEquals('0899999999', $user->phone);
    }

    public function test_webhook_handles_user_suspension_status_updated(): void
    {
        $user = User::create([
            'name'     => 'Suspend Target',
            'email'    => 'sync.suspend@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'logto_id' => 'logto_sync_suspend_88',
            'status'   => UserStatus::Active,
        ]);

        $payloadArray = [
            'event'  => 'User.SuspensionStatus.Updated',
            'hookId' => 'hook_evt_003',
            'data'   => [
                'id'          => 'logto_sync_suspend_88',
                'isSuspended' => true,
            ],
        ];

        $payloadJson = json_encode($payloadArray) ?: '';
        $signature   = hash_hmac('sha256', $payloadJson, $this->signingKey);

        $response = $this->postJson(
            '/api/webhooks/logto',
            $payloadArray,
            ['logto-signature-sha256' => $signature]
        );

        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals(UserStatus::Suspended, $user->status);
    }

    public function test_webhook_handles_user_deleted_event(): void
    {
        $user = User::create([
            'name'     => 'Delete Target',
            'email'    => 'sync.delete@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'logto_id' => 'logto_sync_delete_77',
            'status'   => UserStatus::Active,
        ]);

        $payloadArray = [
            'event'  => 'User.Deleted',
            'hookId' => 'hook_evt_004',
            'data'   => [
                'id' => 'logto_sync_delete_77',
            ],
        ];

        $payloadJson = json_encode($payloadArray) ?: '';
        $signature   = hash_hmac('sha256', $payloadJson, $this->signingKey);

        $response = $this->postJson(
            '/api/webhooks/logto',
            $payloadArray,
            ['logto-signature-sha256' => $signature]
        );

        $response->assertStatus(200);

        $this->assertNull(User::find($user->id));
        $this->assertNotNull(User::withTrashed()->find($user->id));
    }

    public function test_webhook_handles_user_signed_in_event(): void
    {
        $user = User::create([
            'name'     => 'Sign In Target',
            'email'    => 'sync.signin@banglipai.web.id',
            'password' => Hash::make('Secret123!'),
            'logto_id' => 'logto_sync_signin_66',
            'status'   => UserStatus::Active,
        ]);

        $payloadArray = [
            'event'     => 'User.Signed_in',
            'hookId'    => 'hook_evt_signin_101',
            'ip'        => '10.10.10.99',
            'userAgent' => 'Mozilla/5.0 (X11; Linux x86_64) Firefox/125.0',
            'data'      => [
                'id' => 'logto_sync_signin_66',
            ],
        ];

        $payloadJson = json_encode($payloadArray) ?: '';
        $signature   = hash_hmac('sha256', $payloadJson, $this->signingKey);

        $response = $this->postJson(
            '/api/webhooks/logto',
            $payloadArray,
            ['logto-signature-sha256' => $signature]
        );

        $response->assertStatus(200);

        $signInLog = UserSignInLog::where('user_id', $user->id)->first();
        $this->assertNotNull($signInLog);
        $this->assertEquals('10.10.10.99', $signInLog->ip_address);
        $this->assertEquals('hook_evt_signin_101', $signInLog->logto_event_id);
    }
}
