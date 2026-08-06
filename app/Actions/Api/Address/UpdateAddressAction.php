<?php

namespace App\Actions\Api\Address;

use App\Models\Address;
use Illuminate\Http\Request;

class UpdateAddressAction
{
    public function run(Request $request, Address $address)
    {
           $updateData = $request->validated();
            // Normalize phone on update: if phone already starts with country code, store only national part
            if (isset($updateData['phone'], $updateData['country_code']) && $updateData['country_code'] !== '' && str_starts_with(trim($updateData['phone']), $updateData['country_code'])) {
                $updateData['phone'] = substr(trim($updateData['phone']), strlen($updateData['country_code']));
            }
            $address->fill($updateData)->save();
        return $address;
    }
}
