<?php

namespace App\Actions\Admin\Category;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class IndexCategoryAction
{
    public function run(array $filters = []): LengthAwarePaginator
    {
        $query = Category::query()->withCount(['meals', 'subcategories']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', $filters['status'] === 'active');
        }

        return $query->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();
    }
}
