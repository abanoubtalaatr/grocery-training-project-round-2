<?php

namespace App\Actions\Api\Subcategory;

use App\Models\Subcategory;
use Illuminate\Http\Request;

class GetAllSubcategoriesAction
{
    public function run(Request $request)
    {
        $query = Subcategory::query()
            ->with('category')
            ->withCount([
                'meals as meals_count' => fn ($q) => $q->available()
            ])
            ->active();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        return $query
            ->inRandomOrder()
            ->get();
    }
}