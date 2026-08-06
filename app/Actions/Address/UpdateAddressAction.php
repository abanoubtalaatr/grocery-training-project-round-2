<?php

namespace App\Actions\Address;

use App\Models\Address;
use App\Models\User;

class UpdateAddressAction
{
    public function execute(Address $address, array $updateData): Address
    {
        // Normalize phone on update: if phone already starts with country code, store only national part
        if (isset($updateData['phone'], $updateData['country_code']) && $updateData['country_code'] !== '' && str_starts_with(trim($updateData['phone']), $updateData['country_code'])) {
            $updateData['phone'] = substr(trim($updateData['phone']), strlen($updateData['country_code']));
        }
        
        $address->update($updateData);
        
        return $address->fresh();
    }
}
