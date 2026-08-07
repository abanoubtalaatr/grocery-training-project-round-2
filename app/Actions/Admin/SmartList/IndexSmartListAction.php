<?php

namespace App\Actions\Admin\SmartList;

use App\Models\SmartList;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class IndexSmartListAction
{
    public function run(Request $request): LengthAwarePaginator
    {
        $query = SmartList::with(['user'])->withCount(['meals as items_count']);

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->latest()->paginate(20)->withQueryString();
    }
}
