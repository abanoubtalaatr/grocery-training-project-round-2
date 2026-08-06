<?php

namespace App\Actions\Address;

use App\Models\Address;
use App\Models\User;

class SetDefaultAddressAction
{
    public function execute(User $user, Address $address): Address
    {
        if ($address->is_default) {
            return $address;
        }

        $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return $address->fresh();
    }
}
