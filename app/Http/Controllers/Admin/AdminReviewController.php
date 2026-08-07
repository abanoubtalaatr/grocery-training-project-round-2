<?php

namespace App\Http\Controllers\Admin;

use App\Action\Admin\Review\ApproveReviewAction;
use App\Action\Admin\Review\DeleteReviewAction;
use App\Action\Admin\Review\GetReviewStatsAction;
use App\Action\Admin\Review\GetReviewsAction;
use App\Action\Admin\Review\RejectReviewAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ReviewResource;
use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    use ApiResponse;

    public function index(Request $request, GetReviewsAction $action): JsonResponse
    {
        $result = $action->execute($request->all());

        return $this->success(
            [
                'reviews' => ReviewResource::collection($result['reviews']),
                'pagination' => $result['pagination'],
            ],
            'Reviews retrieved successfully'
        );
    }

    public function show(Review $review): JsonResponse
    {
        $review->load(['user:id,username,firstname,lastname', 'meal:id,title,slug']);

        return $this->success(new ReviewResource($review),'Review retrieved successfully');
    }

    public function approve(Review $review, ApproveReviewAction $action): JsonResponse
    {
        $action->execute($review);

        return $this->success(new ReviewResource($review->fresh()),'Review approved successfully');
    }

    public function reject(Review $review, RejectReviewAction $action): JsonResponse
    {
        $action->execute($review);

        return $this->success(new ReviewResource($review->fresh()), 'Review rejected successfully' );
    }

    public function destroy(Review $review, DeleteReviewAction $action): JsonResponse
    {
        $action->execute($review);

        return $this->success(null, 'Review deleted successfully');
    }

    public function stats(GetReviewStatsAction $action): JsonResponse
    {
        $stats = $action->execute();

        return $this->success($stats, 'Review statistics retrieved successfully');
    }
}
