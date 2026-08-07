<?php

namespace App\Actions\Admin;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Meal;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;

class GetAdminDashboardDataAction
{
    public function execute(): array
    {
        // Basic stats
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'total_meals' => Meal::count(),
            'available_meals' => Meal::available()->count(),
            'out_of_stock_meals' => Meal::outOfStock()->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::whereIn('status', ['placed', 'processing', 'shipping', 'out_for_delivery'])->count(),
            'total_revenue' => (float) Order::where('status', '!=', 'cancelled')->sum('total'),
            'pending_reviews' => Review::pending()->count(),
            'new_messages' => ContactMessage::new()->count(),
            'total_categories' => Category::count(),
            'total_reviews' => Review::count(),
            'total_messages' => ContactMessage::count(),
        ];

        // Recent lists
        $recentOrders = Order::with(['user', 'items.meal'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer' => $order->user?->username ?? 'Unknown',
                'status' => $order->status_description,
                'total' => (float) $order->total,
                'created_at' => $order->created_at?->format('M d, Y'),
                'items_count' => $order->items->sum('quantity'),
            ])
            ->toArray();

        $recentReviews = Review::with(['user', 'meal'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Review $r) => [
                'id' => $r->id,
                'user' => $r->user?->username ?? 'Unknown',
                'meal' => $r->meal?->title ?? 'Unknown',
                'rating' => (int) $r->rating,
                'comment' => is_string($r->comment) ? mb_strimwidth($r->comment, 0, 80, '...') : null,
                'is_approved' => (bool) $r->is_approved,
                'created_at' => $r->created_at?->format('M d, Y'),
            ])
            ->toArray();

        $recentMessages = ContactMessage::latest()
            ->take(5)
            ->get()
            ->map(fn (ContactMessage $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'email' => $m->email,
                'subject' => $m->subject,
                'status' => $m->status,
                'created_at' => $m->created_at?->format('M d, Y'),
            ])
            ->toArray();

        $latestUsers = User::latest()
            ->take(5)
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'username' => $u->username,
                'email' => $u->email,
                'is_active' => (bool) $u->is_active,
                'created_at' => $u->created_at?->format('M d, Y'),
            ])
            ->toArray();

        // Monthly metrics for last 12 months
        $labels = [];
        $ordersPerMonth = [];
        $revenuePerMonth = [];

        $start = Carbon::now()->startOfMonth()->subMonths(11);
        for ($i = 0; $i < 12; $i++) {
            $m = $start->copy()->addMonths($i);
            $labels[] = $m->format('M Y');

            $from = $m->copy()->startOfMonth()->toDateTimeString();
            $to = $m->copy()->endOfMonth()->toDateTimeString();

            $ordersPerMonth[] = Order::whereBetween('created_at', [$from, $to])->count();
            $revenuePerMonth[] = (float) Order::whereBetween('created_at', [$from, $to])->where('status', '!=', 'cancelled')->sum('total');
        }

        return [
            'stats' => $stats,
            'recent_orders' => $recentOrders,
            'recent_reviews' => $recentReviews,
            'recent_messages' => $recentMessages,
            'latest_users' => $latestUsers,
            'top_meals' => Meal::with('category')->orderByDesc('sold_count')->take(5)->get()->map(fn (Meal $meal) => [
                'id' => $meal->id,
                'title' => $meal->title,
                'category' => $meal->category?->name ?? 'Uncategorized',
                'sold_count' => (int) $meal->sold_count,
                'stock_quantity' => (int) $meal->stock_quantity,
                'is_available' => (bool) $meal->is_available,
            ])->toArray(),
            'monthly' => [
                'labels' => $labels,
                'orders' => $ordersPerMonth,
                'revenue' => $revenuePerMonth,
            ],
        ];
    }
}
