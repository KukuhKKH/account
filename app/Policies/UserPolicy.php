<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function view(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        if ($user->isAdmin() && !$user->isSuperadmin() && $model->isSuperadmin()) {
            return false;
        }

        return $user->canManageUsers();
    }

    public function create(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function update(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        if ($user->isAdmin() && !$user->isSuperadmin() && $model->isSuperadmin()) {
            return false;
        }

        return $user->canManageUsers();
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->isAdmin() && !$user->isSuperadmin() && $model->isSuperadmin()) {
            return false;
        }

        return $user->canManageUsers();
    }


    public function restore(User $user, User $model): bool
    {
        if ($user->isAdmin() && !$user->isSuperadmin() && $model->isSuperadmin()) {
            return false;
        }

        return $user->canManageUsers();
    }


    public function forceDelete(User $user, User $model): bool
    {
        return $user->isSuperadmin();
    }
}
