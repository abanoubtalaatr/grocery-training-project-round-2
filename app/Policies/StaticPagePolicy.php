<?php

namespace App\Policies;

use App\Models\StaticPage;
use App\Models\User;

class StaticPagePolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StaticPage $staticPage): bool
    {
        if ($staticPage->is_published) {
            return true;
        }

        return $user->is_admin;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StaticPage $staticPage): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StaticPage $staticPage): bool
    {
        return $user->is_admin;
    }
}
