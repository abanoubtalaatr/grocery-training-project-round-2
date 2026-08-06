<?php

namespace App\Actions\Api\Faq;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class GetFaqsAction
{
    public function run(Request $request): array
    {
        $query = Faq::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'LIKE', "%{$search}%")
                    ->orWhere('answer', 'LIKE', "%{$search}%");
            });
        }

        $query->ordered();

        $categories = null;
        if ($request->boolean('with_categories', false)) {
            $categories = Faq::active()
                ->distinct('category')
                ->pluck('category')
                ->filter()
                ->values();
        }

        $perPage = (int) $request->get('per_page', 15);
        $faqs = $query->paginate($perPage);

        return [
            'faqs' => $faqs,
            'categories' => $categories,
        ];
    }
}
