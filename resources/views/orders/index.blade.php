@extends('layouts.app')

@section('title', trans('orders.history.title'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ trans('orders.history.heading') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ trans('orders.history.subtitle') }}</p>
    </div>

    @if($activeOrdersCount > 0)
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-700">
                {{ trans('orders.history.active_count', ['count' => $activeOrdersCount]) }}
            </p>
        </div>
    @endif

    @if($orders->isEmpty())
        <div class="text-center py-16">
            <svg class="mx-auto w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="mt-4 text-gray-500">{{ trans('orders.history.no_orders') }}</p>
            <a href="{{ route('catalog.meals') }}" class="mt-4 inline-block bg-brand-500 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 transition">
                {{ trans('common.continue_shopping') }}
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
                <a href="{{ route('orders.show', $order->id) }}" class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-gray-900">{{ $order->order_number }}</span>
                                @php
                                    $statusColors = [
                                        'awaiting_payment' => 'bg-yellow-100 text-yellow-800',
                                        'placed' => 'bg-blue-100 text-blue-800',
                                        'processing' => 'bg-indigo-100 text-indigo-800',
                                        'shipping' => 'bg-purple-100 text-purple-800',
                                        'out_for_delivery' => 'bg-orange-100 text-orange-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $order->status_description }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                {{ $order->created_at->format('M d, Y') }} &middot; {{ $order->items->count() }} {{ trans('common.items') }}
                            </p>
                            <p class="mt-1 text-xs text-gray-400">
                                {{ $order->delivery_type === 'delivery' ? trans('common.delivery') : trans('common.pickup') }}
                                &middot; {{ ucfirst($order->payment_method === 'cash_on_delivery' ? trans('common.cash_on_delivery') : trans('common.pay_with_card')) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900">${{ number_format((float) $order->total, 2) }}</p>
                            @if(!in_array($order->status, ['delivered', 'cancelled']))
                                <a href="{{ route('orders.track', $order->id) }}" class="mt-1 inline-flex items-center gap-1 text-xs text-brand-600 hover:text-brand-700 font-medium" onclick="event.stopPropagation();">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ trans('orders.tracking.live_tracking') }}
                                </a>
                            @else
                                <p class="mt-1 text-xs text-gray-400">{{ trans('orders.history.view_details') }}</p>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
