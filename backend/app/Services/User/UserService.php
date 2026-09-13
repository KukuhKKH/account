<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Data\User\CreateUserData;
use App\Data\User\UpdateUserData;
use App\Data\User\UserFilterData;
use App\Enums\UserStatus;
use App\Exceptions\User\CannotDeactivateProtectedAccountException;
use App\Exceptions\User\CannotDeleteLastAdministratorException;
use App\Exceptions\User\CannotDeleteOwnAccountException;
use App\Exceptions\User\CannotResetOwnPasswordException;
use App\Exceptions\User\InvalidCurrentPasswordException;
use App\Exceptions\User\InvalidUserStatusTransitionException;
use App\Exceptions\User\UserAccessDeniedException;
use App\Exceptions\User\UserAlreadyExistsException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\PasswordChangeLog;
use App\Models\User;
use App\Models\UserRole;
use App\Policies\UserPolicy;
use App\Services\Audit\AuditContext;
use App\Services\Auth\LogtoM2MService;
use Exception;
use Hypervel\Database\Eloquent\Builder;
use Hypervel\Database\Eloquent\Collection;
use Hypervel\Support\Facades\DB;
use Hypervel\Support\Facades\Hash;
use Hypervel\Support\Facades\Log;
use Hypervel\Support\Str;

use App\Services\Auth\PasswordChangeService;

class UserService
{
    protected PasswordChangeService $passwordService;

    public function __construct(
        protected LogtoM2MService        $logtoM2M,
        protected UserPolicy             $policy,
        ?PasswordChangeService           $passwordService = null,
    ) {
        $this->passwordService = $passwordService ?? new PasswordChangeService($this->logtoM2M, $this->policy);
    }

    /**
     * Get paginated user directory with search, filtering, and eager-loaded roles.
     *
     * @return array{items: Collection<int, User>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function listUsers(UserFilterData $filter): array
    {
        /** @var Builder<User> $query */
        $query = User::query()->with('roles');

        // 1. Text Search Filter (name, email, phone, logto_id)
        if (! empty($filter->search)) {
            $search = '%' . strtolower($filter->search) . '%';

            $query->where(function (Builder $q) use ($search): void {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(phone) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(logto_id) LIKE ?', [$search]);
            });
        }

        // 2. Role Filter
        if (! empty($filter->role)) {
            $query->whereHas('roles', function (Builder $q) use ($filter): void {
                $q->where('role', $filter->role);
            });
        }

        // 3. Status Filter using typed column
        if (! empty($filter->status) && $filter->status !== 'all') {
            $query->where('status', $filter->status);
        }

        // Order by latest created
        $query->orderBy('id', 'desc');

        $total    = $query->count();
        $items    = $query->forPage($filter->page, $filter->perPage)->get();
        $lastPage = max(1, (int) ceil($total / $filter->perPage));

