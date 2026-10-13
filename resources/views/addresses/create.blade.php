@extends('layouts.app')

@section('title', trans('orders.addresses.create_title'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('addresses.index') }}" class="text-sm text-brand-600 hover:text-brand-700">
            &larr; {{ trans('common.back') }}
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ trans('orders.addresses.create_heading') }}</h1>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('addresses.store') }}">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="label" class="block text-sm font-medium text-gray-700">{{ trans('orders.addresses.label') }}</label>
                    <input type="text" name="label" id="label" value="{{ old('label') }}"
                        placeholder="{{ trans('orders.addresses.label_placeholder') }}"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">{{ trans('orders.addresses.full_name') }} *</label>
                        <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">{{ trans('orders.addresses.phone') }} *</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                    </div>
                </div>

                <div>
                    <label for="country_code" class="block text-sm font-medium text-gray-700">{{ trans('orders.profile.country_code') }}</label>
                    <input type="text" name="country_code" id="country_code" value="{{ old('country_code') }}"
                        placeholder="+1"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                </div>

                <div>
                    <label for="street_address" class="block text-sm font-medium text-gray-700">{{ trans('orders.addresses.street_address') }} *</label>
                    <input type="text" name="street_address" id="street_address" value="{{ old('street_address') }}" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="building_number" class="block text-sm font-medium text-gray-700">{{ trans('orders.addresses.building_number') }}</label>
                        <input type="text" name="building_number" id="building_number" value="{{ old('building_number') }}"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                    </div>
                    <div>
                        <label for="floor" class="block text-sm font-medium text-gray-700">{{ trans('orders.addresses.floor') }}</label>
                        <input type="text" name="floor" id="floor" value="{{ old('floor') }}"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                    </div>
                    <div>
                        <label for="apartment" class="block text-sm font-medium text-gray-700">{{ trans('orders.addresses.apartment') }}</label>
                        <input type="text" name="apartment" id="apartment" value="{{ old('apartment') }}"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                    </div>
                </div>

                <div>
                    <label for="landmark" class="block text-sm font-medium text-gray-700">{{ trans('orders.addresses.landmark') }}</label>
                    <input type="text" name="landmark" id="landmark" value="{{ old('landmark') }}"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700">{{ trans('orders.addresses.city') }} *</label>
                        <input type="text" name="city" id="city" value="{{ old('city') }}" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                    </div>
                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700">{{ trans('orders.addresses.state') }}</label>
                        <input type="text" name="state" id="state" value="{{ old('state') }}"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                    </div>
                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700">{{ trans('orders.addresses.postal_code') }}</label>
                        <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                    </div>
                </div>

                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700">{{ trans('orders.addresses.country') }}</label>
                    <input type="text" name="country" id="country" value="{{ old('country') }}"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">{{ trans('orders.addresses.notes') }}</label>
                    <textarea name="notes" id="notes" rows="2" maxlength="500"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_default" id="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}
                        class="h-4 w-4 text-brand-500 focus:ring-brand-500 border-gray-300 rounded">
                    <label for="is_default" class="ml-2 text-sm text-gray-700">{{ trans('orders.addresses.set_as_default') }}</label>
                </div>
            </div>

            <div class="mt-8 flex items-center gap-4">
                <button type="submit" class="bg-brand-500 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 transition">
                    {{ trans('orders.addresses.save_address') }}
                </button>
                <a href="{{ route('addresses.index') }}" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                    {{ trans('common.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
