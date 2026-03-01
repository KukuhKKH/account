<?php

namespace App\Services;

use App\Data\CreateUserData;
use App\Data\UpdateUserData;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserSignInLog;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class UserService
{
    public function __construct(
        protected LogtoService $logtoService,
    ) {}

    /**
     * @param array     $filters
     * @param int       $perPage
     * @param User|null $currentUser
     * @return Paginator
     */
    public function listUsers(array $filters = [], int $perPage = 15, ?User $currentUser = null): Paginator
    {
        $query = User::query();

        if ($currentUser && $currentUser->isAdmin() && !$currentUser->isSuperadmin()) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('role', UserRole::ROLE_SUPERADMIN);
            });
        }

        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('role', $filters['role']);
            });
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $orderBy        = $filters['order_by'] ?? 'created_at';
        $orderDirection = $filters['order_direction'] ?? 'desc';

        $query->orderBy($orderBy, $orderDirection);
        return $query->paginate($perPage);
    }

    /**
     * @param int $userId
     * @return User|null
     */
    public function getUser(int $userId): ?User
    {
        return User::with('signInLogs')->find($userId);
    }

    /**
     * @param CreateUserData $data
     * @return User
     * @throws RequestException|ConnectionException
     */
    public function createUser(CreateUserData $data): User
    {
        return $this->logtoService->createUser($data->toArray());
    }

    /**
     * @param User           $user
     * @param UpdateUserData $data
     * @return User
     * @throws RequestException|ConnectionException
     */
    public function updateUser(User $user, UpdateUserData $data): User
    {
        return $this->logtoService->updateUser($user, $data->toArray());
    }

    /**
     * @param User $user
     * @return bool
     * @throws RequestException|ConnectionException
     */
    public function deleteUser(User $user): bool
    {
        return $this->logtoService->deleteUser($user);
    }

    /**
     * @param User       $user
     * @param array|null $deviceInfo
     * @return void
     */
    public function recordSignIn(User $user, ?array $deviceInfo = null): void
    {
        $this->logtoService->recordSignIn($user, $deviceInfo);
    }

    /**
     * @param User $user
     * @param int  $perPage
     * @return Paginator
     */
    public function getUserSignInHistory(User $user, int $perPage = 10): Paginator
    {
        return $user->signInLogs()
            ->latest('signed_in_at')
            ->paginate($perPage);
    }

    /**
     * @param int $limit
     * @return Collection
     */
    public function getRecentSignIns(int $limit = 10): Collection
    {
        return User::with('signInLogs')
            ->latest('last_login_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array
     */
    public function getUserStatistics(): array
    {
        $totalUsers = User::query()->count();

        return [
            'total_users' => $totalUsers,

            'superadmins' => User::query()->whereHas('roles', function ($query) {
                $query->where('role', UserRole::ROLE_SUPERADMIN);
            })->count(),

            'admins' => User::query()->whereHas('roles', function ($query) {
                $query->where('role', UserRole::ROLE_ADMIN);
            })->count(),

            'regular_users' => User::query()->whereHas('roles', function ($query) {
                $query->where('role', UserRole::ROLE_USER);
            })->count(),

            'total_sign_ins'            => UserSignInLog::query()->count(),
            'average_sign_ins_per_user' => (int)(UserSignInLog::query()->count() / max($totalUsers, 1)),
        ];
    }
}
