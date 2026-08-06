<?php

namespace App\Actions\Api\SmartList;

use App\Models\SmartList;
use Illuminate\Support\Facades\Auth;

class ShowSmartListAction
{
    public function run(int $id): SmartList
    {
        return SmartList::where('user_id', Auth::id())
            ->with('meals')
            ->findOrFail($id);
    }
}