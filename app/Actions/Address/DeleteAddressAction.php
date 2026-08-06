<?php

namespace App\Actions\Address;

use App\Models\Address;
use App\Models\User;

class DeleteAddressAction
{
    public function execute(User $user, Address $address): void
    {
        $wasDefault = $address->is_default;
        $address->delete();

        // If deleted address was default, set another address as default
        if ($wasDefault) {
            $newDefault = $user->addresses()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }
    }
}
