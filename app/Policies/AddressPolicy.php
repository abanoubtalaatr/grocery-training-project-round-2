<?php

namespace App\Policies;

use App\Models\Address;
use App\Models\User;

class AddressPolicy
{
    public function view(User $user, Address $address): bool
    {
        return (string) $user->id === (string) $address->user_id;
    }

    public function update(User $user, Address $address): bool
    {
        return (string) $user->id === (string) $address->user_id;
    }

    public function delete(User $user, Address $address): bool
    {
        return (string) $user->id === (string) $address->user_id;
    }
}
