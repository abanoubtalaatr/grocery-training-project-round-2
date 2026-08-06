<?php

namespace App\Action\Api;

class GetMeAction
{
    public function execute($user)
    {
        // load minimal relations used by profile endpoints
        return $user->load(['addresses', 'favorites.meal.category', 'favorites.meal.subcategory']);
    }
}
