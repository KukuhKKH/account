<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\PasswordChangeType;
use App\Exceptions\User\CannotResetOwnPasswordException;
use App\Exceptions\User\InvalidCurrentPasswordException;
use App\Exceptions\User\UserAccessDeniedException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\PasswordChangeLog;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\Audit\AuditContext;
use Exception;
use Hypervel\Support\Facades\DB;
use Hypervel\Support\Facades\Hash;
use Hypervel\Support\Facades\Log;

class PasswordChangeService
{
    public function __construct(
        protected LogtoM2MService $logtoM2M,
        protected UserPolicy      $policy,
    ) {
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
        // 1. Verify current password
        if (! Hash::check($currentPassword, $user->password)) {
            Log::warning('Security Alert: Failed current password verification during profile password change', [
                'user_id'    => $user->id,
                'user_email' => $user->email,
                'ip'         => $context->ipAddress,
            ]);

            throw new InvalidCurrentPasswordException('Kata sandi lama yang Anda masukkan salah.');
        }

        // 2. Sync to Logto Management API
        $viaLogto = false;

        if (! empty($user->logto_id)) {
            try {
                $this->logtoM2M->setUserPassword($user->logto_id, $newPassword);
                $viaLogto = true;
            } catch (Exception $e) {
                Log::warning('Logto M2M user self-password change sync error: ' . $e->getMessage(), [
                    'logto_id' => $user->logto_id,
                ]);
            }
        }

        // 3. Atomic Database Transaction: update password & insert PasswordChangeLog
        DB::transaction(function () use ($user, $newPassword, $context, $viaLogto): void {
            $user->password = Hash::make($newPassword);
            $user->save();

            PasswordChangeLog::create([
                'user_id'            => $user->id,
                'changed_by_user_id' => $user->id,
                'change_type'        => PasswordChangeType::SelfChange,
                'ip_address'         => $context->ipAddress,
                'user_agent'         => $context->userAgent,
                'reason'             => 'User changed own password via profile security settings',
                'via_logto_api'      => $viaLogto,
                'metadata'           => [
                    'user_email' => $user->email,
                    'roles'      => $user->getRoleNames(),
                ],
            ]);
        });

        // 4. Security Audit Log
        Log::info('AUDIT: User successfully changed own password', [
            'action'     => 'user.change_own_password',
            'user_id'    => $user->id,
            'user_email' => $user->email,
            'via_logto'  => $viaLogto,
            'ip'         => $context->ipAddress,
            'user_agent' => $context->userAgent,
        ]);

        return $user;
    }

    /**
     * Reset target user's password with strict authorization check, Logto sync, and audit logging.
     *
     * @throws CannotResetOwnPasswordException
     * @throws UserAccessDeniedException
     * @throws UserNotFoundException
     */
    public function adminResetPassword(
        User|int     $target,
        string       $newPassword,
        User         $actor,
        AuditContext $context,
        ?string      $reason = null,
    ): User {
        $user = $target instanceof User ? $target : $this->resolveUser($target);

        // 1. Invariant: Anti-Self-Reset on Admin Endpoint
        if ($actor->id === $user->id) {
            throw new CannotResetOwnPasswordException('Anda tidak dapat mereset kata sandi akun Anda sendiri melalui menu ini. Silakan gunakan menu Profil Pengguna & Kunci Keamanan dengan memasukkan kata sandi lama Anda.');
        }

        // 2. Security Policy Gate
        if (! $this->policy->resetPassword($actor, $user)) {
            Log::warning('Security Barrier: Unauthorized password reset attempt', [
                'actor_id'  => $actor->id,
                'target_id' => $user->id,
                'ip'        => $context->ipAddress,
            ]);

            throw new UserAccessDeniedException('Anda tidak memiliki wewenang untuk mereset kata sandi akun ini.');
        }

        // 3. Sync to Logto Management API
        $viaLogto = false;

        if (! empty($user->logto_id)) {
            try {
                $this->logtoM2M->setUserPassword($user->logto_id, $newPassword);
                $viaLogto = true;
            } catch (Exception $e) {
                Log::warning('Logto M2M password reset sync error: ' . $e->getMessage(), [
                    'logto_id' => $user->logto_id,
                ]);
            }
        }

        // 4. Atomic Database Transaction: update password & insert PasswordChangeLog
        DB::transaction(function () use ($user, $newPassword, $actor, $reason, $context, $viaLogto): void {
            $user->password = Hash::make($newPassword);
            $user->save();

            PasswordChangeLog::create([
                'user_id'            => $user->id,
                'changed_by_user_id' => $actor->id,
                'change_type'        => PasswordChangeType::AdminReset,
                'ip_address'         => $context->ipAddress,
                'user_agent'         => $context->userAgent,
                'reason'             => $reason ?? 'Admin password reset via dashboard',
                'via_logto_api'      => $viaLogto,
                'metadata'           => [
                    'actor_email'  => $actor->email,
                    'actor_role'   => $actor->getRoleNames(),
                    'target_email' => $user->email,
                ],
            ]);
        });

        // 5. Security Audit Trail
        Log::warning('AUDIT: User password reset by administrator', [
            'action'       => 'user.reset_password',
            'actor_id'     => $actor->id,
            'target_id'    => $user->id,
            'target_email' => $user->email,
            'via_logto'    => $viaLogto,
            'ip'           => $context->ipAddress,
            'user_agent'   => $context->userAgent,
        ]);

        return $user;
    }

    /**
     * System-initiated password reset / webhook synchronizer.
     */
    public function systemResetPassword(
        User|int           $target,
        string             $newPassword,
        AuditContext       $context,
        ?string            $reason = null,
        PasswordChangeType $changeType = PasswordChangeType::SystemReset,
    ): User {
        $user = $target instanceof User ? $target : $this->resolveUser($target);

        DB::transaction(function () use ($user, $newPassword, $context, $reason, $changeType): void {
            $user->password = Hash::make($newPassword);
            $user->save();

            PasswordChangeLog::create([
                'user_id'            => $user->id,
                'changed_by_user_id' => null,
                'change_type'        => $changeType,
                'ip_address'         => $context->ipAddress,
                'user_agent'         => $context->userAgent,
                'reason'             => $reason ?? 'Automated system password sync',
                'via_logto_api'      => false,
                'metadata'           => [
                    'target_email' => $user->email,
                    'system_node'  => gethostname() ?: 'banglipai-identity',
                ],
            ]);
        });

        Log::info('AUDIT: System password sync completed', [
            'action'      => 'user.system_password_sync',
            'user_id'     => $user->id,
            'user_email'  => $user->email,
            'change_type' => $changeType->value,
            'ip'          => $context->ipAddress,
        ]);

        return $user;
    }

    /**
     * Helper to resolve User instance from ID.
     *
     * @throws UserNotFoundException
     */
    protected function resolveUser(int $id): User
    {
        /** @var User|null $user */
        $user = User::with('roles')->find($id);

        if (! $user) {
            throw new UserNotFoundException('Pengguna dengan ID ' . $id . ' tidak ditemukan dalam sistem.');
        }

        return $user;
    }
}
