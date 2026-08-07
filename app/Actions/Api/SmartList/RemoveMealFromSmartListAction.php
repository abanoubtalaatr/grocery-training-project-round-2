<?php

namespace App\Actions\Api\SmartList;

use App\Models\SmartList;

class RemoveMealFromSmartListAction
{
    /**
     * Execute the action to remove a meal from a smart list.
     */
    public function execute(SmartList $smartList, string $mealId): SmartList
    {
        $smartList->meals()->detach($mealId);

        return $smartList->load('meals');
    }
}
