<?php

namespace App\Actions\Offer;

use App\Models\Offer;

class ShowOfferByCodeAction
{
    public function execute(string $code)
    {
        return Offer::where('code', $code)->firstOrFail();
    }
}