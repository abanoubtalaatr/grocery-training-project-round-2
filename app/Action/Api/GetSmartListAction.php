<?php

namespace App\Action\Api;

use App\Models\SmartList;

class GetSmartListAction
{
    public function execute($user, string $id): SmartList
    {
        return SmartList::where('user_id', $user->id)->with('meals')->findOrFail($id);
    }
}
