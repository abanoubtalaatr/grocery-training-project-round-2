<?php

namespace App\Action\Api;

use App\Models\SmartList;

class DeleteSmartListAction
{
    public function execute($user, $id): void
    {
        $smartList = SmartList::where('user_id', $user->id)->findOrFail($id);
        $smartList->meals()->detach();
        $smartList->delete();
    }
}
