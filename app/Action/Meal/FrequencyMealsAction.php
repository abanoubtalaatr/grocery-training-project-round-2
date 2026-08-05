<?php

namespace App\Action\Meal;

use App\Models\Meal;
use App\Services\FrequencyService;

class FrequencyMealsAction
{
    public function handle($user, string $frequencyType, ?int $subcategoryId = null, int $limit = 50)
    {
        $service = app(FrequencyService::class);
        return $service->getFrequentlyOrderedMeals($user, $frequencyType, $limit, $subcategoryId);
    }
}
