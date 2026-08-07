<?php

namespace App\Action\Admin\Review;

use App\Models\Review;

class GetReviewStatsAction
{
    public function execute(): array
    {
        $total = Review::count();
        $approved = Review::where('is_approved', true)->count();
        $pending = Review::where('is_approved', false)->count();

        return [
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'average_rating' => round(Review::avg('rating') ?? 0, 1),
            'by_rating' => [
                '5' => Review::where('rating', 5)->count(),
                '4' => Review::where('rating', 4)->count(),
                '3' => Review::where('rating', 3)->count(),
                '2' => Review::where('rating', 2)->count(),
                '1' => Review::where('rating', 1)->count(),
            ],
        ];
    }
}
