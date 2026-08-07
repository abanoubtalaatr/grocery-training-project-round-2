<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\CreateReviewAction;
use App\Action\Api\DeleteReviewAction;
use App\Action\Api\GetMealReviewStatsAction;
use App\Action\Api\GetMealReviewsAction;
use App\Action\Api\GetUserReviewsAction;
use App\Action\Api\UpdateReviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReviewRequest;
use App\Http\Requests\Api\UpdateReviewRequest;
use App\Http\Resources\Api\MealReviewStatsResource;
use App\Http\Resources\Api\ReviewResource;
use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['user', 'meal'])->latest();

        if ($request->has('meal_id')) {
            $query->where('meal_id', $request->meal_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->boolean('approved_only', true)) {
            $query->approved();
        }

        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }

        $perPage = $request->input('per_page', 15);
        $reviews = $query->paginate($perPage);

        return $this->success(ReviewResource::collection($reviews),'Reviews retrieved successfully');
    }

    public function store(StoreReviewRequest $request, CreateReviewAction $action): JsonResponse
    {
        $review = $action->execute($request->user(), $request->validated());
        $review->load(['user', 'meal']);

        return $this->success(new ReviewResource($review),'Review submitted successfully. Waiting for admin approval.',20);
    }

    public function show(string $id): JsonResponse
    {
        $review = Review::with(['user', 'meal'])->findOrFail($id);

        return $this->success( new ReviewResource($review),'Review retrieved successfully');
    }

    public function update(UpdateReviewRequest $request, string $id, UpdateReviewAction $action): JsonResponse
    {
        $review = $action->execute($request->user(), $id, $request->validated());
        $review->load(['user', 'meal']);

        return $this->success(new ReviewResource($review),'Review updated successfully');
    }

    public function destroy(Request $request, string $id, DeleteReviewAction $action): JsonResponse
    {
        $action->execute($request->user(), $id);

        return $this->success(null, 'Review deleted successfully');
    }

    public function getMealReviews(string $mealId, Request $request, GetMealReviewsAction $action): JsonResponse
    {
        $result = $action->execute($mealId, (int) $request->input('per_page', 10));

        return $this->success(
            [
                'meal' => $result['meal'],
                'reviews' => ReviewResource::collection($result['reviews']),
                'pagination' => $result['pagination'],
            ],
            'Meal reviews retrieved successfully'
        );
    }

    public function getUserReviews(Request $request, GetUserReviewsAction $action): JsonResponse
    {
        $userId = $request->user_id ?? $request->user()->id;
        $result = $action->execute($userId, (int) $request->input('per_page', 10));

        return $this->success(
            [
                'reviews' => ReviewResource::collection($result['reviews']),
                'pagination' => $result['pagination'],
            ],
            'User reviews retrieved successfully'
        );
    }

    public function getMealReviewStats(string $mealId, GetMealReviewStatsAction $action): JsonResponse
    {
        $stats = $action->execute($mealId);

        return $this->success( new MealReviewStatsResource($stats),'Review statistics retrieved successfully');
    }
}
