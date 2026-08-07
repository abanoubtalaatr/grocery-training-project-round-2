<?php

namespace App\Action\Admin\Offer;

use App\Models\Offer;

class CreateOfferAction
{
    public function execute(array $data): Offer
    {
        return Offer::create($data);
    }
}
