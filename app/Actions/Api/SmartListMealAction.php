<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\DTOs\Api\SmartListMealData;
use App\Models\SmartList;

class SmartListMealAction
{
    public function execute(SmartList $smartList, SmartListMealData $data): SmartList
    {
        $smartList->meals()->syncWithoutDetaching([$data->mealId]);
        return  $smartList->load('meals');
    }

    public function remove(SmartList $smartList, int $mealId): SmartList
    {
        $smartList->meals()->detach($mealId);

        return $smartList->load('meals');
    }

}
