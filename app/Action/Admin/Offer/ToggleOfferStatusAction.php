<?php

namespace App\Action\Admin\Offer;

use App\Models\Offer;

class ToggleOfferStatusAction
{
    public function execute(Offer $offer): void
    {
        $offer->update([
            'is_active' => !$offer->is_active,
        ]);
    }
}
