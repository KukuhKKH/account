<?php

namespace App\Services;

use App\Models\PasswordChangeLog;
use App\Models\User;
use App\Remote\LogtoRemote;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class PasswordChangeService
{
    public function __construct(
        protected LogtoRemote $logtoRemote,
    ) {}

    /**
     *
     * @param User   $user      User requesting password reset
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent string
     * @return bool True if email sent successfully
     * @throws Exception If user has no system ID or email sending fails
     */
    public function sendPasswordResetEmail(User $user, string $ipAddress, string $userAgent): bool
    {
        if (!$user->logto_id) {
            throw new Exception('User tidak memiliki ID sistem. Password tidak dapat direset.');
        }

        try {
            $this->logtoRemote->sendPasswordResetEmail($user->logto_id);

            $log = new PasswordChangeLog([
                'user_id'            => $user->id,
                'changed_by_user_id' => $user->id,
                'change_type'        => 'self_change',
                'ip_address'         => $ipAddress,
                'user_agent'         => $userAgent,
                'via_logto_api'      => true,
                'metadata'           => [
                    'method' => 'email_link',
                ],
            ]);

            return $log->save();
        } catch (RequestException|ConnectionException $e) {
            Log::error('Failed to send password reset email', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            throw new Exception('Gagal mengirim email reset password. Silakan coba lagi.');
        }
    }

    /**
     * @param User   $user            User changing their password
     * @param string $currentPassword Current password for verification
     * @param string $newPassword     New password to set
     * @param string $ipAddress       IP address of the request
     * @param string $userAgent       User agent string
     * @return bool True if password changed successfully
     * @throws Exception If user has no system ID, current password is invalid, or change fails
     */
    public function changeUserPassword(
        User   $user,
        string $currentPassword,
        string $newPassword,
        string $ipAddress,
        string $userAgent,
    ): bool {
        if (!$user->logto_id) {
            throw new Exception('User tidak memiliki ID sistem. Password tidak dapat diubah.');
        }

        try {
            $this->logtoRemote->verifyUserPassword($user->logto_id, $currentPassword);
            $this->logtoRemote->updateUserPassword($user->logto_id, $newPassword);

            $log = new PasswordChangeLog([
                'user_id'            => $user->id,
                'changed_by_user_id' => $user->id,
                'change_type'        => 'self_change',
                'ip_address'         => $ipAddress,
                'user_agent'         => $userAgent,
                'via_logto_api'      => true,
                'metadata'           => [
                    'method' => 'direct_change',
                ],
            ]);

            return $log->save();
        } catch (RequestException|ConnectionException $e) {
            Log::error('Failed to change password', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            if ($e->getCode() === 401 || str_contains($e->getMessage(), 'password')) {
                throw new Exception('Password saat ini tidak valid.');
            }

            throw new Exception('Gagal mengubah password. Silakan coba lagi.');
        }
    }

    /**
     * @param User        $targetUser  User whose password will be reset
     * @param User        $adminUser   Admin/Superadmin performing the reset
     * @param string      $newPassword New password to set
     * @param string|null $reason      Reason for password reset (for audit trail)
     * @param string      $ipAddress   IP address of the request
     * @param string      $userAgent   User agent string
     * @return bool True if password reset successfully
     * @throws Exception If unauthorized, target user has no system ID, or reset fails
     */
    public function resetUserPassword(
        User    $targetUser,
        User    $adminUser,
        string  $newPassword,
        ?string $reason,
        string  $ipAddress,
        string  $userAgent,
    ): bool {
        if (!$targetUser->logto_id) {
            throw new Exception('User tidak memiliki ID sistem. Password tidak dapat direset.');
        }

        if (!$adminUser->isSuperadmin() && !$adminUser->isAdmin()) {
            throw new Exception('Anda tidak memiliki izin untuk mereset password user.');
        }

        if ($adminUser->isAdmin() && !$adminUser->isSuperadmin()) {
            if ($targetUser->isSuperadmin()) {
                throw new Exception('Admin Account tidak dapat mereset password Superadmin.');
            }
            if ($targetUser->isAdmin()) {
                throw new Exception('Admin Account tidak dapat mereset password Admin Account lainnya.');
            }
        }

        try {
            $this->logtoRemote->updateUserPassword($targetUser->logto_id, $newPassword);

            $log = new PasswordChangeLog([
                'user_id'            => $targetUser->id,
                'changed_by_user_id' => $adminUser->id,
                'change_type'        => 'admin_reset',
                'ip_address'         => $ipAddress,
                'user_agent'         => $userAgent,
                'reason'             => $reason,
                'via_logto_api'      => true,
                'metadata'           => [
                    'admin_name'        => $adminUser->name,
                    'admin_email'       => $adminUser->email,
                    'target_user_name'  => $targetUser->name,
                    'target_user_email' => $targetUser->email,
                ],
            ]);

            $log->save();
            Log::warning('Admin password reset', [
                'admin_user_id'  => $adminUser->id,
                'admin_email'    => $adminUser->email,
                'target_user_id' => $targetUser->id,
                'target_email'   => $targetUser->email,
                'reason'         => $reason,
                'ip'             => $ipAddress,
            ]);

            return true;
        } catch (RequestException|ConnectionException $e) {
            Log::error('Failed to reset user password', [
                'admin_user_id'  => $adminUser->id,
                'target_user_id' => $targetUser->id,
                'error'          => $e->getMessage(),
            ]);

            throw new Exception('Gagal mereset password user. Silakan coba lagi.');
        }
    }

    /**
     * @param User $user  User to get password change history for
     * @param int  $limit Maximum number of records to return (default: 10)
     * @return array<int, array{id: int, change_type: string, changed_by: array{name: string, email: string}|null, reason: string|null, ip_address: string|null, created_at: string, created_at_human: string}>
     */
    public function getPasswordChangeHistory(User $user, int $limit = 10): array
    {
        return PasswordChangeLog::query()
            ->where('user_id', '=', $user->id)
            ->with(['changedBy:id,name,email'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id'               => $log->id,
                    'change_type'      => $log->change_type,
                    'changed_by'       => $log->changedBy ? [
                        'name'  => $log->changedBy->name,
                        'email' => $log->changedBy->email,
                    ] : null,
                    'reason'           => $log->reason,
                    'ip_address'       => $log->ip_address,
                    'created_at'       => $log->created_at->format('d M Y, H:i'),
                    'created_at_human' => $log->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }
}
