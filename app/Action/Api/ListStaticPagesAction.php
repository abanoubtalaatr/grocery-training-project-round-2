<?php

namespace App\Action\Api;

use App\Models\StaticPage;
use Illuminate\Http\Request;

class ListStaticPagesAction
{
    public function execute(Request $request, $user = null)
    {
        $query = StaticPage::query();

        if (!$user || !($user->is_admin ?? false)) {
            $query->published();
        }

        if ($request->has('published')) {
            $query->where('is_published', $request->boolean('published'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        $query->ordered();

        $perPage = $request->get('per_page', 0);

        if ($perPage) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }
}
