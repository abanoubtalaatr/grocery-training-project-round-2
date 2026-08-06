<?php

namespace App\Http\Controllers\Api\Review;

use App\Actions\Api\Review\GetMealReviewsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ReviewResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetMealReviewsController extends Controller
{
    use ApiTrait;

    public function __invoke(int $mealId, Request $request, GetMealReviewsAction $action): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $result = $action->run($mealId, $perPage);

        return $this->dataResponse([
            'meal' => $result['meal'],
            'reviews' => ReviewResource::collection($result['reviews']),
            'meta' => [
                'current_page' => $result['reviews']->currentPage(),
                'last_page' => $result['reviews']->lastPage(),
                'per_page' => $result['reviews']->perPage(),
                'total' => $result['reviews']->total(),
            ],
        ]);
    }
}
