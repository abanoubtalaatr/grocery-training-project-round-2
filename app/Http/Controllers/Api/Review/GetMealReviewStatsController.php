<?php

namespace App\Http\Controllers\Api\Review;

use App\Actions\Api\Review\GetMealReviewStatsAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class GetMealReviewStatsController extends Controller
{
    use ApiTrait;

    public function __invoke(int $mealId, GetMealReviewStatsAction $action): JsonResponse
    {
        $stats = $action->run($mealId);

        return $this->dataResponse($stats);
    }
}