        return [
            'items'    => $items,
            'total'    => $total,
            'page'     => $filter->page,
            'perPage'  => $filter->perPage,
            'lastPage' => $lastPage,
        ];
    }

    /**
     * Find a single user by local database ID.
     *
     * @throws UserNotFoundException
     */
    public function getUserById(int $id): User
    {
        /** @var User|null $user */
        $user = User::with('roles')->find($id);

        if (! $user) {
            throw new UserNotFoundException('Pengguna dengan ID ' . $id . ' tidak ditemukan dalam sistem.');
        }

        return $user;
    }

    /**
     * Create a new user with strict authorization verification & Logto dual-sync.
     *
     * @throws UserAccessDeniedException
     * @throws UserAlreadyExistsException
     */
    public function createUser(
        CreateUserData $data,
        User           $actor,
        AuditContext   $context,
    ): User {
        // 1. Security Policy Gate: Privilege Escalation barrier
        if (! $this->policy->create($actor, $data->role)) {
            Log::warning('Security Barrier: Unauthorized user creation attempt', [
                'actor_id'    => $actor->id,
                'actor_email' => $actor->email,
                'target_role' => $data->role,
                'ip'          => $context->ipAddress,
            ]);

            throw new UserAccessDeniedException('Anda tidak memiliki izin untuk membuat akun dengan role ' . $data->role . '.');
        }

        // 2. Email uniqueness check
        if (User::where('email', $data->email)->exists()) {
            throw new UserAlreadyExistsException('Alamat email ' . $data->email . ' sudah terdaftar dalam sistem.');
        }

        // 3. Password preparation
        $plainPassword = $data->password;

        if (empty($plainPassword)) {
            $plainPassword = Str::random(16) . '!9A';
        }

        $logtoId = null;

        // 4. Create user in Logto Management API (if configured)
        try {
            $logtoPayload = [
                'primaryEmail' => $data->email,
                'name'         => $data->name,
                'password'     => $plainPassword,
            ];

            if (! empty($data->phone)) {
                $logtoPayload['primaryPhone'] = $data->phone;
            }

            $logtoUser = $this->logtoM2M->createUser($logtoPayload);

            if (! empty($logtoUser['id'])) {
                $logtoId = (string) $logtoUser['id'];
            }
        } catch (Exception $e) {
            Log::warning('Logto M2M user creation skipped/failed: ' . $e->getMessage(), [
                'email' => $data->email,
            ]);
        }

        // 5. Database transaction for local integrity
        /** @var User $newUser */
        $newUser = DB::transaction(function () use ($data, $plainPassword, $logtoId): User {
            $customData = array_merge($data->customData, [
                'created_by' => 'bff_management',
            ]);

            /** @var User $user */
            $user = User::create([
                'name'        => $data->name,
                'email'       => $data->email,
                'password'    => Hash::make($plainPassword),
                'logto_id'    => $logtoId,
                'status'      => UserStatus::Active,
                'phone'       => $data->phone,
                'address'     => $data->address,
                'avatar'      => $data->avatar,
                'custom_data' => $customData,
            ]);

            $user->roles()->create([
                'role' => $data->role,
            ]);

            return $user;
        });

        $newUser->load('roles');

        // 6. Security Audit Trail Logging
        Log::info('AUDIT: User created successfully', [
            'action'          => 'user.create',
            'actor_id'        => $actor->id,
            'actor_email'     => $actor->email,
            'created_user_id' => $newUser->id,
            'created_email'   => $newUser->email,
            'assigned_role'   => $data->role,
            'logto_id'        => $logtoId,
            'ip'              => $context->ipAddress,
            'user_agent'      => $context->userAgent,
        ]);

        return $newUser;
    }

    /**
     * Update an existing user with strict authorization checks & Logto sync.
     *
     * @throws UserAccessDeniedException
     * @throws UserAlreadyExistsException
     */
    public function updateUser(
        User|int       $target,
        UpdateUserData $data,
        User           $actor,
        AuditContext   $context,
    ): User {
        $user = $target instanceof User ? $target : $this->getUserById($target);

        // 1. Security Policy Gate: Authorization & Escalation barrier
        if (! $this->policy->update($actor, $user, $data->role)) {
            Log::warning('Security Barrier: Unauthorized user update attempt', [
                'actor_id'  => $actor->id,
                'target_id' => $user->id,
                'ip'        => $context->ipAddress,
            ]);

            throw new UserAccessDeniedException('Anda tidak memiliki hak akses untuk mengubah data pengguna ini.');
        }

        // 2. Email uniqueness check if changed
        if ($data->email !== null && strtolower($data->email) !== strtolower($user->email)) {
            if (User::where('email', $data->email)->where('id', '!=', $user->id)->exists()) {
                throw new UserAlreadyExistsException('Alamat email ' . $data->email . ' sudah digunakan oleh akun lain.');
            }
        }

        // 3. Sync update to Logto Management API
        if (! empty($user->logto_id)) {
            try {
                $logtoPayload = [];

                if ($data->name !== null) {
                    $logtoPayload['name'] = $data->name;
                }

                if ($data->email !== null) {
                    $logtoPayload['primaryEmail'] = $data->email;
                }

                if ($data->phone !== null) {
                    $logtoPayload['primaryPhone'] = $data->phone;
                }

                if (! empty($logtoPayload)) {
                    $this->logtoM2M->updateUser($user->logto_id, $logtoPayload);
                }
            } catch (Exception $e) {
                Log::warning('Logto M2M user update sync error: ' . $e->getMessage(), [
                    'logto_id' => $user->logto_id,
                ]);
            }
        }

        // 4. Database Transaction for atomic updates
        DB::transaction(function () use ($user, $data): void {
            $fillable = [];

            if ($data->name !== null) {
                $fillable['name'] = $data->name;
            }

            if ($data->email !== null) {
                $fillable['email'] = $data->email;
            }

            if ($data->phone !== null) {
                $fillable['phone'] = $data->phone;
            }

            if ($data->address !== null) {
                $fillable['address'] = $data->address;
            }

            if ($data->avatar !== null) {
                $fillable['avatar'] = $data->avatar;
            }

            if ($data->customData !== null) {
                $fillable['custom_data'] = array_merge($user->custom_data ?? [], $data->customData);
            }

            if (! empty($fillable)) {
                $user->update($fillable);
            }

            // Role Update
            if ($data->role !== null && in_array($data->role, UserRole::getAllRoles(), true)) {
                $user->roles()->delete();
                $user->roles()->create(['role' => $data->role]);
            }
        });

        $user->refresh();
        $user->load('roles');

        // 5. Security Audit Log
        Log::info('AUDIT: User updated successfully', [
            'action'         => 'user.update',
            'actor_id'       => $actor->id,
            'target_id'      => $user->id,
            'updated_fields' => array_keys($data->toArray()),
            'ip'             => $context->ipAddress,
            'user_agent'     => $context->userAgent,
        ]);

        return $user;
    }

    /**
     * Change user status (Active or Suspended) with explicit idempotent state assignment.
     *
     * @throws UserAccessDeniedException
     * @throws CannotDeactivateProtectedAccountException
     * @throws InvalidUserStatusTransitionException
     */
    public function changeStatus(
        User|int     $target,
        UserStatus   $status,
        User         $actor,
        AuditContext $context,
        ?string      $reason = null,
    ): User {
        $user = $target instanceof User ? $target : $this->getUserById($target);

        // 1. Invariant: Anti-Self-Suspension
        if ($actor->id === $user->id && $status === UserStatus::Suspended) {
            throw new CannotDeactivateProtectedAccountException('Anda tidak dapat menangguhkan akun Anda sendiri.');
        }

        // 2. Invariant: Root Superadmin Immutability
        if ($user->isSuperadmin() && $status === UserStatus::Suspended) {
            throw new CannotDeactivateProtectedAccountException('Akun Superadmin tidak dapat dinonaktifkan demi kestabilan cluster.');
        }

        // 3. Security Policy Check
        if (! $this->policy->changeStatus($actor, $user)) {
            Log::warning('Security Barrier: Blocked status change attempt', [
                'actor_id'       => $actor->id,
                'target_id'      => $user->id,
                'desired_status' => $status->value,
                'ip'             => $context->ipAddress,
            ]);

            throw new UserAccessDeniedException('Anda tidak memiliki izin untuk mengubah status akun pengguna ini.');
        }

        // 4. Idempotency Check: if status is already the desired value, return without error
        if ($user->status === $status) {
            return $user;
        }

        $isSuspended = $status === UserStatus::Suspended;

        // 5. Sync to Logto Management API
        if (! empty($user->logto_id)) {
            try {
                $this->logtoM2M->toggleSuspend($user->logto_id, $isSuspended);
            } catch (Exception $e) {
                Log::warning('Logto M2M toggle suspend error: ' . $e->getMessage(), [
                    'logto_id'     => $user->logto_id,
                    'is_suspended' => $isSuspended,
                ]);
            }
        }

        // 6. Atomic Database Update
        DB::transaction(function () use ($user, $status): void {
            $user->status = $status;
            $user->save();
        });

        // 7. Security Audit Log
        Log::warning('AUDIT: User account status changed explicitly', [
            'action'     => 'user.change_status',
            'actor_id'   => $actor->id,
            'target_id'  => $user->id,
            'new_status' => $status->value,
            'reason'     => $reason,
            'ip'         => $context->ipAddress,
            'user_agent' => $context->userAgent,
        ]);

        return $user;
    }

    /**
     * Delete user using Soft Deletes to preserve referential integrity and audit trails.
     *
     * @throws CannotDeleteOwnAccountException
     * @throws CannotDeleteLastAdministratorException
     * @throws UserAccessDeniedException
     */
    public function deleteUser(
        User|int     $target,
        User         $actor,
        AuditContext $context,
    ): bool {
        $user = $target instanceof User ? $target : $this->getUserById($target);

        // 1. Invariant: Anti-Self-Harm (Cannot delete own account)
        if ($actor->id === $user->id) {
            throw new CannotDeleteOwnAccountException('Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // 2. Invariant: Last Superadmin Protection
        if ($user->isSuperadmin()) {
            $superadminCount = UserRole::where('role', UserRole::ROLE_SUPERADMIN)->count();

            if ($superadminCount <= 1) {
                throw new CannotDeleteLastAdministratorException('Tidak dapat menghapus Administrator atau Superadmin terakhir demi kestabilan sistem.');
            }
        }

        // 3. Security Policy Gate
        if (! $this->policy->delete($actor, $user)) {
            Log::warning('Security Barrier: Blocked user deletion attempt', [
                'actor_id'  => $actor->id,
                'target_id' => $user->id,
                'ip'        => $context->ipAddress,
            ]);

            throw new UserAccessDeniedException('Anda tidak memiliki izin untuk menghapus akun pengguna ini.');
        }

        // 4. Delete user in Logto Management API
        if (! empty($user->logto_id)) {
            try {
                $this->logtoM2M->deleteUser($user->logto_id);
            } catch (Exception $e) {
                Log::warning('Logto M2M delete sync error: ' . $e->getMessage(), [
                    'logto_id' => $user->logto_id,
                ]);
            }
        }

        // 5. Atomic soft deletion in database
        $userEmail = $user->email;

        DB::transaction(function () use ($user): void {
            $user->roles()->delete();
            $user->delete(); // Soft delete via Eloquent SoftDeletes
        });

        // 6. Security Audit Log
        Log::alert('AUDIT: User soft-deleted with preserved audit history', [
            'action'        => 'user.delete',
            'actor_id'      => $actor->id,
            'deleted_id'    => $user->id,
            'deleted_email' => $userEmail,
            'ip'            => $context->ipAddress,
            'user_agent'    => $context->userAgent,
        ]);

        return true;
    }

    /**
     * Reset target user's password with strict authorization check, Logto sync, and audit logging.
     *
     * @throws CannotResetOwnPasswordException
     * @throws UserAccessDeniedException
     */
    public function resetUserPassword(
        User|int     $target,
        string       $newPassword,
        User         $actor,
        AuditContext $context,
        ?string      $reason = null,
    ): User {
        return $this->passwordService->adminResetPassword(
            target:      $target,
            newPassword: $newPassword,
            actor:       $actor,
            context:     $context,
            reason:      $reason,
        );
    }

    /**
     * Change authenticated user's own password with current password verification.
     *
     * @throws InvalidCurrentPasswordException
     */
    public function changeOwnPassword(
        User         $user,
        string       $currentPassword,
        string       $newPassword,
        AuditContext $context,
    ): User {
        return $this->passwordService->changeOwnPassword(
            user:            $user,
            currentPassword: $currentPassword,
            newPassword:     $newPassword,
            context:         $context,
        );
    }
}
