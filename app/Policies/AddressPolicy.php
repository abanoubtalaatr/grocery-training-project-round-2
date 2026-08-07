<?php

namespace App\Policies;

use App\Models\Address;
use App\Models\User;

class AddressPolicy
{
    public function view(User $user, Address $address): bool
    {
        return $address->user_id === $user->id || (bool) $user->is_admin;
    }

    public function update(User $user, Address $address): bool
    {
        return $this->view($user, $address);
    }

    public function delete(User $user, Address $address): bool
    {
        return $this->view($user, $address);
    }
}
