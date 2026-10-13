@extends('layouts.app')

@section('title', trans('orders.detail.title') . ' - ' . $order->order_number)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('orders.index') }}" class="text-sm text-brand-600 hover:text-brand-700">
            &larr; {{ trans('common.back') }}
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $order->order_number }}</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ trans('orders.detail.placed_on', ['date' => $order->created_at->format('M d, Y \a\t g:i A')]) }}
                    </p>
                </div>
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
        </div>

        {{-- Status Tracker --}}
        @if($order->status !== 'cancelled')
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ trans('orders.detail.order_progress') }}</h3>
                @php
                    $steps = [
                        ['key' => 'placed', 'label' => trans('orders.detail.status_placed'), 'position' => 1],
                        ['key' => 'processing', 'label' => trans('orders.detail.status_processing'), 'position' => 2],
                        ['key' => 'shipping', 'label' => trans('orders.detail.status_shipping'), 'position' => 3],
                        ['key' => 'out_for_delivery', 'label' => trans('orders.detail.status_out_for_delivery'), 'position' => 4],
                        ['key' => 'delivered', 'label' => trans('orders.detail.status_delivered'), 'position' => 5],
                    ];
                    $currentPosition = $order->status_position;
                @endphp
                <div class="flex items-center justify-between">
                    @foreach($steps as $index => $step)
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                                {{ $currentPosition >= $step['position'] ? 'bg-brand-500 text-white' : 'bg-gray-200 text-gray-500' }}">
                                @if($currentPosition > $step['position'])
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @else
                                    {{ $step['position'] }}
                                @endif
                            </div>
                            <p class="mt-2 text-xs text-center font-medium {{ $currentPosition >= $step['position'] ? 'text-brand-600' : 'text-gray-400' }}">
                                {{ $step['label'] }}
                            </p>
                            @if($currentPosition >= $step['position'] && $index < count($steps) - 1 && $currentPosition === $step['position'])
                                @php
                                    $timestampField = match($step['key']) {
                                        'placed' => 'placed_at',
                                        'processing' => 'processing_at',
                                        'shipping' => 'shipping_at',
                                        'out_for_delivery' => 'out_for_delivery_at',
                                        'delivered' => 'delivered_at',
                                        default => null,
                                    };
                                @endphp
                                @if($timestampField && $order->{$timestampField})
                                    <p class="mt-1 text-xs text-gray-400">{{ $order->{$timestampField}->format('g:i A') }}</p>
                                @endif
                            @endif
                        </div>
                        @if(! $loop->last)
                            <div class="flex-1 h-0.5 mx-1 {{ $currentPosition > $step['position'] ? 'bg-brand-500' : 'bg-gray-200' }}"></div>
                        @endif
                    @endforeach
                </div>

                @if($order->estimated_delivery_time && ! in_array($order->status, ['delivered', 'cancelled']))
                    <div class="mt-4 p-3 bg-white rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">{{ trans('orders.detail.estimated_delivery') }}:</span>
                            {{ $order->estimated_delivery_time->format('M d, Y \a\t g:i A') }}
                        </p>
                    </div>
                @endif
            </div>
        @endif

        <div class="p-6 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ trans('orders.detail.items') }}</h3>
            <div class="space-y-3">
                @foreach($order->items as $item)
                    <div class="flex items-center gap-4">
                        @if($item->meal && $item->meal->image_path)
                            <img src="{{ asset('storage/' . $item->meal->image_path) }}" alt="{{ $item->meal->localized_name }}" class="w-12 h-12 rounded-lg object-cover">
                        @else
                            <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $item->meal->localized_name ?? $item->meal->title ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ trans('common.qty') }}: {{ $item->quantity }} &times; ${{ number_format((float) $item->unit_price, 2) }}</p>
                        </div>
                        <p class="text-sm font-medium text-gray-900">${{ number_format((float) $item->subtotal, 2) }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="p-6 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ trans('orders.detail.pricing') }}</h3>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">{{ trans('common.subtotal') }}</span>
                    <span class="text-gray-900">${{ number_format((float) $order->subtotal, 2) }}</span>
                </div>
                @if((float) $order->discount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ trans('common.discount') }}</span>
                        <span class="text-green-600">-${{ number_format((float) $order->discount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">{{ trans('common.tax') }}</span>
                    <span class="text-gray-900">${{ number_format((float) $order->tax, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">{{ trans('common.shipping_fee') }}</span>
                    <span class="text-gray-900">
                        @if((float) $order->shipping_fee > 0)
                            ${{ number_format((float) $order->shipping_fee, 2) }}
                        @else
                            {{ trans('common.free') }}
                        @endif
                    </span>
                </div>
                <div class="flex justify-between text-base font-bold pt-2 border-t border-gray-200">
                    <span class="text-gray-900">{{ trans('common.total') }}</span>
                    <span class="text-gray-900">${{ number_format((float) $order->total, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="p-6 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ trans('orders.detail.delivery_info') }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500">{{ trans('common.delivery_method') }}</p>
                    <p class="text-sm font-medium text-gray-900">
                        {{ $order->delivery_type === 'delivery' ? trans('common.delivery') : trans('common.pickup') }}
                        @if($order->delivery_speed)
                            ({{ ucfirst($order->delivery_speed) }})
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ trans('common.payment_method') }}</p>
                    <p class="text-sm font-medium text-gray-900">
                        @switch($order->payment_method)
                            @case('card') {{ trans('common.pay_with_card') }} @break
                            @case('stripe_checkout') {{ trans('common.pay_with_stripe') }} @break
                            @case('cash_on_delivery') {{ trans('common.cash_on_delivery') }} @break
                            @default {{ ucfirst($order->payment_method) }}
                        @endswitch
                    </p>
                </div>
                @if($order->delivery_type === 'delivery' && $order->address)
                    <div class="sm:col-span-2">
                        <p class="text-xs text-gray-500">{{ trans('common.delivery_address') }}</p>
                        <p class="text-sm font-medium text-gray-900">{{ $order->address->full_address }}</p>
                    </div>
                @endif
                @if($order->delivery_type === 'pickup' && $order->pickup_location)
                    <div class="sm:col-span-2">
                        <p class="text-xs text-gray-500">{{ trans('common.pickup_location') }}</p>
                        <p class="text-sm font-medium text-gray-900">{{ $order->pickup_location }}</p>
                    </div>
                @endif
                @if($order->notes)
                    <div class="sm:col-span-2">
                        <p class="text-xs text-gray-500">{{ trans('common.notes') }}</p>
                        <p class="text-sm text-gray-900">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if(!in_array($order->status, ['delivered', 'cancelled']))
            <div class="p-6 border-t border-gray-200">
                <a href="{{ route('orders.track', $order->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-medium hover:bg-brand-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ trans('orders.tracking.live_tracking') }}
                </a>
            </div>
        @endif

        @if($canCancel)
            <div class="p-6" x-data="{ showCancelForm: false }">
                <div x-show="!showCancelForm">
                    <button @click="showCancelForm = true" class="text-sm text-red-600 hover:text-red-800 font-medium">
                        {{ trans('orders.detail.cancel_order') }}
                    </button>
                </div>
                <div x-show="showCancelForm" x-cloak>
                    <form method="POST" action="{{ route('orders.cancel', $order->id) }}">
                        @csrf
                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700">{{ trans('orders.detail.cancel_reason') }}</label>
                            <textarea name="reason" id="reason" rows="3" maxlength="500"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                                placeholder="{{ trans('orders.detail.cancel_reason_placeholder') }}"></textarea>
                        </div>
                        <div class="mt-4 flex items-center gap-4">
                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">
                                {{ trans('orders.detail.confirm_cancel') }}
                            </button>
                            <button type="button" @click="showCancelForm = false" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                                {{ trans('common.cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
