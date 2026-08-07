<?php

namespace App\Policies;

use App\Models\Meal;
use App\Models\User;

class MealPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, Meal $meal): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, Meal $meal): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, Meal $meal): bool
    {
        return $user->is_admin;
    }

    public function restore(User $user, Meal $meal): bool
    {
        return $user->is_admin;
    }

    public function forceDelete(User $user, Meal $meal): bool
    {
        return $user->is_admin;
    }
}
