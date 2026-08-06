<?php

namespace App\Action\Api;

use App\Models\SmartList;

class ShowSmartListAction
{
    public function execute($user, $id): SmartList
    {
        return SmartList::where('user_id', $user->id)->with('meals')->findOrFail($id);
    }
}
