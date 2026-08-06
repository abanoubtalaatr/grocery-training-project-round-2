<?php

namespace App\Action\Api;

use App\Services\FrequencyService;

class GetFrequentlyOrderedMealsAction
{
    public function execute($user, string $frequencyType, ?int $subcategoryId = null)
    {
        $service = app(FrequencyService::class);

        return $service->getFrequentlyOrderedMeals($user, $frequencyType, 50, $subcategoryId);
    }
}