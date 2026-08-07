<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Reviews\StoreReviewAction;
use App\Actions\Api\Reviews\UpdateReviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReviewRequest;
use App\Http\Requests\Api\UpdateReviewRequest;
use App\Http\Resources\Api\ReviewResource;
use App\Models\Meal;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::query()
            ->with(['user', 'meal'])
            ->latest()
            ->when($request->filled('meal_id'), fn ($query) => $query->where('meal_id', $request->integer('meal_id')))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('rating'), fn ($query) => $query->where('rating', $request->integer('rating')))
            ->when($request->boolean('approved_only', true), fn ($query) => $query->approved())
            ->when($request->filled('min_rating'), fn ($query) => $query->where('rating', '>=', $request->integer('min_rating')));

        $reviews = $query->paginate($request->integer('per_page', 15));

        return $this->paginatedResponse(
            $reviews,
            ReviewResource::collection($reviews),
            'Reviews retrieved successfully'
        );
    }

    public function store(StoreReviewRequest $request, StoreReviewAction $storeReview): JsonResponse
    {
        try {
            $review = $storeReview->execute($request->user(), $request->validated());
        } catch (InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->successResponse(
            new ReviewResource($review),
            'Review submitted successfully. Waiting for admin approval.',
            201
        );
    }

    public function show(Review $review): JsonResponse
    {
        return $this->successResponse(
            new ReviewResource($review->load(['user', 'meal'])),
            'Review retrieved successfully'
        );
    }

    public function update(UpdateReviewRequest $request, Review $review, UpdateReviewAction $updateReview): JsonResponse
    {
        $this->authorize('update', $review);

        $review = $updateReview->execute($review, $request->validated());

        return $this->successResponse(
            new ReviewResource($review),
            'Review updated successfully'
        );
    }

    public function destroy(Review $review): JsonResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return $this->successResponse(null, 'Review deleted successfully');
    }

    public function getMealReviews(Meal $meal, Request $request): JsonResponse
    {
        $reviews = $meal->reviews()
            ->with('user')
            ->approved()
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return $this->paginatedResponse(
            $reviews,
            ReviewResource::collection($reviews),
            'Meal reviews retrieved successfully',
            200,
            [
                'meal' => [
                    'id' => $meal->id,
                    'title' => $meal->title,
                    'average_rating' => round(Review::getAverageRating($meal->id), 1),
                    'total_reviews' => Review::getTotalReviews($meal->id),
                ],
            ]
        );
    }

    public function getUserReviews(Request $request): JsonResponse
    {
        $reviews = Review::query()
            ->with('meal')
            ->where('user_id', $request->integer('user_id') ?: $request->user()->id)
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return $this->paginatedResponse(
            $reviews,
            ReviewResource::collection($reviews),
            'User reviews retrieved successfully'
        );
    }

    public function getMealReviewStats(Meal $meal): JsonResponse
    {
        $stats = $meal->reviews()
            ->approved()
            ->selectRaw('
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
            ')
            ->first();

        return $this->successResponse([
            'total_reviews' => (int) $stats->total_reviews,
            'average_rating' => round($stats->average_rating ?? 0, 1),
            'rating_distribution' => [
                'five_star' => (int) $stats->five_star,
                'four_star' => (int) $stats->four_star,
                'three_star' => (int) $stats->three_star,
                'two_star' => (int) $stats->two_star,
                'one_star' => (int) $stats->one_star,
            ],
        ], 'Meal review statistics retrieved successfully');
    }
}
