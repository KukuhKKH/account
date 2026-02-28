<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSignInLog;
use App\Remote\LogtoRemote;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class LogtoService
{
    public function __construct(
        protected LogtoRemote $remote,
    ) {}

    /**
     * @param array $userData
     * @return User
     * @throws RequestException|ConnectionException
     */
    public function createUser(array $userData): User
    {
        try {
            $logtoData = [
                'primaryEmail' => $userData['email'],
                'name'         => $userData['name'] ?? null,
                'phone'        => $userData['phone'] ?? null,
                'customData'   => [
                    'role'    => $userData['role'] ?? 'user',
                    'address' => $userData['address'] ?? null,
                ],
            ];

            if (isset($userData['password'])) {
                $logtoData['password'] = $userData['password'];
            }

            $logtoUser = $this->remote->createUser($logtoData);
            $logtoId   = $logtoUser['id'];

            $localUser = new User();

            $localUser->logto_id = $logtoId;
            $localUser->name     = $userData['name'];
            $localUser->email    = $userData['email'];
            $localUser->phone    = $userData['phone'] ?? null;
            $localUser->address  = $userData['address'] ?? null;
            $localUser->role     = $userData['role'] ?? 'user';

            $localUser->custom_data = [
                'role' => $userData['role'] ?? 'user',
            ];

            $localUser->avatar = $userData['avatar'] ?? null;
            $localUser->save();

            Log::info('User created successfully', [
                'local_user_id' => $localUser->id,
                'logto_id'      => $logtoId,
            ]);

            return $localUser;
        } catch (RequestException $e) {
            Log::error('Failed to create user', [
                'message' => $e->getMessage(),
                'email'   => $userData['email'] ?? null,
            ]);
            throw $e;
        }
    }

    /**
     * @param User  $user
     * @param array $userData
     * @return User
     * @throws RequestException|ConnectionException
     */
    public function updateUser(User $user, array $userData): User
    {
        try {
            $logtoData = collect($userData)
                ->only(['name', 'phone'])
                ->filter()
                ->toArray();

            if (!empty($userData['email']) && $userData['email'] !== $user->email) {
                $logtoData['primaryEmail'] = $userData['email'];
            }

            $customData = array_merge(
                $user->custom_data ?? [],
                collect($userData)
                    ->only(['role', 'address'])
                    ->filter()
                    ->toArray()
            );

            if (!empty($customData)) {
                $logtoData['customData'] = $customData;
            }

            if (!empty($logtoData)) {
                $this->remote->updateUser($user->logto_id, $logtoData);
            }

            $user->fill([
                'name'        => $userData['name'] ?? $user->name,
                'email'       => $userData['email'] ?? $user->email,
                'phone'       => $userData['phone'] ?? $user->phone,
                'address'     => $userData['address'] ?? $user->address,
                'role'        => $userData['role'] ?? $user->role,
                'avatar'      => $userData['avatar'] ?? $user->avatar,
                'custom_data' => $customData,
            ])->save();

            Log::info('User updated successfully', [
                'user_id'  => $user->id,
                'logto_id' => $user->logto_id,
            ]);

            return $user->fresh();
        } catch (RequestException $e) {
            Log::error('Failed to update user', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            throw $e;
        }
    }

    /**
     * @param User $user
     * @return bool
     * @throws RequestException|ConnectionException
     */
    public function deleteUser(User $user): bool
    {
        try {
            $this->remote->deleteUser($user->logto_id);

            $user->delete();

            Log::info('User deleted successfully', [
                'user_id'  => $user->id,
                'logto_id' => $user->logto_id,
            ]);

            return true;
        } catch (RequestException $e) {
            Log::error('Failed to delete user', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            throw $e;
        }
    }

    /**
     * @param User   $user
     * @param string $role
     * @return User
     * @throws RequestException|ConnectionException
     */
    public function syncRoleToLogto(User $user, string $role): User
    {
        try {
            $customData         = $user->custom_data ?? [];
            $customData['role'] = $role;

            $this->remote->updateCustomData($user->logto_id, $customData);

            $user->role        = $role;
            $user->custom_data = $customData;
            $user->save();

            Log::info('User role synced to Logto', [
                'user_id' => $user->id,
                'role'    => $role,
            ]);

            return $user->fresh();
        } catch (RequestException $e) {
            Log::error('Failed to sync role to Logto', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            throw $e;
        }
    }

    /**
     * @param string $logtoId
     * @return User|null
     * @throws RequestException|ConnectionException
     */
    public function syncUserFromLogto(string $logtoId): ?User
    {
        try {
            $logtoUser = $this->remote->getUser($logtoId);

            $user = User::where('logto_id', $logtoId)->first();
            if (!$user) {
                $user           = new User();
                $user->logto_id = $logtoId;
            }

            $user->name   = $logtoUser['name'] ?? $user->name ?? null;
            $user->email  = $logtoUser['primaryEmail'] ?? $logtoUser['email'] ?? $user->email;
            $user->phone  = $logtoUser['phone'] ?? $user->phone;
            $user->avatar = $logtoUser['avatar'] ?? $user->avatar;

            $user->custom_data = $logtoUser['customData'] ?? $user->custom_data;

            if (isset($logtoUser['customData']['role'])) {
                $user->role = $logtoUser['customData']['role'];
            }

            $user->save();

            Log::info('User synced from Logto', [
                'logto_id' => $logtoId,
                'user_id'  => $user->id,
            ]);

            return $user;
        } catch (RequestException $e) {
            Log::error('Failed to sync user from Logto', [
                'message'  => $e->getMessage(),
                'logto_id' => $logtoId,
            ]);

            throw $e;
        }
    }

    /**
     * @param User       $user
     * @param array|null $deviceInfo
     * @return void
     */
    public function recordSignIn(User $user, ?array $deviceInfo = null): void
    {
        try {
            $user->last_login_at = now();
            $user->save();

            $signInLog               = new UserSignInLog();
            $signInLog->user_id      = $user->id;
            $signInLog->ip_address   = request()->ip();
            $signInLog->user_agent   = request()->userAgent();
            $signInLog->device_info  = $deviceInfo ?? $this->parseDeviceInfo(request()->userAgent());
            $signInLog->signed_in_at = now();
            $signInLog->save();

            Log::info('Sign-in recorded', [
                'user_id'    => $user->id,
                'ip_address' => request()->ip(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to record sign-in', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * @param string|null $userAgent
     * @return array
     */
    protected function parseDeviceInfo(?string $userAgent): array
    {
        if (!$userAgent) {
            return [];
        }

        return [
            'user_agent' => $userAgent,
            'browser'    => $this->extractBrowser($userAgent),
            'os'         => $this->extractOS($userAgent),
        ];
    }

    /**
     * @param string $userAgent
     * @return string
     */
    protected function extractBrowser(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        return match (true) {
            str_contains($ua, 'edg/')     => 'Edge',
            str_contains($ua, 'chrome/')  => 'Chrome',
            str_contains($ua, 'firefox/') => 'Firefox',
            str_contains($ua, 'safari/')  => 'Safari',
            default                       => 'Unknown',
        };
    }

    /**
     * @param string $userAgent
     * @return string
     */
    protected function extractOS(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        return match (true) {
            str_contains($ua, 'windows') => 'Windows',
            str_contains($ua, 'android') => 'Android',
            str_contains($ua, 'iphone'),
            str_contains($ua, 'ipad')    => 'iOS',
            str_contains($ua, 'mac os')  => 'macOS',
            str_contains($ua, 'linux')   => 'Linux',
            default                      => 'Unknown',
        };
    }
}
