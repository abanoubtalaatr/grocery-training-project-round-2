<?php

namespace App\Http\Controllers\Api;

use App\Actions\Review\DeleteReviewAction;
use App\Actions\Review\StoreReviewAction;
use App\Actions\Review\UpdateReviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Meal;
use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    use ApiResponse;

    /**
     * Get all reviews (with filters)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Review::query()
            ->with(['user', 'meal'])
            ->latest();

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

        $perPage = (int) $request->input('per_page', 15);
        $reviews = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => ReviewResource::collection($reviews),
            'meta'    => [
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'per_page'     => $reviews->perPage(),
                'total'        => $reviews->total(),
            ],
        ]);
    }

    /**
     * Store a new review
     */
    public function store(StoreReviewRequest $request, StoreReviewAction $action): JsonResponse
    {
        $review = $action->execute($request->user(), $request->validated());

        return $this->created(
            new ReviewResource($review->load(['user', 'meal'])),
            'Review submitted successfully. Waiting for admin approval.'
        );
    }

    /**
     * Get single review
     */
    public function show(Review $review): JsonResponse
    {
        return $this->success(new ReviewResource($review->load(['user', 'meal'])));
    }

    /**
     * Update review
     */
    public function update(UpdateReviewRequest $request, Review $review, UpdateReviewAction $action): JsonResponse
    {
        $this->authorize('update', $review);

        $updated = $action->execute($review, $request->validated());

        return $this->success(
            new ReviewResource($updated),
            'Review updated successfully'
        );
    }

    /**
     * Delete review
     */
    public function destroy(Review $review, DeleteReviewAction $action): JsonResponse
    {
        $this->authorize('delete', $review);

        $action->execute($review);

        return $this->success(null, 'Review deleted successfully');
    }

    /**
     * Get reviews for a specific meal
     */
    public function getMealReviews(Meal $meal, Request $request): JsonResponse
    {
        $reviews = Review::with('user')
            ->where('meal_id', $meal->id)
            ->approved()
            ->latest()
            ->paginate((int) $request->input('per_page', 10));

        $averageRating = Review::getAverageRating($meal->id);
        $totalReviews  = Review::getTotalReviews($meal->id);

        return response()->json([
            'success' => true,
            'meal'    => [
                'id'             => $meal->id,
                'name'           => $meal->name ?? $meal->title,
                'average_rating' => round($averageRating, 1),
                'total_reviews'  => $totalReviews,
            ],
            'data'    => ReviewResource::collection($reviews),
            'meta'    => [
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'per_page'     => $reviews->perPage(),
                'total'        => $reviews->total(),
            ],
        ]);
    }

    /**
     * Get user's reviews
     */
    public function getUserReviews(Request $request): JsonResponse
    {
        $userId = $request->user_id ?? Auth::id();

        $reviews = Review::with('meal')
            ->where('user_id', $userId)
            ->latest()
            ->paginate((int) $request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'data'    => ReviewResource::collection($reviews),
            'meta'    => [
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'per_page'     => $reviews->perPage(),
                'total'        => $reviews->total(),
            ],
        ]);
    }

    /**
     * Get review statistics for a meal
     */
    public function getMealReviewStats(Meal $meal): JsonResponse
    {
        $stats = Review::where('meal_id', $meal->id)
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

        return $this->success([
            'total_reviews'       => (int) ($stats->total_reviews ?? 0),
            'average_rating'      => round((float) ($stats->average_rating ?? 0), 1),
            'rating_distribution' => [
                'five_star'  => (int) ($stats->five_star ?? 0),
                'four_star'  => (int) ($stats->four_star ?? 0),
                'three_star' => (int) ($stats->three_star ?? 0),
                'two_star'   => (int) ($stats->two_star ?? 0),
                'one_star'   => (int) ($stats->one_star ?? 0),
            ],
        ]);
    }
}