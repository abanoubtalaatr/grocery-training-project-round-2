<?php

namespace App\Actions\Api\Meal;

use App\Models\Meal;

class ShowMealAction
{
    public function run(string $id): Meal
    {
        return Meal::with([
            'category',
            'subcategory',
            'reviews' => fn ($q) => $q->approved()->with('user:id,username,firstname,lastname')->orderBy('created_at', 'desc'),
        ])->findOrFail($id);
    }
}
