<?php

namespace App\Actions\Api\Meal;

use App\Models\SmartList;

class AddMealToSmartListAction
{
    /**
     * Execute the action to add a meal to a smart list.
     */
    public function execute(SmartList $smartList, int $mealId): SmartList
    {
        $smartList->meals()->attach($mealId);

        return $smartList->load('meals');
    }
}
