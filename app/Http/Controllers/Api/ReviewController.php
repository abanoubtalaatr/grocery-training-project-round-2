<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Review\StoreReviewAction;
use App\Actions\Api\Review\UpdateReviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReviewRequest;
use App\Http\Requests\Api\UpdateReviewRequest;
use App\Http\Resources\Api\ReviewResource;
use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;

    /**
     * Get all reviews with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Review::query()
            ->with(['user', 'meal'])
            ->latest();

        // Apply filters
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

        return $this->paginated($reviews, 'Reviews retrieved successfully');
    }

    /**
     * Get single review
     */
    public function show(Review $review): JsonResponse
    {
        $review->load(['user', 'meal']);

        return $this->success(
            new ReviewResource($review),
            'Review retrieved successfully'
        );
    }

    /**
     * Create new review
     */
    public function store(StoreReviewRequest $request, StoreReviewAction $action): JsonResponse
    {
        $this->authorize('create', Review::class);

        // Check if user has already reviewed this meal
        if (Review::where('user_id', $request->user()->id)
            ->where('meal_id', $request->meal_id)
            ->exists()) {
            return $this->error(
                'You have already reviewed this meal',
                400
            );
        }

        $review = $action->execute($request);

        return $this->success(
            new ReviewResource($review),
            'Review submitted successfully. Waiting for admin approval.',
            201
        );
    }

    /**
     * Update review
     */
    public function update(UpdateReviewRequest $request, Review $review, UpdateReviewAction $action): JsonResponse
    {
        $this->authorize('update', $review);

        $review = $action->execute($review, $request);

        return $this->success(
            new ReviewResource($review),
            'Review updated successfully'
        );
    }

    /**
     * Delete review
     */
    public function destroy(Review $review): JsonResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return $this->success(null, 'Review deleted successfully');
    }
}
