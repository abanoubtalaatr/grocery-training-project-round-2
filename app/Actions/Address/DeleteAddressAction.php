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

        if ($wasDefault) {
            $newDefault = $user->addresses()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }
    }
}
