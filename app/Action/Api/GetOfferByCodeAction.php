<?php

namespace App\Action\Api;

use App\Models\Offer;

class GetOfferByCodeAction
{
    public function execute(string $code): Offer
    {
        return Offer::where('code', $code)->firstOrFail();
    }
}