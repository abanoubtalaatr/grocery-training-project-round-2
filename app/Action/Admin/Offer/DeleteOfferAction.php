<?php

namespace App\Action\Admin\Offer;

use App\Models\Offer;

class DeleteOfferAction
{
    public function execute(Offer $offer): void
    {
        $offer->delete();
    }
}
