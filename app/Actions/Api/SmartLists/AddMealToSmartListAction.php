<?php

namespace App\Actions\Api\SmartLists;

use App\Models\SmartList;

class AddMealToSmartListAction
{
    public function execute(SmartList $smartList, int $mealId): SmartList
    {
        $smartList->meals()->syncWithoutDetaching([$mealId]);

        return $smartList->fresh()->load('meals');
    }
}
