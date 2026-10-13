@extends('layouts.app')

@section('title', trans('orders.addresses.title'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ trans('orders.addresses.heading') }}</h1>
        <a href="{{ route('addresses.create') }}" class="bg-brand-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 transition">
            {{ trans('orders.addresses.add_new') }}
        </a>
    </div>

    @if($addresses->isEmpty())
        <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-200">
            <svg class="mx-auto w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="mt-4 text-gray-500">{{ trans('orders.addresses.no_addresses') }}</p>
            <a href="{{ route('addresses.create') }}" class="mt-4 inline-block bg-brand-500 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 transition">
                {{ trans('orders.addresses.add_first') }}
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($addresses as $address)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 {{ $address->is_default ? 'border-brand-300 ring-1 ring-brand-200' : '' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                @if($address->label)
                                    <span class="text-xs font-medium text-brand-600 bg-brand-50 px-2 py-0.5 rounded">{{ $address->label }}</span>
                                @endif
                                @if($address->is_default)
                                    <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded">{{ trans('common.default') }}</span>
                                @endif
                            </div>
                            <p class="mt-2 text-sm font-medium text-gray-900">{{ $address->full_name }}</p>
                            <p class="mt-1 text-sm text-gray-600">{{ $address->full_address }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ $address->formatted_phone }}</p>
                            @if($address->notes)
                                <p class="mt-1 text-xs text-gray-400 italic">{{ $address->notes }}</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 ml-4">
                            @if(! $address->is_default)
                                <form method="POST" action="{{ route('addresses.setDefault', $address->id) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-brand-600 hover:text-brand-700 font-medium">
                                        {{ trans('orders.addresses.set_default') }}
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('addresses.edit', $address->id) }}" class="text-sm text-gray-600 hover:text-gray-800">
                                {{ trans('common.edit') }}
                            </a>
                            <form method="POST" action="{{ route('addresses.destroy', $address->id) }}"
                                x-data="{ confirmed: false }"
                                @submit.prevent="if(confirmed) $el.submit(); else if(confirm('{{ trans('orders.addresses.delete_confirm') }}')) { confirmed = true; $el.submit(); }">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                    {{ trans('common.delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
