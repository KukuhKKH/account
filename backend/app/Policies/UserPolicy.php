<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserRole;
use Hypervel\Support\Facades\Log;

/**
 * Class UserPolicy
 *
 * Defense-in-Depth Authorization & Governance Policy
 * Protects identity cluster against privilege escalation, accidental lockout, and self-harm.
 */
class UserPolicy
{
    /**
     * Determine if the actor can view the user directory.
     */
    public function viewAny(User $actor): bool
    {
        return $actor->canManageUsers();
    }

    /**
     * Determine if the actor can view a specific user's full details.
     */
    public function view(User $actor, User $target): bool
    {
        if ($actor->isSuperadmin()) {
            return true;
        }

        if ($actor->isAdmin()) {
            return true;
        }

        return $actor->id === $target->id;
    }

    /**
     * Determine if the actor can create a new user with the requested role.
     * Enforces strict privilege escalation barriers.
     */
    public function create(User $actor, ?string $targetRole = null): bool
    {
        if ($targetRole === null) {
            return $actor->canManageUsers();
        }

        // Only Superadmin can create other Superadmins or Admins
        if ($targetRole === UserRole::ROLE_SUPERADMIN || $targetRole === UserRole::ROLE_ADMIN) {
            return $actor->isSuperadmin();
        }

        // Both Superadmin and Admin Account can create standard Users
        if ($targetRole === UserRole::ROLE_USER) {
            return $actor->canManageUsers();
        }

        return false;
    }

    /**
     * Determine if the actor can update the target user and optionally assign a new role.
     */
    public function update(User $actor, User $target, ?string $newRole = null): bool
    {
        // 1. Superadmin can update anyone
        if ($actor->isSuperadmin()) {
            // Guard: Prevent self-demotion if actor is the last Superadmin
            if ($actor->id === $target->id && $newRole !== null && $newRole !== UserRole::ROLE_SUPERADMIN) {
                $superadminCount = UserRole::where('role', UserRole::ROLE_SUPERADMIN)->count();

                if ($superadminCount <= 1) {
                    Log::warning('Security Policy Violation: Last Superadmin attempted self-demotion', [
                        'actor_id' => $actor->id,
                    ]);

                    return false;
                }
            }

            return true;
        }

        // 2. Admin Account rules: Cannot modify Superadmin or fellow Admin Accounts
        if ($actor->isAdmin()) {
            // Admin can update their own personal info, but CANNOT change their own role
            if ($actor->id === $target->id) {
                return $newRole === null || $newRole === UserRole::ROLE_ADMIN;
            }

            // Target must be a standard User
            if ($target->isSuperadmin() || $target->isAdmin()) {
                Log::warning('Security Policy Violation: Admin attempted to modify elevated account', [
                    'actor_id'  => $actor->id,
                    'target_id' => $target->id,
                ]);

                return false;
            }

            // Cannot elevate target to Admin or Superadmin
            if ($newRole !== null && $newRole !== UserRole::ROLE_USER) {
                Log::warning('Security Policy Violation: Admin attempted privilege escalation', [
                    'actor_id' => $actor->id,
                    'new_role' => $newRole,
                ]);

                return false;
            }

            return true;
        }

        // 3. Regular User: Can only update own profile without touching role
        if ($actor->id === $target->id) {
            return $newRole === null || $newRole === UserRole::ROLE_USER;
        }

        return false;
    }

    /**
     * Determine if the actor can delete the target user.
     * Enforces fail-safe anti-lockout & anti-self-harm safeguards.
     */
    public function delete(User $actor, User $target): bool
    {
        // Rule 1: Self-Deletion Protection (Fail-Safe)
        if ($actor->id === $target->id) {
            Log::warning('Security Policy Violation: Self-deletion attempt blocked', [
                'actor_id' => $actor->id,
            ]);

            return false;
        }

        // Rule 2: Last Superadmin Immutability Guard
        if ($target->isSuperadmin()) {
            $superadminCount = UserRole::where('role', UserRole::ROLE_SUPERADMIN)->count();

            if ($superadminCount <= 1) {
                Log::critical('Security Policy Alert: Attempted deletion of LAST remaining Superadmin blocked', [
                    'actor_id'  => $actor->id,
                    'target_id' => $target->id,
                ]);

                return false;
            }
        }

        // Rule 3: Superadmin can delete any other account
        if ($actor->isSuperadmin()) {
            return true;
        }

        // Rule 4: Admin Account can ONLY delete regular users
        if ($actor->isAdmin()) {
            if ($target->isSuperadmin() || $target->isAdmin()) {
                Log::warning('Security Policy Violation: Admin attempted to delete elevated account', [
                    'actor_id'  => $actor->id,
                    'target_id' => $target->id,
                ]);

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Determine if the actor can change the status (activate/suspend) of the target user.
     */
    public function changeStatus(User $actor, User $target): bool
    {
        // Rule 1: Cannot suspend or alter own account status
        if ($actor->id === $target->id) {
            Log::warning('Security Policy Violation: Self-status modification attempt blocked', [
                'actor_id' => $actor->id,
            ]);

            return false;
        }

        // Rule 2: Superadmin accounts can never be suspended (Cluster Stability Guard)
        if ($target->isSuperadmin()) {
            Log::warning('Security Policy Violation: Attempt to change Superadmin account status blocked', [
                'actor_id'  => $actor->id,
                'target_id' => $target->id,
            ]);

            return false;
        }

        // Rule 3: Superadmin can manage status of Admins and Users
        if ($actor->isSuperadmin()) {
            return true;
        }

        // Rule 4: Admin Account can ONLY manage status of regular Users
        if ($actor->isAdmin()) {
            if ($target->isAdmin()) {
                Log::warning('Security Policy Violation: Admin attempted to change fellow Admin status', [
                    'actor_id'  => $actor->id,
                    'target_id' => $target->id,
                ]);

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Determine if the actor can suspend or unsuspend the target user (legacy alias).
     */
    public function toggleStatus(User $actor, User $target): bool
    {
        return $this->changeStatus($actor, $target);
    }

    /**
     * Determine if the actor can reset the target user's password.
     */
    public function resetPassword(User $actor, User $target): bool
    {
        // Rule 0: Anti-Self Reset Protection (Strict)
        // Pengguna dilarang mereset kata sandi akunnya sendiri via endpoint reset admin.
        // Penggantian kata sandi sendiri WAJIB melalui menu Profil dengan verifikasi kata sandi lama.
        if ($actor->id === $target->id) {
            Log::warning('Security Policy Violation: Self-password reset attempt blocked on admin endpoint', [
                'actor_id' => $actor->id,
            ]);

            return false;
        }

        // 1. Superadmin can reset any user's password
        if ($actor->isSuperadmin()) {
            return true;
        }

        // 2. Admin Account can ONLY reset regular User password (cannot reset fellow Admin or Superadmin)
        if ($actor->isAdmin()) {
            if ($target->isSuperadmin() || $target->isAdmin()) {
                Log::warning('Security Policy Violation: Admin attempted to reset elevated account password', [
                    'actor_id'  => $actor->id,
                    'target_id' => $target->id,
                ]);

                return false;
            }

            return true;
        }

        return false;
    }
}
