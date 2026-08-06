<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Review\DestroyReviewAction;
use App\Actions\Api\Review\GetReviewsAction;
use App\Actions\Api\Review\ShowReviewAction;
use App\Actions\Api\Review\StoreReviewAction;
use App\Actions\Api\Review\UpdateReviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReviewRequest;
use App\Http\Requests\Api\UpdateReviewRequest;
use App\Http\Resources\Api\ReviewResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LogicException;

class ReviewController extends Controller
{
    use ApiTrait;

    public function index(Request $request, GetReviewsAction $action): JsonResponse
    {
        $reviews = $action->run($request);

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

    public function store(StoreReviewRequest $request, StoreReviewAction $action): JsonResponse
    {
        try {
            $review = $action->run($request->user(), $request->validated());

            return $this->dataResponse(
                new ReviewResource($review),
                'Review submitted successfully. Waiting for admin approval.',
                201
            );
        } catch (LogicException $e) {
            return $this->errorResponse([], $e->getMessage(), 400);
        }
    }

    public function show(int $id, ShowReviewAction $action): JsonResponse
    {
        $review = $action->run($id);

        return $this->dataResponse(new ReviewResource($review));
    }

    public function update(UpdateReviewRequest $request, int $id, UpdateReviewAction $action): JsonResponse
    {
        $review = $action->run($request->user(), $id, $request->validated());

        return $this->dataResponse(
            new ReviewResource($review),
            'Review updated successfully'
        );
    }

    public function destroy(int $id, DestroyReviewAction $action): JsonResponse
    {
        $action->run(request()->user(), $id);

        return $this->successResponse('Review deleted successfully');
    }
}