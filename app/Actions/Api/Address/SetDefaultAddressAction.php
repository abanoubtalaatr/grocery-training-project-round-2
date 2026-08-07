<?php

namespace App\Actions\Api\Address;

use App\Models\Address;

class SetDefaultAddressAction
{
    public function execute(Address $address): Address
    {
        if (! $address->is_default) {
            $address->update(['is_default' => true]);
        }

        return $address->fresh();
    }
}
