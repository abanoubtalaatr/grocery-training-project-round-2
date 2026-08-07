<?php

namespace App\Actions\Address;

use App\Models\Address;

class UpdateAddressAction
{
    public function execute(Address $address, array $updateData): Address
    {
        if (isset($updateData['phone'], $updateData['country_code']) && $updateData['country_code'] !== '' && str_starts_with(trim($updateData['phone']), $updateData['country_code'])) {
            $updateData['phone'] = substr(trim($updateData['phone']), strlen($updateData['country_code']));
        }

        $address->fill($updateData)->save();

        return $address->fresh();
    }
}
