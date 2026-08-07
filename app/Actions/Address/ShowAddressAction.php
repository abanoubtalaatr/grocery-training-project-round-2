<?php

namespace App\Actions\Address;

use App\Models\User;
use App\Models\Address;

class ShowAddressAction
{
    public function execute(User $user, string $id): Address
    {
        return $user->addresses()->findOrFail($id);
    }
}