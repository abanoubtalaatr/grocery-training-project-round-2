<?php

namespace App\Action\Api;

use App\Models\SmartList;

class GetSmartListsAction
{
    public function execute($user)
    {
        return SmartList::where('user_id', $user->id)->with('meals')->get();
    }
}
