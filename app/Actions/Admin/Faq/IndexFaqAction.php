<?php

namespace App\Actions\Admin\Faq;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class IndexFaqAction
{
    public function run(Request $request): array
    {
        $query = Faq::withTrashed();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($status = $request->input('status')) {
            if ($status === 'active') $query->where('is_active', true)->whereNull('deleted_at');
            elseif ($status === 'inactive') $query->where('is_active', false)->whereNull('deleted_at');
            elseif ($status === 'trashed') $query->onlyTrashed();
        }

        $faqs = $query->ordered()->paginate(20)->withQueryString();
        $categories = Faq::whereNull('deleted_at')->distinct()->pluck('category')->filter()->sort()->values();

        return compact('faqs', 'categories');
    }
}
