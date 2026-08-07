<?php

namespace App\Actions\Api\Addresses;

use App\Models\Address;
use Illuminate\Support\Facades\DB;

class SetDefaultAddressAction
{
    public function execute(Address $address): Address
    {
        if ($address->is_default) {
            return $address;
        }

        return DB::transaction(function () use ($address) {
            $address->user->addresses()
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);

            $address->update(['is_default' => true]);

            return $address->fresh();
        });
    }
}
