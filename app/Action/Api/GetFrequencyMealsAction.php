<?php

namespace App\Action\Api;

use App\Services\FrequencyService;
use Illuminate\Contracts\Pagination\Paginator;

class GetFrequencyMealsAction
{
    protected $service;

    public function __construct(FrequencyService $service)
    {
        $this->service = $service;
    }

    public function execute($user, string $frequencyType, $limit = 50, $subcategoryId = null)
    {
        return $this->service->getFrequentlyOrderedMeals($user, $frequencyType, $limit, $subcategoryId);
    }
}
