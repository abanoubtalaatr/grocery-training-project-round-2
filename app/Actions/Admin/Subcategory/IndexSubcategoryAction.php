<?php

namespace App\Actions\Admin\Subcategory;

use App\Models\Subcategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class IndexSubcategoryAction
{
    public function run(array $filters = []): LengthAwarePaginator
    {
        $query = Subcategory::query()->with('category')->withCount('meals');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['category_id']) && $filters['category_id'] !== '' && $filters['category_id'] !== null) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== null) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        return $query->orderBy('order', 'asc')
            ->paginate(15)
            ->withQueryString();
    }
}
