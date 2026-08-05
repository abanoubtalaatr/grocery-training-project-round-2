<?php

namespace App\Action\Offer;

use App\Models\Offer;

class FeaturedOffersAction
{
    public function handle(int $limit = 5)
    {
        return Offer::featured()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
