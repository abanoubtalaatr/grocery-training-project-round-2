<?php

namespace App\Action\Admin\Offer;

use App\Models\Offer;

class UpdateOfferAction
{
    public function execute(Offer $offer, array $data): void
    {
        $offer->update($data);
    }
}
    