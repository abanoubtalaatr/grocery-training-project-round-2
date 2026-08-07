<?php

namespace App\Actions\Admin\Meal;

use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class IndexMealAction
{
    public function run(Request $request): LengthAwarePaginator
    {
        $query = Meal::withTrashed()
            ->with(['category', 'subcategory'])
            ->withCount(['favorites']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($status = $request->input('status')) {
            if ($status === 'available') {
                $query->where('is_available', true)->whereNull('deleted_at');
            } elseif ($status === 'unavailable') {
                $query->where('is_available', false)->whereNull('deleted_at');
            } elseif ($status === 'trashed') {
                $query->onlyTrashed();
            } elseif ($status === 'low_stock') {
                $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10)->whereNull('deleted_at');
            } elseif ($status === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0)->whereNull('deleted_at');
            }
        }

        if ($featured = $request->input('featured')) {
            $query->where('is_featured', true);
        }

        return $query->orderByDesc('created_at')->paginate(20)->withQueryString();
    }
}
