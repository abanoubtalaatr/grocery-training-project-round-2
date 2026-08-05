<?php

namespace App\Action\Profile;

use App\Models\User;
use App\Models\Order;
use App\Models\Address;

class GetProfileAction
{
    public function handle(User $user): array
    {
        $user->loadMissing(['favorites.meal.category', 'favorites.meal.subcategory']);

        $addresses = $user->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $orders = Order::where('user_id', $user->id)
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();

        $notifications = $user->notifications()
            ->where(function ($q) {
                $q->where('data->type', 'order_confirmation')
                    ->orWhere('data->type', 'order_shipped')
                    ->orWhere('data->type', 'delivery_updates');
            })
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $wishlist = $user->favorites()->with('meal')->get();

        return [
            'user' => $user,
            'addresses' => $addresses,
            'orders' => $orders,
            'notifications' => $notifications,
            'wishlist' => $wishlist,
        ];
    }
}
