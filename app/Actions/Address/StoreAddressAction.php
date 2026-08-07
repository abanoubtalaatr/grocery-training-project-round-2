<?php

namespace App\Actions\Address;

use App\Models\Address;
use App\Models\User;

class StoreAddressAction
{
    public function execute(User $user, array $data): Address
    {
        $isFirstAddress = $user->addresses()->count() === 0;

        $data['is_default'] = ! empty($data['is_default']) || $isFirstAddress;

        // Normalize phone: if phone already starts with country code, store only the national part
        $phone = trim($data['phone'] ?? '');
        $code  = trim($data['country_code'] ?? '');
        if ($code !== '' && str_starts_with($phone, $code)) {
            $data['phone'] = substr($phone, strlen($code));
        }

        return $user->addresses()->create($data);
    }
}
