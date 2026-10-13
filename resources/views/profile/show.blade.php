@extends('layouts.app')

@section('title', trans('orders.profile.title'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ trans('orders.profile.heading') }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
            <div class="w-20 h-20 mx-auto rounded-full bg-brand-100 flex items-center justify-center overflow-hidden">
                @if($user->profile_image_url)
                    <img src="{{ $user->profile_image_url }}" alt="{{ $user->full_name }}" class="w-20 h-20 rounded-full object-cover">
                @elseif($user->avatar)
                    <img src="{{ $user->avatar }}" alt="{{ $user->full_name }}" class="w-20 h-20 rounded-full object-cover">
                @else
                    <span class="text-2xl font-bold text-brand-600">{{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}</span>
                @endif
            </div>
            <h2 class="mt-4 text-lg font-semibold text-gray-900">{{ $user->full_name }}</h2>
            <p class="text-sm text-gray-500">{{ $user->username }}</p>
            <a href="{{ route('profile.edit') }}" class="mt-4 inline-block text-sm text-brand-600 hover:text-brand-700 font-medium">
                {{ trans('common.edit') }} {{ trans('common.profile') }}
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ trans('orders.profile.account_stats') }}</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">{{ trans('orders.profile.total_orders') }}</span>
                    <span class="text-sm font-medium text-gray-900">{{ $totalOrdersCount }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">{{ trans('orders.profile.active_orders') }}</span>
                    <span class="text-sm font-medium text-gray-900">{{ $activeOrdersCount }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">{{ trans('orders.profile.saved_addresses') }}</span>
                    <span class="text-sm font-medium text-gray-900">{{ $addressesCount }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ trans('orders.profile.quick_actions') }}</h3>
            <div class="space-y-2">
                <a href="{{ route('orders.index') }}" class="block text-sm text-brand-600 hover:text-brand-700 font-medium">
                    {{ trans('orders.history.heading') }}
                </a>
                <a href="{{ route('addresses.index') }}" class="block text-sm text-brand-600 hover:text-brand-700 font-medium">
                    {{ trans('orders.profile.manage_addresses') }}
                </a>
                <a href="{{ route('profile.image') }}" class="block text-sm text-brand-600 hover:text-brand-700 font-medium">
                    {{ trans('orders.profile.change_photo') }}
                </a>
                <a href="{{ route('sessions.index') }}" class="block text-sm text-brand-600 hover:text-brand-700 font-medium">
                    {{ trans('common.active_sessions') }}
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ trans('orders.profile.personal_info') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500">{{ trans('orders.profile.firstname') }}</p>
                <p class="text-sm font-medium text-gray-900">{{ $user->firstname ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">{{ trans('orders.profile.lastname') }}</p>
                <p class="text-sm font-medium text-gray-900">{{ $user->lastname ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">{{ trans('orders.profile.email') }}</p>
                <p class="text-sm font-medium text-gray-900">{{ $user->email ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">{{ trans('orders.profile.phone') }}</p>
                <p class="text-sm font-medium text-gray-900">{{
                    ($user->phone && $user->phone !== '')
                        ? ((str_starts_with($user->phone, '+') || ($user->country_code && str_starts_with($user->phone, $user->country_code)))
                            ? $user->phone
                            : $user->country_code . $user->phone)
                        : '-'
                }}</p>
            </div>
            @if($user->gender)
                <div>
                    <p class="text-xs text-gray-500">{{ trans('orders.profile.gender') }}</p>
                    <p class="text-sm font-medium text-gray-900">{{ ucfirst($user->gender) }}</p>
                </div>
            @endif
            @if($user->birthday)
                <div>
                    <p class="text-xs text-gray-500">{{ trans('orders.profile.birthday') }}</p>
                    <p class="text-sm font-medium text-gray-900">{{ $user->birthday->format('M d, Y') }}</p>
                </div>
            @endif
            <div>
                <p class="text-xs text-gray-500">{{ trans('orders.profile.member_since') }}</p>
                <p class="text-sm font-medium text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>

    @if($user->orders->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900">{{ trans('orders.profile.recent_orders') }}</h3>
                <a href="{{ route('orders.index') }}" class="text-sm text-brand-600 hover:text-brand-700">{{ trans('common.view_all') }}</a>
            </div>
            <div class="space-y-3">
                @foreach($user->orders as $order)
                    <a href="{{ route('orders.show', $order->id) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-500">{{ $order->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">${{ number_format((float) $order->total, 2) }}</p>
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
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $order->status_description }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
