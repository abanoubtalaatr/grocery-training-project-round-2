<?php

namespace App\Actions\Api\StaticPage;

use App\Models\StaticPage;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class GetStaticPagesAction
{
    public function run(Request $request): LengthAwarePaginator
    {
        $query = StaticPage::query();

        if (! $request->user() || ! $request->user()->is_admin) {
            $query->published();
        }

        if ($request->has('published')) {
            $query->where('is_published', $request->boolean('published'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        $query->ordered();

        $perPage = (int) $request->get('per_page', 20);

        return $query->paginate($perPage);
    }
}
