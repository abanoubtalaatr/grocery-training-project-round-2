<?php

namespace App\Actions\Api\Offer;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Collection;

class GetFeaturedOffersAction
{
    public function run(): Collection
    {
        return Offer::featured()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
}
