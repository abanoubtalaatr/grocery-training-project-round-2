<?php

namespace App\Policies;

use App\Models\Address;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AddressPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    public function setDefault(User $user, Address $address)
    {
        return $user->id === $address->user_id;
    }
}
