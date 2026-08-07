<?php

namespace App\Actions\Admin;

use App\Models\Meal;

class MealShowAction
{
    public function execute(Meal $meal): Meal
    {
        $meal->load(['category', 'subcategory', 'reviews' => fn ($q) => $q->approved()->orderBy('created_at', 'desc')]);

        return $meal;
    }
}
