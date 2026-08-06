<?php

namespace App\Http\Controllers\Api\Review;

use App\Actions\Api\Review\GetUserReviewsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ReviewResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetUserReviewsController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, GetUserReviewsAction $action): JsonResponse
    {
        $userId = $request->input('user_id', $request->user()->id);
        $perPage = (int) $request->input('per_page', 10);
        $reviews = $action->run($userId, $perPage);

        return $this->dataResponse([
            'reviews' => ReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }
}
