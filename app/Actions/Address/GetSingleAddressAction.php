<?php

namespace App\Actions\Address;

use App\Models\Address;
use App\Models\User;

class GetSingleAddressAction
{
    public function execute(User $user, string $id): Address
    {
        return $user->addresses()->findOrFail($id);
    }
}
