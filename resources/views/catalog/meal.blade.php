@extends('layouts.app')

@section('title', $meal->localized_title)

@section('content')
<nav class="mb-4 text-sm text-gray-500">
    <a href="{{ route('catalog.categories') }}" class="hover:text-brand-600">{{ trans('common.categories') }}</a>
    <span class="mx-1">/</span>
    <a href="{{ route('catalog.category', $meal->category->slug) }}" class="hover:text-brand-600">{{ $meal->category->localized_name }}</a>
    @if($meal->subcategory)
        <span class="mx-1">/</span>
        <a href="{{ route('catalog.subcategory', $meal->subcategory->slug) }}" class="hover:text-brand-600">{{ $meal->subcategory->localized_name }}</a>
    @endif
    <span class="mx-1">/</span>
    <span class="text-gray-900">{{ $meal->localized_title }}</span>
</nav>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div>
        <div class="relative aspect-square rounded-xl overflow-hidden bg-gray-100">
            @if($meal->image_url)
                <img src="{{ $meal->image_url }}" alt="{{ $meal->localized_title }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center text-gray-400">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            @endif

            @if($meal->hasOffer())
                <span class="absolute top-4 left-4 bg-red-500 text-white text-sm font-bold px-3 py-1 rounded-lg">
                    {{ $meal->offer_title ?? trans('common.special_offer') }}
                </span>
            @endif
        </div>
    </div>

    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $meal->localized_title }}</h1>

        @if($meal->category)
            <a href="{{ route('catalog.category', $meal->category->slug) }}" class="mt-2 inline-block text-sm text-brand-600 hover:text-brand-700">
                {{ $meal->category->localized_name }}
            </a>
        @endif

        @if($meal->rating > 0)
            <div class="flex items-center mt-3 gap-2">
                <div class="flex text-amber-400">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= floor($meal->rating))
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @else
                            <svg class="w-4 h-4 fill-current text-gray-300" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endif
                    @endfor
                </div>
                <span class="text-sm text-gray-600">{{ $meal->rating }} ({{ $meal->rating_count }} {{ trans('common.reviews') }})</span>
            </div>
        @endif

        <div class="mt-4 flex items-baseline gap-3">
            @if($meal->discount_price !== null)
                <span class="text-2xl font-bold text-brand-600">${{ number_format((float) $meal->discount_price, 2) }}</span>
                <span class="text-lg text-gray-400 line-through">${{ number_format((float) $meal->price, 2) }}</span>
            @else
                <span class="text-2xl font-bold text-brand-600">${{ number_format((float) $meal->price, 2) }}</span>
            @endif
        </div>

        <div class="mt-4 flex items-center gap-4 text-sm">
            @if($meal->size)
                <span class="text-gray-600">{{ $meal->size }}</span>
            @endif
            @if($meal->brand)
                <span class="text-gray-600">{{ $meal->brand }}</span>
            @endif
            <span class="{{ $meal->isInStock() ? 'text-green-600' : 'text-red-600' }}">
                {{ $meal->isInStock() ? trans('common.in_stock') : trans('common.out_of_stock') }}
            </span>
        </div>

        <div class="mt-6 flex items-center gap-3">
            @auth
                @if($meal->is_available && $meal->isInStock())
                    <div x-data="{ qty: 1 }">
                        <form method="POST" action="{{ route('cart.addItem') }}" class="flex items-center gap-2">
                            @csrf
                            <input type="hidden" name="meal_id" value="{{ $meal->id }}">
                            <input type="hidden" name="quantity" :value="qty">
                            <div class="flex items-center border border-gray-300 rounded-lg">
                                <button type="button" @click="qty = Math.max(1, qty - 1)" class="w-9 h-9 flex items-center justify-center text-gray-600 hover:bg-gray-50 rounded-l-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                </button>
                                <span class="w-9 text-center text-sm font-medium" x-text="qty">1</span>
                                <button type="button" @click="qty = Math.min({{ config('cart.max_quantity_per_product', 10) }}, qty + 1)" class="w-9 h-9 flex items-center justify-center text-gray-600 hover:bg-gray-50 rounded-r-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                            <button type="submit" class="flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-medium bg-brand-500 text-white hover:bg-brand-600 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                {{ trans('common.add_to_cart') }}
                            </button>
                        </form>
                    </div>
                @endif

                <form method="POST" action="{{ route('favorites.toggle', $meal->id) }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border transition
                        {{ $isFavorited ? 'bg-red-50 border-red-200 text-red-600 hover:bg-red-100' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                        @if($isFavorited)
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        @endif
                        {{ $isFavorited ? trans('favorites.favorited') : trans('favorites.add') }}
                    </button>
                </form>

                @if($userSmartLists->isNotEmpty())
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            {{ trans('smart_lists.add_to_list') }}
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                            @foreach($userSmartLists as $list)
                                <form method="POST" action="{{ route('smart-lists.meals.store', $list->id) }}">
                                    @csrf
                                    <input type="hidden" name="meal_id" value="{{ $meal->id }}">
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        {{ $list->name }} <span class="text-gray-400">({{ $list->meals_count }})</span>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <a href="{{ route('login') }}" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    {{ trans('common.add_to_cart') }}
                </a>
            @endauth
        </div>

        @if($meal->localized_description)
            <div class="mt-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-2">{{ trans('common.description') }}</h2>
                <p class="text-sm text-gray-600 leading-relaxed">{{ $meal->localized_description }}</p>
            </div>
        @endif

        @if($meal->features)
            <div class="mt-4">
                <h2 class="text-sm font-semibold text-gray-900 mb-2">{{ trans('common.details') }}</h2>
                <div class="text-sm text-gray-600">
                    @if(is_array($meal->features))
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($meal->features as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p>{{ $meal->features }}</p>
                    @endif
                </div>
            </div>
        @endif

        @if($meal->includes)
            <div class="mt-4">
                <h2 class="text-sm font-semibold text-gray-900 mb-2">{{ trans('common.includes', [], 'auth') }}</h2>
                <div class="text-sm text-gray-600">
                    @if(is_array($meal->includes))
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($meal->includes as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p>{{ $meal->includes }}</p>
                    @endif
                </div>
            </div>
        @endif

        @if($meal->how_to_use)
            <div class="mt-4">
                <h2 class="text-sm font-semibold text-gray-900 mb-2">{{ trans('common.how_to_use', [], 'auth') }}</h2>
                <div class="text-sm text-gray-600">
                    @if(is_array($meal->how_to_use))
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($meal->how_to_use as $step)
                                <li>{{ $step }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p>{{ $meal->how_to_use }}</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@if($meal->reviews->count())
    <div class="mt-12">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ trans('common.reviews') }} ({{ $meal->reviews->count() }})</h2>
        <div class="space-y-4">
            @foreach($meal->reviews as $review)
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-900">
                            {{ $review->user->full_name ?? $review->user->username ?? 'User' }}
                        </span>
                        <div class="flex text-amber-400">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $review->rating)
                                    <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endif
                            @endfor
                        </div>
                        <span class="text-xs text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                    @if($review->comment)
                        <p class="mt-2 text-sm text-gray-600">{{ $review->comment }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

@if($relatedMeals->count())
    <div class="mt-12">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ trans('common.related_products') }}</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
            @foreach($relatedMeals as $related)
                @include('catalog.partials.meal-card', ['meal' => $related])
            @endforeach
        </div>
    </div>
@endif
@endsection
