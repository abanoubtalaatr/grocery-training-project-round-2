<?php

namespace App\Action\Api;

use App\Models\SmartList;

class RemoveMealFromSmartListAction
{
    public function execute($user, string $id, string $mealId)
    {
        $smartList = SmartList::where('user_id', $user->id)->findOrFail($id);
        $smartList->meals()->detach($mealId);
        return $smartList->load('meals');
    }
}
