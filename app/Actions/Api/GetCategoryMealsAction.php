<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\DTOs\Api\CategoryMealsData;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetCategoryMealsAction
{
    public function handle(Category $category, CategoryMealsData $data): LengthAwarePaginator
    {
        $query = $category->meals()
            ->with('subcategory')
            ->available();

        if ($data->featured !== null) {
            $data->featured
                ? $query->featured()
                : $query->where('is_featured', false);
        }

        if ($data->subcategoryId !== null) {
            $query->where('subcategory_id', $data->subcategoryId);
        }

        if ($data->inStock !== null) {
            $data->inStock
                ? $query->inStock()
                : $query->outOfStock();
        }

        $sortBy = $data->sortBy;
        $sortOrder = $data->sortOrder;

        if ($sortBy === 'newest') {
            $sortBy = 'created_at';
            $sortOrder = 'desc';
        }

        if ($sortBy === 'price') {
            $query->orderByRaw(
                "COALESCE(discount_price, price) {$sortOrder}"
            );
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query->paginate($data->perPage ?? 15);
    }
}
