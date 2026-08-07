<?php

namespace App\Actions\Offer;

use App\Models\Offer;

class FeaturedOffersAction
{
    public function execute()
    {
        return Offer::featured()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }
}