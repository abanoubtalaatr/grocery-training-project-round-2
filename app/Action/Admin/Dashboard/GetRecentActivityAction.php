<?php

namespace App\Action\Admin\Dashboard;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class GetRecentActivityAction
{
    public function execute(int $limit = 10): array
    {
        // Recent orders
        $recentOrders = Order::with('user:id,username,firstname,lastname')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($order) => [
                'type' => 'order',
                'id' => $order->id,
                'order_number' => $order->order_number,
                'user' => $order->user?->full_name ?? 'N/A',
                'total' => (float) $order->total,
                'status' => $order->status,
                'created_at' => $order->created_at?->diffForHumans(),
            ]);

        // Recent users
        $recentUsers = User::latest()
            ->take(5)
            ->get()
            ->map(fn ($user) => [
                'type' => 'user',
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'created_at' => $user->created_at?->diffForHumans(),
            ]);

        // Merge and sort by created_at
        $activities = $recentOrders->concat($recentUsers)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values()
            ->toArray();

        return $activities;
    }
}
