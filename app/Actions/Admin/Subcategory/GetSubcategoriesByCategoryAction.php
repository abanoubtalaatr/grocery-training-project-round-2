<?php

namespace App\Actions\Admin\Subcategory;

use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Collection;

class GetSubcategoriesByCategoryAction
{
    public function run(int $categoryId): Collection
    {
        return Subcategory::where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
