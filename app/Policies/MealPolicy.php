<?php

namespace App\Policies;

use App\Models\Meal;
use App\Models\User;

class MealPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Meal $meal): bool
    {
        return true;
    }
}
