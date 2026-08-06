<?php

namespace App\Actions\Api\Offer;

use App\Models\Offer;

class ShowOfferByCodeAction
{
    public function run(string $code): Offer
    {
        return Offer::where('code', $code)->firstOrFail();
    }
}
