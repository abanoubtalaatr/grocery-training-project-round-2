<?php

namespace App\Action\Profile;

use App\Models\Address;
use App\Models\Order;

class ProfilePresenter
{
    public function present(array $payload): array
    {
        $user = $payload['user'];
        $addresses = $payload['addresses'];
        $orders = $payload['orders'];
        $notifications = $payload['notifications'];
        $wishlist = $payload['wishlist'];

        $addressesFormatted = $addresses->map(fn (Address $a) => [
            'id' => $a->id,
            'label' => $a->label,
            'full_name' => $a->full_name,
            'phone' => $a->phone,
            'country_code' => $a->country_code,
            'street_address' => $a->street_address,
            'building_number' => $a->building_number,
            'floor' => $a->floor,
            'apartment' => $a->apartment,
            'landmark' => $a->landmark,
            'city' => $a->city,
            'state' => $a->state,
            'postal_code' => $a->postal_code,
            'country' => $a->country,
            'full_address' => $a->full_address ?? null,
            'is_default' => $a->is_default,
            'created_at' => $a->created_at,
            'updated_at' => $a->updated_at,
        ])->values();

        $orderHistory = $orders->map(fn (Order $o) => [
            'id' => $o->id,
            'order_number' => $o->order_number,
            'status' => $o->status,
            'status_description' => $o->status_description,
            'total' => (float) $o->total,
            'placed_at' => $o->placed_at?->toIso8601String(),
            'created_at' => $o->created_at?->toIso8601String(),
            'item_count' => $o->items->count(),
        ])->values();

        $inProgressWithTracking = $orders->whereNotIn('status', ['cancelled', 'delivered'])
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'status' => $o->status,
                'status_description' => $o->status_description,
                'tracking' => [],
                'total' => (float) $o->total,
                'placed_at' => $o->placed_at?->toIso8601String(),
                'estimated_delivery_time' => $o->estimated_delivery_time?->toIso8601String(),
                'address' => $o->address ? $this->formatAddress($o->address) : null,
                'items' => $o->items->map(fn ($item) => [
                    'id' => $item->id,
                    'meal' => ['id' => $item->meal->id, 'title' => $item->meal->title, 'image_url' => $item->meal->image_url],
                    'quantity' => $item->quantity,
                    'subtotal' => (float) $item->subtotal,
                ])->values(),
            ])->values();

        $notificationsFormatted = $notifications->map(fn ($n) => [
            'id' => $n->id,
            'type' => $n->data['type'] ?? 'order',
            'title' => $n->data['title'] ?? 'Order update',
            'body' => $n->data['body'] ?? '',
            'is_read' => $n->read_at !== null,
            'read_at' => $n->read_at?->toIso8601String(),
            'created_at' => $n->created_at?->toIso8601String(),
            'action_url' => $n->data['action_url'] ?? null,
        ])->values();

        $wishlistFormatted = $wishlist->map(fn ($f) => [
            'id' => $f->meal->id,
            'title' => $f->meal->title,
            'slug' => $f->meal->slug,
            'image_url' => $f->meal->image_url,
            ...$f->meal->getApiPriceAttributes(),
            'has_offer' => $f->meal->hasOffer(),
            'category' => $f->meal->category ? ['id' => $f->meal->category->id, 'name' => $f->meal->category->name] : null,
            'is_favorited' => true,
            'favorited_at' => $f->created_at?->toIso8601String(),
        ])->values();

        return [
            'me' => [
                'id' => $user->id,
                'profile_picture' => $user->profile_image_url,
                'name' => $user->full_name,
                'username' => $user->username,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'gender' => $user->gender,
                'birthday' => $user->birthday?->format('Y-m-d'),
                'email' => $user->email,
                'phone' => $user->phone,
                'country_code' => $user->country_code,
                'email_verified' => $user->email_verified,
                'phone_verified' => $user->phone_verified,
                'preferred_languages' => $user->preferred_languages ?? [],
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'addresses' => $addressesFormatted,
            'order_history' => ['orders' => $orderHistory, 'ordered_at' => $orderHistory->map(fn ($o) => $o['placed_at'] ?? $o['created_at'])->values()],
            'in_progress_orders' => $inProgressWithTracking,
            'order_notifications' => $notificationsFormatted,
            'settings' => ['privacy_and_security' => ['active_sessions' => [], 'change_password' => ['available' => true], 'change_username' => ['available' => true]]],
            'wishlist' => $wishlistFormatted,
        ];
    }

    private function formatAddress($address): array
    {
        return [
            'id' => $address->id,
            'label' => $address->label,
            'full_name' => $address->full_name,
            'phone' => $address->phone,
            'country_code' => $address->country_code,
            'street_address' => $address->street_address,
            'building_number' => $address->building_number,
            'floor' => $address->floor,
            'apartment' => $address->apartment,
            'landmark' => $address->landmark,
            'city' => $address->city,
            'state' => $address->state,
            'postal_code' => $address->postal_code,
            'country' => $address->country,
            'full_address' => $address->full_address ?? null,
            'is_default' => $address->is_default,
            'created_at' => $address->created_at,
            'updated_at' => $address->updated_at,
        ];
    }
}
