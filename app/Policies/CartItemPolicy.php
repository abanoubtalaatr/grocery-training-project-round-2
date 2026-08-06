<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Models\User;

class CartItemPolicy
{
    public function view(User $user, CartItem $cartItem): bool
    {
        return $user->id === $cartItem->cart->user_id;
    }

    public function update(User $user, CartItem $cartItem): bool
    {
        return $user->id === $cartItem->cart->user_id;
    }

    public function delete(User $user, CartItem $cartItem): bool
    {
        return $user->id === $cartItem->cart->user_id;
    }
}
