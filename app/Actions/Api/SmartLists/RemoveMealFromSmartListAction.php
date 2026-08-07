<?php

namespace App\Actions\Api\SmartLists;

use App\Models\Meal;
use App\Models\SmartList;

class RemoveMealFromSmartListAction
{
    public function execute(SmartList $smartList, Meal $meal): SmartList
    {
        $smartList->meals()->detach($meal->id);

        return $smartList->fresh()->load('meals');
    }
}
