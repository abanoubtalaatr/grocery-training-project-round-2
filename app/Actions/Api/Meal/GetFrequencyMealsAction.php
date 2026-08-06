<?php

namespace App\Actions\Api\Meal;

use App\Services\FrequencyService;
use Illuminate\Support\Collection;

class GetFrequencyMealsAction
{
    public function __construct(
        private readonly FrequencyService $frequencyService
    ) {}

    public function run($user, string $frequencyType, ?int $subcategoryId): Collection
    {
        return $this->frequencyService->getFrequentlyOrderedMeals($user, $frequencyType, 50, $subcategoryId);
    }
}
