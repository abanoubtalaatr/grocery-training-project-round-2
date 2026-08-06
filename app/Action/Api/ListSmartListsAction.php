<?php

namespace App\Action\Api;

use App\Models\SmartList;
use Illuminate\Http\Request;

class ListSmartListsAction
{
    public function execute($user, Request $request)
    {
        $query = SmartList::where('user_id', $user->id)->with('meals');

        // Optional filtering, pagination can be added here
        $perPage = (int) $request->get('per_page', 0);

        if ($perPage > 0) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }
}
