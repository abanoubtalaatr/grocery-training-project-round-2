<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, User $model): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, User $model): bool
    {
        return $user->is_admin;
    }

    public function toggleStatus(User $user, User $model): bool
    {
        // Admins can toggle any user except themselves
        return $user->is_admin && $user->id !== $model->id;
    }
}
