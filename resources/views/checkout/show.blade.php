@extends('layouts.app')

@section('title', trans('common.checkout'))

@section('content')
<div x-data="checkoutForm()">
    <nav class="mb-4 text-sm text-gray-500">
        <a href="{{ route('catalog.categories') }}" class="hover:text-brand-600">{{ trans('common.categories') }}</a>
        <span class="mx-1">/</span>
        <a href="{{ route('cart.show') }}" class="hover:text-brand-600">{{ trans('common.cart') }}</a>
        <span class="mx-1">/</span>
        <span class="text-gray-900">{{ trans('common.checkout') }}</span>
    </nav>

    <h1 class="text-2xl font-bold text-gray-900">{{ trans('common.checkout') }}</h1>

    <form method="POST" action="{{ route('checkout.placeOrder') }}" class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
        @csrf

        <div class="lg:col-span-2 space-y-6">
            {{-- Delivery Type --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ trans('common.delivery_method') }}</h2>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition"
                        :class="deliveryType === 'delivery' ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="delivery_type" value="delivery" x-model="deliveryType" class="text-brand-500 focus:ring-brand-500">
                        <div>
                            <span class="text-sm font-medium text-gray-900">{{ trans('common.delivery') }}</span>
                            <p class="text-xs text-gray-500">{{ trans('common.deliver_to_address') }}</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition"
                        :class="deliveryType === 'pickup' ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="delivery_type" value="pickup" x-model="deliveryType" class="text-brand-500 focus:ring-brand-500">
                        <div>
                            <span class="text-sm font-medium text-gray-900">{{ trans('common.pickup') }}</span>
                            <p class="text-xs text-gray-500">{{ trans('common.pickup_from_store') }}</p>
                        </div>
                    </label>
                </div>
                @error('delivery_type')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Delivery Address --}}
            <div class="bg-white rounded-xl shadow-sm p-6" x-show="deliveryType === 'delivery'" x-transition>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ trans('common.delivery_address') }}</h2>

                @if($addresses->count() > 0)
                    <div class="space-y-3">
                        @foreach($addresses as $address)
                            <label class="flex items-start gap-3 p-4 border-2 rounded-xl cursor-pointer transition"
                                :class="selectedAddress == {{ $address->id }} ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="address_id" value="{{ $address->id }}" x-model="selectedAddress" class="mt-0.5 text-brand-500 focus:ring-brand-500">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-900">{{ $address->label ?: trans('common.address') }}</span>
                                        @if($address->is_default)
                                            <span class="text-xs bg-brand-100 text-brand-700 px-2 py-0.5 rounded">{{ trans('common.default') }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">{{ $address->full_address }}</p>
                                    @if($address->full_name)
                                        <p class="text-xs text-gray-500">{{ $address->full_name }} {{ $address->phone ? '- ' . $address->formatted_phone : '' }}</p>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <p class="text-sm text-gray-500">{{ trans('common.no_addresses') }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ trans('common.add_address_hint') }}</p>
                    </div>
                @endif
                @error('address_id')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Pickup Location --}}
            <div class="bg-white rounded-xl shadow-sm p-6" x-show="deliveryType === 'pickup'" x-transition>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ trans('common.pickup_location') }}</h2>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-900 font-medium">{{ $pickupLocation ?? trans('common.store_address_default') }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ trans('common.pickup_hours') }}</p>
                </div>
                <input type="hidden" name="pickup_location" :value="deliveryType === 'pickup' ? '{{ $pickupLocation }}' : ''">
                @error('pickup_location')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Delivery Speed --}}
            <div class="bg-white rounded-xl shadow-sm p-6" x-show="deliveryType === 'delivery'" x-transition>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ trans('common.delivery_speed') }}</h2>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition"
                        :class="deliverySpeed === 'standard' ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="delivery_speed" value="standard" x-model="deliverySpeed" class="text-brand-500 focus:ring-brand-500">
                        <div>
                            <span class="text-sm font-medium text-gray-900">{{ trans('common.standard') }}</span>
                            <p class="text-xs text-gray-500">{{ trans('common.standard_delivery_time') }}</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition"
                        :class="deliverySpeed === 'priority' ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="delivery_speed" value="priority" x-model="deliverySpeed" class="text-brand-500 focus:ring-brand-500">
                        <div>
                            <span class="text-sm font-medium text-gray-900">{{ trans('common.priority') }}</span>
                            <p class="text-xs text-gray-500">{{ trans('common.priority_delivery_time') }}</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Payment Method --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ trans('common.payment_method') }}</h2>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition"
                        :class="paymentMethod === 'stripe_checkout' ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="payment_method" value="stripe_checkout" x-model="paymentMethod" class="text-brand-500 focus:ring-brand-500">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-purple-600" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-7.076-2.19L3.5 21.5C5.578 22.898 8.812 24 12.21 24c2.631 0 4.808-.658 6.308-1.908 1.655-1.366 2.467-3.356 2.467-5.634 0-4.097-2.467-5.799-6.967-7.308z"/>
                            </svg>
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ trans('common.pay_with_card') }}</span>
                                <p class="text-xs text-gray-500">{{ trans('common.stripe_checkout_desc') }}</p>
                            </div>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition"
                        :class="paymentMethod === 'cash_on_delivery' ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="payment_method" value="cash_on_delivery" x-model="paymentMethod" class="text-brand-500 focus:ring-brand-500">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ trans('common.cash_on_delivery') }}</span>
                                <p class="text-xs text-gray-500">{{ trans('common.cod_desc') }}</p>
                            </div>
                        </div>
                    </label>
                </div>
                @error('payment_method')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Notes --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ trans('common.order_notes') }}</h2>
                <textarea name="notes" rows="3" placeholder="{{ trans('common.order_notes_placeholder') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Order Summary --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
                <h2 class="text-lg font-semibold text-gray-900">{{ trans('common.order_summary') }}</h2>

                <div class="mt-4 max-h-60 overflow-y-auto space-y-3">
                    @foreach($cart->items as $item)
                        <div class="flex gap-3">
                            @if($item->meal->image_url)
                                <img src="{{ $item->meal->image_url }}" alt="" class="w-12 h-12 rounded-lg object-cover shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-gray-100 shrink-0"></div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 line-clamp-1">{{ $item->meal->localized_title }}</p>
                                <p class="text-xs text-gray-500">x{{ $item->quantity }}</p>
                            </div>
                            <span class="text-sm font-medium text-gray-900 shrink-0">${{ number_format((float) $item->subtotal, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 space-y-3 border-t border-gray-100 pt-4">
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
                            <span class="text-gray-600">{{ trans('common.coupon_discount') }}</span>
                            <span class="font-medium text-green-600">-${{ number_format((float) $cart->coupon_discount, 2) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ trans('common.tax') }} (10%)</span>
                        <span class="font-medium">${{ number_format((float) $cart->tax, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ trans('common.shipping_fee') }}</span>
                        <span class="font-medium" id="shipping-fee">{{ trans('common.calculated_at_next_step') }}</span>
                    </div>

                    <div class="border-t border-gray-200 pt-3 flex justify-between">
                        <span class="text-base font-semibold text-gray-900">{{ trans('common.total') }}</span>
                        <span class="text-base font-bold text-brand-600">${{ number_format((float) $cart->total, 2) }}</span>
                    </div>
                </div>

                <button type="submit" class="mt-6 w-full bg-brand-500 text-white py-3 rounded-lg font-medium hover:bg-brand-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    x-bind:disabled="!canSubmit">
                    <span x-show="paymentMethod === 'stripe_checkout'">{{ trans('common.pay_with_stripe') }}</span>
                    <span x-show="paymentMethod === 'cash_on_delivery'">{{ trans('common.place_order') }}</span>
                </button>

                <p class="mt-3 text-xs text-gray-400 text-center">
                    {{ trans('common.secure_checkout') }}
                </p>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function checkoutForm() {
    return {
        deliveryType: 'delivery',
        selectedAddress: '{{ $addresses->where('is_default', true)->first()?->id ?? ($addresses->first()?->id ?? '') }}',
        deliverySpeed: 'standard',
        paymentMethod: 'stripe_checkout',
        get canSubmit() {
            if (this.deliveryType === 'delivery' && !this.selectedAddress) return false;
            if (!this.paymentMethod) return false;
            return true;
        }
    }
}
</script>
@endpush
@endsection
