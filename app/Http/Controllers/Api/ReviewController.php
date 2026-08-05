<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Action\Review\CreateReviewAction;
use App\Action\Review\ListReviewsAction;
use App\Action\Review\DeleteReviewAction;
use App\Http\Requests\Api\CreateReviewRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request, ListReviewsAction $action, $mealId): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $paginator = $action->handle((int) $mealId, $perPage);

        $items = $paginator->getCollection()->map(function ($review) {
            return [
                'id' => $review->id,
                'user' => $review->relationLoaded('user') && $review->user ? ['id' => $review->user->id, 'name' => $review->user->full_name ?? $review->user->username ?? 'User'] : null,
                'rating' => (int) $review->rating,
                'comment' => $review->comment,
                'images' => $review->images ?? [],
                'created_at' => $review->created_at,
            ];
        });

        return response()->json(['success' => true, 'message' => 'Reviews retrieved successfully', 'data' => $items, 'pagination' => ['current_page' => $paginator->currentPage(), 'last_page' => $paginator->lastPage(), 'per_page' => $paginator->perPage(), 'total' => $paginator->total()]]);
    }

    public function store(CreateReviewRequest $request, CreateReviewAction $action): JsonResponse
    {
        $user = $request->user();
        $review = $action->handle($user, $request->validated());

        return response()->json(['success' => true, 'message' => 'Review submitted successfully', 'data' => $review]);
    }

    public function destroy(Request $request, DeleteReviewAction $action, $id): JsonResponse
    {
        $user = $request->user();
        try {
            $action->handle($user, (int) $id);
            return response()->json(['success' => true, 'message' => 'Review deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }
}
