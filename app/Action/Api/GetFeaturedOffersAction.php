<?php

namespace App\Action\Api;

use App\Models\Offer;

class GetFeaturedOffersAction
{
    public function execute()
    {
        return Offer::featured()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
}