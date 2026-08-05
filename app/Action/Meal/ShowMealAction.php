<?php

namespace App\Action\Meal;

use App\Models\Meal;

class ShowMealAction
{
    public function handle(string $id)
    {
        return Meal::with([
            'category',
            'subcategory',
            'reviews' => fn ($q) => $q->approved()->with('user:id,username,firstname,lastname')->orderBy('created_at', 'desc'),
        ])->findOrFail($id);
    }
}
