<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Hypervel\Support\Facades\Log;

/**
 * Class AuditPolicy
 *
 * Enterprise-Grade Security & Isolation Policy for Audit Trails and Cryptographic Access Logs.
 * Strictly enforces RBAC boundaries to prevent unauthorized reconnaissance.
 */
class AuditPolicy
{
    /**
     * Determine if the actor can view the password change audit log directory.
     */
    public function viewPasswordLogs(User $actor): bool
    {
        return $actor->canManageUsers();
    }

    /**
     * Determine if the actor can view the sign-in access log directory.
     */
    public function viewSignInLogs(User $actor): bool
    {
        return $actor->canManageUsers();
    }

    /**
     * Determine if the actor can view overall security statistics and metrics.
     */
    public function viewSecurityStats(User $actor): bool
    {
        return $actor->canManageUsers();
    }

    /**
     * Determine if the actor can view a specific target user's audit logs.
     * Prevents lower-tier admins from inspecting root/superadmin access trails.
     */
    public function viewUserAuditTrail(User $actor, User $target): bool
    {
        // 1. Superadmin has full visibility over the cluster
        if ($actor->isSuperadmin()) {
            return true;
        }

        // 2. Admin Account can only inspect regular users
        if ($actor->isAdmin()) {
            if ($target->isSuperadmin() || $target->isAdmin()) {
                Log::warning('Security Policy Violation: Admin attempted to inspect elevated account audit trail', [
                    'actor_id'  => $actor->id,
                    'target_id' => $target->id,
                ]);

                return false;
            }

            return true;
        }

        // 3. Regular users can only inspect their own logs
        return $actor->id === $target->id;
    }
}
