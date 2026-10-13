@extends('layouts.app')

@section('title', trans('dashboard.title'))

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ trans('dashboard.heading', ['name' => $user->firstname ?? $user->username]) }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ trans('dashboard.subtitle') }}</p>
    </div>

    {{-- Analytics Cards --}}
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ trans('dashboard.analytics.title') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $analytics['time_spent_minutes'] }}</p>
                        <p class="text-xs text-gray-500">{{ trans('dashboard.analytics.time_spent') }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $analytics['viewed_products_count'] }}</p>
                        <p class="text-xs text-gray-500">{{ trans('dashboard.analytics.viewed_products') }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $analytics['orders_count'] }}</p>
                        <p class="text-xs text-gray-500">{{ trans('dashboard.analytics.total_orders') }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $analytics['notifications_count'] }}</p>
                        <p class="text-xs text-gray-500">{{ trans('dashboard.analytics.unread_notifications') }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $analytics['favorites_count'] }}</p>
                        <p class="text-xs text-gray-500">{{ trans('dashboard.analytics.favorites') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Recent Orders --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">{{ trans('dashboard.recent_orders.title') }}</h3>
                    <a href="{{ route('orders.index') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">{{ trans('dashboard.recent_orders.view_all') }}</a>
                </div>
                @if($recentOrders->isEmpty())
                    <div class="p-6">
                        <p class="text-sm text-gray-500">{{ trans('dashboard.recent_orders.no_orders') }}</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-200">
                        @foreach($recentOrders as $order)
                            <a href="{{ route('orders.show', $order['id']) }}" class="block p-4 hover:bg-gray-50 transition">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $order['order_number'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $order['created_at']->format('M d, Y') }} &middot; {{ trans('dashboard.recent_orders.items_count', ['count' => $order['items_count']]) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-gray-900">${{ number_format($order['total'], 2) }}</p>
                                        @php
                                            $statusColors = [
                                                'placed' => 'bg-blue-100 text-blue-800',
                                                'processing' => 'bg-indigo-100 text-indigo-800',
                                                'shipping' => 'bg-purple-100 text-purple-800',
                                                'out_for_delivery' => 'bg-orange-100 text-orange-800',
                                                'delivered' => 'bg-green-100 text-green-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $order['status_description'] }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Shopping Insights --}}
        <div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ trans('dashboard.shopping_insights.title') }}</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-500">{{ trans('dashboard.shopping_insights.total_spent') }}</p>
                        <p class="text-lg font-bold text-gray-900">${{ number_format($shoppingInsights['total_spent'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">{{ trans('dashboard.shopping_insights.orders_count') }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $shoppingInsights['orders_count'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">{{ trans('dashboard.shopping_insights.avg_order_value') }}</p>
                        <p class="text-lg font-bold text-gray-900">${{ number_format($shoppingInsights['average_order_value'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">{{ trans('dashboard.shopping_insights.avg_days_between') }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $shoppingInsights['average_days_between'] }} {{ trans('dashboard.shopping_insights.days') }}</p>
                    </div>
                </div>
            </div>

            {{-- Top Purchases --}}
            <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">{{ trans('dashboard.top_purchases.title') }}</h3>
                </div>
                @if($topPurchases->isEmpty())
                    <div class="p-6">
                        <p class="text-sm text-gray-500">{{ trans('dashboard.top_purchases.no_purchases') }}</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-200">
                        @foreach($topPurchases as $purchase)
                            <div class="p-4 flex items-center gap-3">
                                @if($purchase['image_path'])
                                    <img src="{{ asset('storage/' . $purchase['image_path']) }}" alt="{{ $purchase['title'] }}" class="w-10 h-10 rounded-lg object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $purchase['title'] }}</p>
                                    <p class="text-xs text-gray-500">{{ trans('dashboard.top_purchases.times_purchased', ['count' => $purchase['total_quantity']]) }}</p>
                                </div>
                                <p class="text-sm font-medium text-gray-900">${{ number_format($purchase['total_spent'], 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
