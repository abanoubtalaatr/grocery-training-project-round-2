<?php

namespace App\Actions\Api\Profile;

use App\Models\User;

class ShowProfileAction
{
    public function run(User $user): User
    {
        $user->load(['addresses', 'favorites.meal.category', 'favorites.meal.subcategory']);
        return $user;
    }
}
