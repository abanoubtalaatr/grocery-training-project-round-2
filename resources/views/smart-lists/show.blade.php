@extends('layouts.app')

@section('title', $smartList->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('smart-lists.index') }}" class="text-sm text-brand-600 hover:text-brand-700">
            &larr; {{ trans('common.back') }}
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $smartList->name }}</h1>
                    @if($smartList->category)
                        <p class="mt-1 text-sm text-brand-600">{{ $smartList->category }}</p>
                    @endif
                    @if($smartList->description)
                        <p class="mt-2 text-sm text-gray-500">{{ $smartList->description }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    @if($smartList->notify_on_price_drop)
                        <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">{{ trans('smart_lists.price_drop_on') }}</span>
                    @endif
                    @if($smartList->notify_on_offers)
                        <span class="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded-full">{{ trans('smart_lists.offers_on') }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">
                    {{ trans('smart_lists.list_items', ['count' => $smartList->meals_count]) }}
                </h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('smart-lists.edit', $smartList->id) }}" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                        {{ trans('common.edit') }}
                    </a>
                    <form method="POST" action="{{ route('smart-lists.destroy', $smartList->id) }}"
                        x-data="{ confirmed: false }"
                        @submit.prevent="if(confirmed) $el.submit(); else if(confirm('{{ trans('smart_lists.delete_confirm') }}')) { confirmed = true; $el.submit(); }">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                            {{ trans('common.delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if($smartList->meals->isNotEmpty())
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($smartList->meals as $meal)
                        <div class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 transition">
                            <a href="{{ route('catalog.meal', $meal->slug ?? $meal->id) }}" class="flex-shrink-0">
                                @if($meal->image_url)
                                    <img src="{{ $meal->image_url }}" alt="{{ $meal->localized_title }}" class="w-14 h-14 rounded-lg object-cover">
                                @else
                                    <div class="w-14 h-14 rounded-lg bg-gray-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                            </a>

                            <div class="flex-1 min-w-0">
                                <a href="{{ route('catalog.meal', $meal->slug ?? $meal->id) }}">
                                    <h4 class="text-sm font-medium text-gray-900 truncate hover:text-brand-600 transition">{{ $meal->localized_title }}</h4>
                                </a>
                                @if($meal->category)
                                    <p class="text-xs text-gray-500">{{ $meal->category->localized_name }}</p>
                                @endif
                                <div class="flex items-baseline gap-2 mt-1">
                                    @if($meal->discount_price !== null)
                                        <span class="text-brand-600 font-bold text-sm">${{ number_format((float) $meal->discount_price, 2) }}</span>
                                        <span class="text-gray-400 line-through text-xs">${{ number_format((float) $meal->price, 2) }}</span>
                                    @else
                                        <span class="text-brand-600 font-bold text-sm">${{ number_format((float) $meal->price, 2) }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                @if($meal->is_available && $meal->isInStock())
                                    <form method="POST" action="{{ route('cart.addItem') }}">
                                        @csrf
                                        <input type="hidden" name="meal_id" value="{{ $meal->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="text-xs text-brand-600 hover:text-brand-700 font-medium border border-brand-200 px-2 py-1 rounded hover:bg-brand-50 transition">
                                            {{ trans('smart_lists.add_to_cart') }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">{{ trans('common.out_of_stock') }}</span>
                                @endif

                                <form method="POST" action="{{ route('smart-lists.meals.remove', [$smartList->id, $meal->id]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium" title="{{ trans('smart_lists.remove_from_list') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200">
                    <form method="POST" action="{{ route('smart-lists.addToCart', $smartList->id) }}">
                        @csrf
                        <button type="submit" class="w-full bg-brand-500 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-brand-600 transition">
                            {{ trans('smart_lists.add_all_to_cart') }}
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="p-6 text-center">
                <p class="text-gray-500 text-sm">{{ trans('smart_lists.list_empty') }}</p>
                <p class="mt-2 text-sm text-gray-400">{{ trans('smart_lists.add_products_hint') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
