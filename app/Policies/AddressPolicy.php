<?php

namespace App\Policies;

use App\Models\Address;
use App\Models\User;

class AddressPolicy
{
    /**
     * Can the user list addresses?
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Can the user view this address?
     */
    public function view(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    /**
     * Can the user create an address?
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Can the user update this address?
     */
    public function update(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    /**
     * Can the user delete this address?
     */
    public function delete(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    /**
     * Can the user set this address as default?
     */
    public function setDefault(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }
}
