@extends('layouts.app')

@section('title', trans('common.cart'))

@section('content')
<div>
    <h1 class="text-2xl font-bold text-gray-900">{{ trans('common.cart') }}</h1>
    @if($cart->item_count > 0)
        <p class="mt-1 text-sm text-gray-600">{{ trans('common.items_count', ['count' => $cart->item_count]) }}</p>
    @endif
</div>

@if($cart->isEmpty())
    <div class="mt-12 text-center">
        <svg class="mx-auto w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
        </svg>
        <p class="mt-4 text-gray-500">{{ trans('common.cart_empty') }}</p>
        <a href="{{ route('catalog.meals') }}" class="mt-4 inline-block bg-brand-500 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 transition">
            {{ trans('common.continue_shopping') }}
        </a>
    </div>
@else
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-4">
            @foreach($cart->items as $item)
                <div class="bg-white rounded-xl shadow-sm p-4 flex gap-4" x-data="{ qty: {{ $item->quantity }} }">
                    <a href="{{ route('catalog.meal', $item->meal->slug) }}" class="shrink-0">
                        @if($item->meal->image_url)
                            <img src="{{ $item->meal->image_url }}" alt="{{ $item->meal->localized_title }}" class="w-20 h-20 rounded-lg object-cover">
                        @else
                            <div class="w-20 h-20 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                    </a>

                    <div class="flex-1 min-w-0">
                        <a href="{{ route('catalog.meal', $item->meal->slug) }}" class="text-sm font-semibold text-gray-900 hover:text-brand-600 transition line-clamp-1">
                            {{ $item->meal->localized_title }}
                        </a>
                        @if($item->meal->category)
                            <p class="mt-0.5 text-xs text-gray-500">{{ $item->meal->category->localized_name }}</p>
                        @endif

                        <div class="mt-2 flex items-baseline gap-2">
                            @if($item->meal->discount_price !== null)
                                <span class="text-brand-600 font-bold text-sm">${{ number_format((float) $item->meal->discount_price, 2) }}</span>
                                <span class="text-gray-400 line-through text-xs">${{ number_format((float) $item->meal->price, 2) }}</span>
                            @else
                                <span class="text-brand-600 font-bold text-sm">${{ number_format((float) $item->meal->price, 2) }}</span>
                            @endif
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('cart.update', $item->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="quantity" :value="Math.max(1, qty - 1)">
                                    <button type="submit" class="w-8 h-8 rounded-lg border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition" :disabled="qty <= 1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                    </button>
                                </form>
                                <span class="w-8 text-center text-sm font-medium" x-text="qty">{{ $item->quantity }}</span>
                                <form method="POST" action="{{ route('cart.update', $item->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="quantity" :value="qty + 1">
                                    <button type="submit" class="w-8 h-8 rounded-lg border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    </button>
                                </form>
                            </div>

                            <div class="flex items-center gap-4">
                                <span class="text-sm font-semibold text-gray-900">${{ number_format((float) $item->subtotal, 2) }}</span>
                                <form method="POST" action="{{ route('cart.remove', $item->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="flex justify-between items-center pt-4">
                <a href="{{ route('catalog.meals') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">
                    {{ trans('common.continue_shopping') }}
                </a>
                <form method="POST" action="{{ route('cart.clear') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium">
                        {{ trans('common.clear_cart') }}
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
                <h2 class="text-lg font-semibold text-gray-900">{{ trans('common.order_summary') }}</h2>

                <form method="POST" action="{{ route('cart.applyCoupon') }}" class="mt-4">
                    @csrf
                    <label class="block text-xs font-medium text-gray-700 mb-1">{{ trans('common.coupon_code') }}</label>
                    <div class="flex gap-2">
                        <input type="text" name="code" value="{{ $cart->coupon_code ?? '' }}" placeholder="{{ trans('common.enter_coupon') }}"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                            {{ $cart->coupon_code ? 'disabled' : '' }}>
                        @if($cart->coupon_code)
                            <a href="{{ route('cart.removeCoupon') }}" class="px-3 py-2 bg-red-50 text-red-600 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                                {{ trans('common.remove') }}
                            </a>
                        @else
                            <button type="submit" class="px-3 py-2 bg-brand-500 text-white rounded-lg text-sm font-medium hover:bg-brand-600 transition">
                                {{ trans('common.apply') }}
                            </button>
                        @endif
                    </div>
                    @error('code')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </form>

                <div class="mt-4 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ trans('common.subtotal') }}</span>
                        <span class="font-medium">${{ number_format((float) $cart->subtotal, 2) }}</span>
                    </div>

                    @if($cart->discount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ trans('common.discount') }}</span>
                            <span class="font-medium text-green-600">-${{ number_format((float) $cart->discount, 2) }}</span>
                        </div>
                    @endif

                    @if($cart->coupon_discount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ trans('common.coupon_discount') }} ({{ $cart->coupon_code }})</span>
                            <span class="font-medium text-green-600">-${{ number_format((float) $cart->coupon_discount, 2) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ trans('common.tax') }} (10%)</span>
                        <span class="font-medium">${{ number_format((float) $cart->tax, 2) }}</span>
                    </div>

                    <div class="border-t border-gray-200 pt-3 flex justify-between">
                        <span class="text-base font-semibold text-gray-900">{{ trans('common.total') }}</span>
                        <span class="text-base font-bold text-brand-600">${{ number_format((float) $cart->total, 2) }}</span>
                    </div>
                </div>

                <a href="{{ route('checkout.show') }}" class="mt-6 block w-full bg-brand-500 text-white text-center py-3 rounded-lg font-medium hover:bg-brand-600 transition">
                    {{ trans('common.proceed_to_checkout') }}
                </a>
            </div>
        </div>
    </div>
@endif
@endsection
