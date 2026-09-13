<?php

declare(strict_types=1);

namespace App\Services\Webhook;

use App\Data\Webhook\LogtoWebhookEventData;
use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserSignInLog;
use App\Services\Auth\LogtoAuthService;
use Carbon\Carbon;
use Exception;
use Hypervel\Support\Facades\Config;
use Hypervel\Support\Facades\DB;
use Hypervel\Support\Facades\Hash;
use Hypervel\Support\Facades\Log;
use Hypervel\Support\Str;

class LogtoWebhookHandler
{
    public function __construct(
        protected LogtoAuthService $authService,
    ) {
    }

    /**
     * Verify incoming webhook cryptographic HMAC-SHA256 signature.
     *
     * @param array<string, mixed>|null $parsedBody
     */
    public function verifySignature(string $rawPayload, ?string $signatureHeader, ?array $parsedBody = null): bool
    {
        /** @var string $signingKey */
        $signingKey = (string) Config::get('services.logto.webhook_signing_key', env('LOGTO_WEBHOOK_SIGNING_KEY', ''));

        // If no signing key configured (e.g. initial dev test), accept request
        if (empty($signingKey)) {
            Log::debug('Logto webhook signing key not configured, allowing request');

            return true;
        }

        if (empty($signatureHeader)) {
            Log::warning('Security Alert: Missing signature header on Logto webhook request');

            return false;
        }

        $cleanSignature = trim(str_replace(['sha256=', 'sha256:'], '', $signatureHeader));

        // 1. Raw payload direct verification
        $expectedDigest = hash_hmac('sha256', $rawPayload, $signingKey);
        if (hash_equals($expectedDigest, $cleanSignature)) {
            return true;
        }

        // 2. Parsed body fallback verification (e.g. testing clients / HTTP reverse proxy reformats)
        if ($parsedBody !== null && ! empty($parsedBody)) {
            $encoded = json_encode($parsedBody);
            if ($encoded !== false && hash_equals(hash_hmac('sha256', $encoded, $signingKey), $cleanSignature)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Process Logto Webhook Event synchronously/coroutine-safe.
     *
     * @return array{handled: bool, event: string, message: string}
     */
    public function handleEvent(LogtoWebhookEventData $event): array
    {
        $eventType = $event->event;

        Log::info('Processing incoming Logto Webhook event', [
            'event'   => $eventType,
            'hook_id' => $event->hookId,
            'user_id' => $event->getLogtoUserId(),
        ]);

        return match ($eventType) {
            'User.Created', 'User.Created.Post'           => $this->handleUserCreated($event),
            'User.Data.Updated', 'User.Updated'           => $this->handleUserUpdated($event),
            'User.Deleted'                                => $this->handleUserDeleted($event),
            'User.SuspensionStatus.Updated'               => $this->handleSuspensionStatusUpdated($event),
            'User.Signed_in', 'PostSignIn', 'User.SignIn' => $this->handleUserSignedIn($event),
            default                                       => [
                'handled' => false,
                'event'   => $eventType,
                'message' => 'Event type ignored or not applicable for local sync',
            ],
        };
    }

    /**
     * Handle User.Created event from external SSO registration.
     *
     * @return array{handled: bool, event: string, message: string}
     */
    protected function handleUserCreated(LogtoWebhookEventData $event): array
    {
        $logtoId = $event->getLogtoUserId();
        $email   = $event->getEmail();

        if (empty($email)) {
            return [
                'handled' => false,
                'event'   => $event->event,
                'message' => 'Skipped: email is missing in payload',
            ];
        }

        // Check if user already exists by logto_id or email
        /** @var User|null $existing */
        $existing = User::where('logto_id', $logtoId)
            ->orWhere('email', $email)
            ->first();

        if ($existing) {
            if (empty($existing->logto_id) && ! empty($logtoId)) {
                $existing->logto_id = $logtoId;
                $existing->save();
            }

            return [
                'handled' => true,
                'event'   => $event->event,
                'message' => 'User already exists, linked logto_id',
            ];
        }

        DB::transaction(function () use ($event, $logtoId, $email): void {
            $user = User::create([
                'name'        => $event->getName() ?: 'User ' . substr($email, 0, strpos($email, '@') ?: 5),
                'email'       => $email,
                'password'    => Hash::make(Str::random(32)),
                'logto_id'    => $logtoId,
                'phone'       => $event->getPhone(),
                'avatar'      => $event->getAvatar(),
                'status'      => UserStatus::Active,
                'custom_data' => $event->getCustomData(),
            ]);

            $user->roles()->create([
                'role' => UserRole::ROLE_USER,
            ]);
        });

        Log::info('AUDIT: Webhook synced newly created user', [
            'email'    => $email,
            'logto_id' => $logtoId,
        ]);

        return [
            'handled' => true,
            'event'   => $event->event,
            'message' => 'User created and role assigned successfully',
        ];
    }

    /**
     * Handle User.Data.Updated event.
     *
     * @return array{handled: bool, event: string, message: string}
     */
    protected function handleUserUpdated(LogtoWebhookEventData $event): array
    {
        $logtoId = $event->getLogtoUserId();
        $email   = $event->getEmail();

        /** @var User|null $user */
        $user = null;

        if (! empty($logtoId)) {
            $user = User::where('logto_id', $logtoId)->first();
        }

        if (! $user && ! empty($email)) {
            $user = User::where('email', $email)->first();
        }

        if (! $user) {
            return [
                'handled' => false,
                'event'   => $event->event,
                'message' => 'User not found in local database',
            ];
        }

        DB::transaction(function () use ($user, $event): void {
            if ($event->getName() !== null) {
                $user->name = $event->getName();
            }

            if ($event->getEmail() !== null) {
                $user->email = $event->getEmail();
            }

            if ($event->getPhone() !== null) {
                $user->phone = $event->getPhone();
            }

            if ($event->getAvatar() !== null) {
                $user->avatar = $event->getAvatar();
            }

            if (! empty($event->getCustomData())) {
                $user->custom_data = array_merge($user->custom_data ?? [], $event->getCustomData());
            }

            $user->save();
        });

        Log::info('AUDIT: Webhook updated user data', [
            'user_id'  => $user->id,
            'logto_id' => $logtoId,
        ]);

        return [
            'handled' => true,
            'event'   => $event->event,
            'message' => 'User profile updated via webhook sync',
        ];
    }

    /**
     * Handle User.Deleted event.
     *
     * @return array{handled: bool, event: string, message: string}
     */
    protected function handleUserDeleted(LogtoWebhookEventData $event): array
    {
        $logtoId = $event->getLogtoUserId();

        if (empty($logtoId)) {
            return [
                'handled' => false,
                'event'   => $event->event,
                'message' => 'Missing logto_id for delete event',
            ];
        }

        /** @var User|null $user */
        $user = User::where('logto_id', $logtoId)->first();

        if (! $user) {
            return [
                'handled' => false,
                'event'   => $event->event,
                'message' => 'User not found for deletion',
            ];
        }

        DB::transaction(function () use ($user): void {
            $user->roles()->delete();
            $user->delete(); // Soft delete
        });

        Log::alert('AUDIT: User soft-deleted via Logto webhook event', [
            'user_id'  => $user->id,
            'email'    => $user->email,
            'logto_id' => $logtoId,
        ]);

        return [
            'handled' => true,
            'event'   => $event->event,
            'message' => 'User soft deleted locally',
        ];
    }

    /**
     * Handle User.SuspensionStatus.Updated event.
     *
     * @return array{handled: bool, event: string, message: string}
     */
    protected function handleSuspensionStatusUpdated(LogtoWebhookEventData $event): array
    {
        $logtoId     = $event->getLogtoUserId();
        $isSuspended = $event->isSuspended();

        if (empty($logtoId) || $isSuspended === null) {
            return [
                'handled' => false,
                'event'   => $event->event,
                'message' => 'Missing logto_id or suspension status',
            ];
        }

        /** @var User|null $user */
        $user = User::where('logto_id', $logtoId)->first();

        if (! $user) {
            return [
                'handled' => false,
                'event'   => $event->event,
                'message' => 'User not found',
            ];
        }

        $user->status = $isSuspended ? UserStatus::Suspended : UserStatus::Active;
        $user->save();

        Log::info('AUDIT: Webhook updated suspension status', [
            'user_id'      => $user->id,
            'is_suspended' => $isSuspended,
        ]);

        return [
            'handled' => true,
            'event'   => $event->event,
            'message' => 'User status updated',
        ];
    }

    /**
     * Handle User.Signed_in event to record login trail.
     *
     * @return array{handled: bool, event: string, message: string}
     */
    protected function handleUserSignedIn(LogtoWebhookEventData $event): array
    {
        $logtoId = $event->getLogtoUserId();

        if (empty($logtoId)) {
            return [
                'handled' => false,
                'event'   => $event->event,
                'message' => 'Missing logto_id for sign-in event',
            ];
        }

        /** @var User|null $user */
        $user = User::where('logto_id', $logtoId)->first();

        if (! $user) {
            return [
                'handled' => false,
                'event'   => $event->event,
                'message' => 'User not found for sign-in recording',
            ];
        }

        try {
            UserSignInLog::create([
                'user_id'        => $user->id,
                'ip_address'     => $event->ip,
                'user_agent'     => $event->userAgent,
                'device_info'    => $this->authService->parseDeviceInfo($event->userAgent),
                'signed_in_at'   => Carbon::now(),
                'logto_event_id' => $event->hookId,
            ]);

            $user->last_login_at = Carbon::now();
            $user->save();
        } catch (Exception $e) {
            Log::error('Failed to log webhook sign-in: ' . $e->getMessage());
        }

        return [
            'handled' => true,
            'event'   => $event->event,
            'message' => 'Sign-in log recorded successfully',
        ];
    }
}
