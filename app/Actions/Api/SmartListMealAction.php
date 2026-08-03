<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\DTOs\Api\SmartListMealData;
use App\DTOs\Api\SmartListMealResult;
use App\Models\SmartList;

class SmartListMealAction
{
    public function execute(SmartList $smartList, SmartListMealData $data): SmartListMealResult
    {
        $attached = $smartList->meals()->syncWithoutDetaching([$data->mealId]);

        $isNew = !empty($attached['attached']);

        return new SmartListMealResult(
            smartList: $smartList->load('meals')->loadCount('meals'),
            isNew: $isNew
        );
    }

    public function remove(SmartList $smartList, int $mealId): ?SmartList
    {
        $exists = $smartList->meals()->where('meal_id', $mealId)->exists();
        
        if (!$exists) {
            return null;
        }

        $smartList->meals()->detach($mealId);

        return $smartList->load('meals')->loadCount('meals');
    }
}