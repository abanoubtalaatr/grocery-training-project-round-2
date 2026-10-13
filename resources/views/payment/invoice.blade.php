@extends('layouts.app')

@section('title', trans('common.invoice') . ' - ' . $order->order_number)

@section('content')
<nav class="mb-4 text-sm text-gray-500">
    <a href="{{ route('home') }}" class="hover:text-brand-600">{{ trans('common.home') }}</a>
    <span class="mx-1">/</span>
    <span class="text-gray-900">{{ trans('common.invoice') }}</span>
</nav>

<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-8">
        {{-- Header --}}
        <div class="flex items-start justify-between border-b border-gray-200 pb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ trans('common.app_name') }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ trans('common.invoice') }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-semibold text-gray-900">{{ $invoiceNumber }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ $order->order_number }}</p>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $order->placed_at ? $order->placed_at->format('M d, Y') : $order->created_at->format('M d, Y') }}
                </p>
            </div>
        </div>

        {{-- Status Badge --}}
        <div class="mt-6">
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
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                {{ $order->status_description }}
            </span>
        </div>

        {{-- Customer & Delivery Info --}}
        <div class="mt-6 grid grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-2">{{ trans('common.customer_info') }}</h3>
                <p class="text-sm text-gray-600">{{ $order->user->full_name ?? $order->user->username }}</p>
                <p class="text-sm text-gray-600">{{ $order->user->email }}</p>
                @if($order->user->phone)
                    <p class="text-sm text-gray-600">{{ $order->user->country_code }}{{ $order->user->phone }}</p>
                @endif
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-2">
                    {{ $order->delivery_type === 'delivery' ? trans('common.delivery_address') : trans('common.pickup_location') }}
                </h3>
                @if($order->delivery_type === 'delivery' && $order->address)
                    <p class="text-sm text-gray-600">{{ $order->address->full_address }}</p>
                    @if($order->address->full_name)
                        <p class="text-xs text-gray-500">{{ $order->address->full_name }}</p>
                    @endif
                @elseif($order->delivery_type === 'pickup')
                    <p class="text-sm text-gray-600">{{ $order->pickup_location }}</p>
                @else
                    <p class="text-sm text-gray-400">-</p>
                @endif
            </div>
        </div>

        {{-- Payment Info --}}
        <div class="mt-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">{{ trans('common.payment_info') }}</h3>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">{{ trans('common.method') }}: {{ $paymentDisplay }}</span>
                @if($order->stripe_payment_intent_id)
                    <span class="text-xs text-gray-400">PI: {{ $order->stripe_payment_intent_id }}</span>
                @endif
            </div>
            @if($order->delivery_type === 'delivery' && $order->delivery_speed)
                <p class="mt-1 text-sm text-gray-600">{{ trans('common.delivery_speed') }}: {{ ucfirst($order->delivery_speed) }}</p>
            @endif
        </div>

        {{-- Items --}}
        <div class="mt-8">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ trans('common.items') }}</h3>
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-700">{{ trans('common.product') }}</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-700">{{ trans('common.qty') }}</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-700">{{ trans('common.price') }}</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-700">{{ trans('common.subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($item->meal->image_url)
                                            <img src="{{ $item->meal->image_url }}" alt="" class="w-10 h-10 rounded-lg object-cover">
                                        @endif
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $item->meal->localized_title }}</p>
                                            @if($item->meal->category)
                                                <p class="text-xs text-gray-500">{{ $item->meal->category->localized_name }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">${{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">${{ number_format((float) $item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pricing Summary --}}
        <div class="mt-6 flex justify-end">
            <div class="w-72 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">{{ trans('common.subtotal') }}</span>
                    <span class="font-medium">${{ number_format((float) $order->subtotal, 2) }}</span>
                </div>

                @if($order->discount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ trans('common.discount') }}</span>
                        <span class="font-medium text-green-600">-${{ number_format((float) $order->discount, 2) }}</span>
                    </div>
                @endif

                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">{{ trans('common.tax') }} (10%)</span>
                    <span class="font-medium">${{ number_format((float) $order->tax, 2) }}</span>
                </div>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">{{ trans('common.shipping_fee') }}</span>
                    <span class="font-medium">{{ $order->shipping_fee > 0 ? '$' . number_format((float) $order->shipping_fee, 2) : trans('common.free') }}</span>
                </div>

                <div class="border-t border-gray-200 pt-2 flex justify-between">
                    <span class="text-base font-semibold text-gray-900">{{ trans('common.total') }}</span>
                    <span class="text-base font-bold text-brand-600">${{ number_format((float) $order->total, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        @if($order->notes)
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm font-semibold text-gray-900 mb-1">{{ trans('common.notes') }}</h3>
                <p class="text-sm text-gray-600">{{ $order->notes }}</p>
            </div>
        @endif

        {{-- Footer --}}
        <div class="mt-8 pt-6 border-t border-gray-200 text-center">
            <p class="text-sm text-gray-500">{{ trans('common.thank_you_order') }}</p>
        </div>
    </div>

    <div class="mt-6 flex justify-center gap-4">
        <a href="{{ route('catalog.meals') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">
            {{ trans('common.continue_shopping') }}
        </a>
    </div>
</div>
@endsection
