<?php

namespace App\Policies;

use App\Models\Favorite;
use App\Models\User;

class FavoritePolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Favorite $favorite): bool
    {
        return $user->id === $favorite->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Favorite $favorite): bool
    {
        return $user->id === $favorite->user_id;
    }
}
