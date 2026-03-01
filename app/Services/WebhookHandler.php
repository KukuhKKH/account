<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserRole;
use App\Models\UserSignInLog;
use Exception;
use Illuminate\Support\Facades\Log;

class WebhookHandler
{
    public function __construct(
        protected LogtoService $logtoService,
    ) {}

    public function handleUserCreated(array $data): void
    {
        try {
            $logtoId = $data['userId'] ?? $data['id'] ?? null;
            if (!$logtoId) {
                Log::warning('User.Created webhook: missing userId', ['data' => $data]);
                return;
            }

            $user = User::query()
                ->where('logto_id', '=', $logtoId)
                ->first();

            if ($user) {
                Log::info('User already exists in local DB', ['logto_id' => $logtoId]);
                return;
            }

            $user              = new User();
            $user->logto_id    = $logtoId;
            $user->name        = $data['name'] ?? 'User';
            $user->email       = $data['primaryEmail'] ?? $data['email'] ?? null;
            $user->phone       = $data['phoneNumber'] ?? $data['phone'] ?? null;
            $user->avatar      = $data['avatar'] ?? null;
            $user->address     = $data['address'] ?? null;
            $user->custom_data = $data['customData'] ?? [];
            $user->save();

            $roles = $data['roles'] ?? $data['customData']['roles'] ?? [];
            if (!empty($roles)) {
                $rolesArray = is_array($roles) ? $roles : [$roles];
                foreach ($rolesArray as $role) {
                    $userRole          = new UserRole();
                    $userRole->user_id = $user->id;
                    $userRole->role    = $role;
                    $userRole->save();
                }
            } else {
                $defaultRole          = new UserRole();
                $defaultRole->user_id = $user->id;
                $defaultRole->role    = UserRole::ROLE_USER;
                $defaultRole->save();
            }

            Log::info('User created from Logto webhook', [
                'logto_id' => $logtoId,
                'user_id'  => $user->id,
                'email'    => $user->email,
            ]);
        } catch (Exception $e) {
            Log::error('Error handling User.Created webhook', [
                'message' => $e->getMessage(),
                'data'    => $data,
            ]);
        }
    }

    public function handleUserUpdated(array $data): void
    {
        try {
            $logtoId = $data['userId'] ?? $data['id'] ?? null;

            if (!$logtoId) {
                Log::warning('User.Updated webhook: missing userId', ['data' => $data]);
                return;
            }

            $user = User::where('logto_id', $logtoId)->first();

            if (!$user) {
                Log::warning('User.Updated webhook: user not found', ['logto_id' => $logtoId]);
                $this->handleUserCreated($data);
                return;
            }

            $fields = [
                'name'         => $data['name'] ?? null,
                'email'        => $data['primaryEmail'] ?? $data['email'] ?? null,
                'phone'        => $data['phoneNumber'] ?? $data['phone'] ?? null,
                'avatar'       => $data['avatar'] ?? null,
                'address'      => $data['address'] ?? null,
                'custom_data'  => $data['customData'] ?? null,
            ];

            $updateData = array_filter($fields, fn($value) => !is_null($value));
            if (!empty($updateData)) {
                $user->fill($updateData);
            }

            if (isset($data['roles']) || isset($data['customData']['roles'])) {
                if (isset($data['roles']) || isset($data['customData']['roles'])) {
                    $roles = (array)($data['roles'] ?? $data['customData']['roles'] ?? []);
                    $user->roles()->delete();

                    $roleInstances = collect($roles)->map(function ($roleName) {
                        return new UserRole(['role' => $roleName]);
                    });

                    $user->roles()->saveMany($roleInstances);
                }
            }

            $user->save();

            Log::info('User updated from Logto webhook', [
                'logto_id' => $logtoId,
                'user_id'  => $user->id,
            ]);
        } catch (Exception $e) {
            Log::error('Error handling User.Updated webhook', [
                'message' => $e->getMessage(),
                'data'    => $data,
            ]);
        }
    }

    public function handleUserDeleted(array $data): void
    {
        try {
            $logtoId = $data['userId'] ?? $data['id'] ?? null;

            if (!$logtoId) {
                Log::warning('User.Deleted webhook: missing userId', ['data' => $data]);
                return;
            }

            $user = User::query()
                ->where('logto_id', $logtoId)
                ->first();

            if ($user) {
                $user->roles()->delete();

                $user->delete();

                Log::info('User deleted from Logto webhook', [
                    'logto_id' => $logtoId,
                    'user_id'  => $user->id,
                ]);
            } else {
                Log::warning('User.Deleted webhook: user not found', ['logto_id' => $logtoId]);
            }
        } catch (Exception $e) {
            Log::error('Error handling User.Deleted webhook', [
                'message' => $e->getMessage(),
                'data'    => $data,
            ]);
        }
    }

    public function handleUserSignedIn(array $data): void
    {
        try {
            $logtoId = $data['userId'] ?? $data['id'] ?? null;
            $eventId = $data['eventId'] ?? $data['logto_event_id'] ?? null;

            if (!$logtoId) {
                Log::warning('User.SignedIn webhook: missing userId', ['data' => $data]);
                return;
            }

            $user = User::query()
                ->where('logto_id', $logtoId)
                ->first();

            if (!$user) {
                Log::warning('User.SignedIn webhook: user not found', ['logto_id' => $logtoId]);
                return;
            }

            $userAgent = $data['userAgent'] ?? $data['user_agent'] ?? null;
            $ipAddress = $data['ipAddress'] ?? $data['ip_address'] ?? null;

            $deviceInfo = [];
            if ($userAgent) {
                $deviceInfo = [
                    'user_agent' => $userAgent,
                    'browser'    => $this->logtoService->extractBrowser($userAgent),
                    'os'         => $this->logtoService->extractOS($userAgent),
                ];
            }

            $signInLog = new UserSignInLog();

            $signInLog->user_id        = $user->id;
            $signInLog->ip_address     = $ipAddress;
            $signInLog->user_agent     = $userAgent;
            $signInLog->device_info    = $deviceInfo;
            $signInLog->signed_in_at   = now();
            $signInLog->logto_event_id = $eventId;
            $signInLog->save();

            $user->update(['last_login_at' => now()]);

            Log::info('Sign-in logged from Logto webhook', [
                'logto_id' => $logtoId,
                'user_id'  => $user->id,
                'ip'       => $ipAddress,
            ]);
        } catch (Exception $e) {
            Log::error('Error handling User.SignedIn webhook', [
                'message' => $e->getMessage(),
                'data'    => $data,
            ]);
        }
    }

}
