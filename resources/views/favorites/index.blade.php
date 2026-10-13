@extends('layouts.app')

@section('title', trans('favorites.title'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ trans('favorites.heading') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ trans('favorites.subtitle') }}</p>
    </div>

    @if($favorites->isEmpty())
        <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-200">
            <svg class="mx-auto w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <p class="mt-4 text-gray-500">{{ trans('favorites.empty') }}</p>
            <a href="{{ route('catalog.meals') }}" class="mt-4 inline-block bg-brand-500 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 transition">
                {{ trans('common.continue_shopping') }}
            </a>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @foreach($favorites as $favorite)
                @php $meal = $favorite->meal; @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden group">
                    <a href="{{ route('catalog.meal', $meal->slug ?? $meal->id) }}" class="block">
                        <div class="relative aspect-square overflow-hidden bg-gray-100">
                            @if($meal->image_url)
                                <img src="{{ $meal->image_url }}" alt="{{ $meal->localized_title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif

                            @if($meal->hasOffer())
                                <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                                    {{ $meal->offer_title ?? trans('common.special_offer') }}
                                </span>
                            @endif
                        </div>
                    </a>

                    <div class="p-3">
                        <a href="{{ route('catalog.meal', $meal->slug ?? $meal->id) }}">
                            <h3 class="text-sm font-semibold text-gray-900 line-clamp-2 group-hover:text-brand-600 transition">
                                {{ $meal->localized_title }}
                            </h3>
                        </a>

                        <div class="mt-2 flex items-baseline gap-2">
                            @if($meal->discount_price !== null)
                                <span class="text-brand-600 font-bold text-sm">${{ number_format((float) $meal->discount_price, 2) }}</span>
                                <span class="text-gray-400 line-through text-xs">${{ number_format((float) $meal->price, 2) }}</span>
                            @else
                                <span class="text-brand-600 font-bold text-sm">${{ number_format((float) $meal->price, 2) }}</span>
                            @endif
                        </div>

                        <div class="mt-2 flex items-center gap-2">
                            <form method="POST" action="{{ route('favorites.toggle', $meal->id) }}" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full text-xs text-red-600 hover:text-red-800 font-medium py-1 border border-red-200 rounded hover:bg-red-50 transition">
                                    {{ trans('favorites.remove') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $favorites->links() }}
        </div>
    @endif
</div>
@endsection
