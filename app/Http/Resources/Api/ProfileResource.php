<?php

namespace App\Http\Resources\Api;

use App\Models\Address;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $addresses = $this->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (Address $a) => $this->formatAddress($a));

        $allOrders = Order::where('user_id', $this->id)
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();

        $orderHistory = $allOrders->map(fn (Order $o) => $this->formatOrderSummary($o));
        $inProgressOrders = $allOrders->whereNotIn('status', ['cancelled', 'delivered']);
        $inProgressWithTracking = $inProgressOrders->map(fn (Order $o) => $this->formatOrderWithTracking($o))->values();

        $orderNotifications = $this->notifications()
            ->where(function ($q) {
                $q->where('data->type', 'order_confirmation')
                    ->orWhere('data->type', 'order_shipped')
                    ->orWhere('data->type', 'delivery_updates');
            })
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(fn ($n) => $this->formatNotification($n));

        $sessions = $this->formatSessions();
        $wishlist = $this->favorites->map(fn ($f) => $this->formatWishlistItem($f))->values();

        return [
            'me' => [
                'id' => $this->id,
                'profile_picture' => $this->profile_image_url,
                'name' => $this->full_name,
                'username' => $this->username,
                'firstname' => $this->firstname,
                'lastname' => $this->lastname,
                'gender' => $this->gender,
                'birthday' => $this->birthday?->format('Y-m-d'),
                'email' => $this->email,
                'phone' => $this->phone,
                'country_code' => $this->country_code,
                'email_verified' => $this->email_verified,
                'phone_verified' => $this->phone_verified,
                'preferred_languages' => $this->preferred_languages ?? [],
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'addresses' => $addresses,
            'order_history' => [
                'orders' => $orderHistory,
                'ordered_at' => $orderHistory->map(fn ($o) => $o['placed_at'] ?? $o['created_at'])->values(),
            ],
            'in_progress_orders' => $inProgressWithTracking,
            'order_notifications' => $orderNotifications,
            'settings' => [
                'privacy_and_security' => [
                    'active_sessions' => $sessions,
                    'change_password' => ['available' => true],
                    'change_username' => ['available' => true],
                ],
            ],
            'wishlist' => $wishlist,
        ];
    }

    private function formatAddress(Address $address): array
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

    private function formatOrderSummary(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_description' => $order->status_description,
            'total' => (float) $order->total,
            'placed_at' => $order->placed_at?->toIso8601String(),
            'created_at' => $order->created_at?->toIso8601String(),
            'item_count' => $order->items->count(),
        ];
    }

    private function formatOrderWithTracking(Order $order): array
    {
        $trackingStage = match ($order->status) {
            'shipping' => 'arriving',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
            default => 'processing',
        };

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_description' => $order->status_description,
            'tracking' => [
                'stage' => $trackingStage,
                'stage_label' => match ($trackingStage) {
                    'arriving' => 'Arriving',
                    'out_for_delivery' => 'Out for delivery',
                    'delivered' => 'Delivered',
                    default => 'Processing',
                },
                'positions' => [
                    ['stage' => 'arriving', 'label' => 'Arriving', 'completed' => in_array($order->status, ['shipping', 'out_for_delivery', 'delivered']), 'timestamp' => $order->shipping_at?->toIso8601String()],
                    ['stage' => 'out_for_delivery', 'label' => 'Out for delivery', 'completed' => in_array($order->status, ['out_for_delivery', 'delivered']), 'timestamp' => $order->out_for_delivery_at?->toIso8601String()],
                    ['stage' => 'delivered', 'label' => 'Delivered', 'completed' => $order->status === 'delivered', 'timestamp' => $order->delivered_at?->toIso8601String()],
                ],
            ],
            'total' => (float) $order->total,
            'placed_at' => $order->placed_at?->toIso8601String(),
            'estimated_delivery_time' => $order->estimated_delivery_time?->toIso8601String(),
            'address' => $order->address ? $this->formatAddress($order->address) : null,
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'meal' => ['id' => $item->meal->id, 'title' => $item->meal->title, 'image_url' => $item->meal->image_url],
                'quantity' => $item->quantity,
                'subtotal' => (float) $item->subtotal,
            ])->values(),
        ];
    }

    private function formatNotification($notification): array
    {
        $data = $notification->data ?? [];

        return [
            'id' => $notification->id,
            'type' => $data['type'] ?? 'order',
            'title' => $data['title'] ?? 'Order update',
            'body' => $data['body'] ?? '',
            'is_read' => $notification->read_at !== null,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
            'action_url' => $data['action_url'] ?? null,
        ];
    }

    private function formatSessions(): array
    {
        $currentTokenId = request()->user()?->currentAccessToken()?->id;

        return $this->tokens()->get()->map(function ($token) use ($currentTokenId) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'is_current' => (string) $token->id === (string) $currentTokenId,
            ];
        })->all();
    }

    private function formatWishlistItem($favorite): array
    {
        $meal = $favorite->meal;

        return [
            'id' => $meal->id,
            'title' => $meal->title,
            'slug' => $meal->slug,
            'image_url' => $meal->image_url,
            ...$meal->getApiPriceAttributes(),
            'has_offer' => $meal->hasOffer(),
            'category' => $meal->category ? ['id' => $meal->category->id, 'name' => $meal->category->name] : null,
            'is_favorited' => true,
            'favorited_at' => $favorite->created_at?->toIso8601String(),
        ];
    }
}
