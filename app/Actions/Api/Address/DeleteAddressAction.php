<?php

namespace App\Actions\Api\Address;

use App\Models\Address;

class DeleteAddressAction
{
    public function run(Address $address)
    {
        $address->delete();
    }
}
