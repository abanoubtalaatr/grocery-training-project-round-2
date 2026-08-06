<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\Meal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Action\Api\ListReviewsAction;
use App\Action\Api\CreateReviewAction;
use App\Action\Api\ShowReviewAction;
use App\Action\Api\UpdateReviewAction;
use App\Action\Api\DeleteReviewAction;
use App\Action\Api\GetMealReviewsAction;
use App\Action\Api\GetUserReviewsAction;
use App\Action\Api\GetMealReviewStatsAction;

class ReviewController extends Controller
{
    /**
     * Get all reviews (with filters)
     */
    public function index(Request $request, ListReviewsAction $action): JsonResponse
    {
        $reviews = $action->execute($request);

        return response()->json([
            'success' => true,
            'data' => ReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ]
        ]);
    }

    /**
     * Store a new review
     */
    public function store(StoreReviewRequest $request, CreateReviewAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to submit review',
            ], $result['status'] ?? 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully. Waiting for admin approval.',
            'data' => new ReviewResource($result['review'])
        ], 201);
    }

    /**
     * Get single review
     */
    public function show($id, ShowReviewAction $action): JsonResponse
    {
        $review = $action->execute($id);

        return response()->json([
            'success' => true,
            'data' => new ReviewResource($review)
        ]);
    }

    /**
     * Update review (only by owner or admin)
     */
    public function update(UpdateReviewRequest $request, $id, UpdateReviewAction $action): JsonResponse
    {
        $result = $action->execute($id, $request->validated());

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Unauthorized',
            ], $result['status'] ?? 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => new ReviewResource($result['review'])
        ]);
    }

    /**
     * Delete review (only by owner or admin)
     */
    public function destroy($id, DeleteReviewAction $action): JsonResponse
    {
        $result = $action->execute($id);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Unauthorized',
            ], $result['status'] ?? 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully'
        ]);
    }

    /**
     * Get reviews for a specific meal
     */
    public function getMealReviews($mealId, Request $request, GetMealReviewsAction $action): JsonResponse
    {
        $result = $action->execute($mealId, $request);

        return response()->json([
            'success' => true,
            'meal' => $result['meal'],
            'data' => ReviewResource::collection($result['reviews']),
            'meta' => [
                'current_page' => $result['reviews']->currentPage(),
                'last_page' => $result['reviews']->lastPage(),
                'per_page' => $result['reviews']->perPage(),
                'total' => $result['reviews']->total(),
            ]
        ]);
    }

    /**
     * Get user's reviews
     */
    public function getUserReviews(Request $request, GetUserReviewsAction $action): JsonResponse
    {
        $reviews = $action->execute($request);

        return response()->json([
            'success' => true,
            'data' => ReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ]
        ]);
    }

    /**
     * Get review statistics for a meal
     */
    public function getMealReviewStats($mealId, GetMealReviewStatsAction $action): JsonResponse
    {
        $data = $action->execute($mealId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}