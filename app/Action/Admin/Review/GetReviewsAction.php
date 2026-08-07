<?php

namespace App\Action\Admin\Review;

use App\Models\Review;

class GetReviewsAction
{
    public function execute(array $filters): array
    {
        $query = Review::with(['user:id,username,firstname,lastname', 'meal:id,title,slug']);

        // Filter by approved status
        if (isset($filters['is_approved'])) {
            $query->where('is_approved', filter_var($filters['is_approved'], FILTER_VALIDATE_BOOLEAN));
        }

        // Filter by rating
        if (!empty($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        // Filter by meal
        if (!empty($filters['meal_id'])) {
            $query->where('meal_id', $filters['meal_id']);
        }

        // Filter by user
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('meal', fn ($mq) => $mq->where('title', 'like', "%{$search}%"))
                    ->orWhereHas('user', fn ($uq) => $uq->where('username', 'like', "%{$search}%"));
            });
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = strtolower($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSortFields = ['created_at', 'rating'];
        
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        $perPage = min(max((int) ($filters['per_page'] ?? 15), 1), 100);
        $reviews = $query->paginate($perPage);

        return [
            'reviews' => $reviews,
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'from' => $reviews->firstItem(),
                'to' => $reviews->lastItem(),
            ],
        ];
    }
}
