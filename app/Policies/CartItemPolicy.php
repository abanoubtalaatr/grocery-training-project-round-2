<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Models\User;

class CartItemPolicy
{
    public function update(User $user, CartItem $cartItem): bool
    {
        return $cartItem->cart?->user_id === $user->id || (bool) $user->is_admin;
    }

    public function delete(User $user, CartItem $cartItem): bool
    {
        return $this->update($user, $cartItem);
    }
}
