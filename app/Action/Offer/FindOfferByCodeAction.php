<?php

namespace App\Action\Offer;

use App\Models\Offer;

class FindOfferByCodeAction
{
    public function handle(string $code): Offer
    {
        return Offer::where('code', $code)->firstOrFail();
    }
}
