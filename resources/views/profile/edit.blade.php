@extends('layouts.app')

@section('title', trans('orders.profile.edit_title'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('profile.show') }}" class="text-sm text-brand-600 hover:text-brand-700">
            &larr; {{ trans('common.back') }}
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ trans('orders.profile.edit_heading') }}</h1>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="firstname" class="block text-sm font-medium text-gray-700">{{ trans('orders.profile.firstname') }}</label>
                        <input type="text" name="firstname" id="firstname" value="{{ old('firstname', $user->firstname) }}" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                    </div>
                    <div>
                        <label for="lastname" class="block text-sm font-medium text-gray-700">{{ trans('orders.profile.lastname') }}</label>
                        <input type="text" name="lastname" id="lastname" value="{{ old('lastname', $user->lastname) }}" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                    </div>
                </div>

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">{{ trans('orders.profile.username') }}</label>
                    <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">{{ trans('orders.profile.email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="country_code" class="block text-sm font-medium text-gray-700">{{ trans('orders.profile.country_code') }}</label>
                        <input type="text" name="country_code" id="country_code" value="{{ old('country_code', $user->country_code) }}"
                            placeholder="+1"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="phone" class="block text-sm font-medium text-gray-700">{{ trans('orders.profile.phone') }}</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">{{ trans('orders.profile.gender') }}</label>
                        <select name="gender" id="gender"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                            <option value="">{{ trans('orders.profile.gender_select') }}</option>
                            <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>{{ trans('orders.profile.gender_male') }}</option>
                            <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>{{ trans('orders.profile.gender_female') }}</option>
                            <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>{{ trans('orders.profile.gender_other') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="birthday" class="block text-sm font-medium text-gray-700">{{ trans('orders.profile.birthday') }}</label>
                        <input type="date" name="birthday" id="birthday" value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center gap-4">
                <button type="submit" class="bg-brand-500 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 transition">
                    {{ trans('common.save') }}
                </button>
                <a href="{{ route('profile.show') }}" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                    {{ trans('common.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
