<?php

namespace App\Actions\Api\Favorite;

use App\Models\User;

class GetFavoritesAction
{
    public function run(User $user)
    {
        return $user->favorites()
            ->with([
                'meal.category',
                'meal.subcategory',
            ])
            ->latest()
            ->get();
    }
}