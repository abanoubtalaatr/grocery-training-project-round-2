<?php

namespace App\Action\Api;

use App\Models\Meal;

class ShowMealAction
{
    public function execute(Meal $meal): Meal
    {
        return $meal->load([
            'category',
            'subcategory',
            'reviews' => fn ($q) => $q->approved()->with('user:id,username,firstname,lastname')->orderBy('created_at', 'desc'),
        ]);
    }
}
