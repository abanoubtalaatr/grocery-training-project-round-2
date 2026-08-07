<?php

namespace App\Actions\Admin\Favorite;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class IndexFavoriteAction
{
    public function run(Request $request): LengthAwarePaginator
    {
        $query = Favorite::with(['user', 'meal']);

        if ($search = $request->input('search')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('meal', fn($q) => $q->where('title', 'like', "%{$search}%"));
        }

        return $query->latest()->paginate(20)->withQueryString();
    }
}
