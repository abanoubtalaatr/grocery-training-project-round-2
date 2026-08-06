<?php

namespace App\Actions\Api\Subcategory;

use App\Models\Subcategory;

class GetSubcategoryAction
{
    public function run(int $id): Subcategory
    {
        return Subcategory::with([
                'category',
                'meals' => fn ($q) => $q->available()->limit(10)
            ])
            ->withCount([
                'meals as meals_count' => fn ($q) => $q->available()
            ])
            ->findOrFail($id);
    }
}