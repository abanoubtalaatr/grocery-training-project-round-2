<?php

namespace App\Actions\Api\SmartList;

use App\Models\SmartList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class IndexSmartListAction
{
    public function run(): Collection
    {
        return SmartList::where('user_id', Auth::id())
            ->with('meals')
            ->get();
    }
}