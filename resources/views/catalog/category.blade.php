@extends('layouts.app')

@section('title', $category->localized_name)

@section('content')
<nav class="mb-4 text-sm text-gray-500">
    <a href="{{ route('catalog.categories') }}" class="hover:text-brand-600">{{ trans('common.categories') }}</a>
    <span class="mx-1">/</span>
    <span class="text-gray-900">{{ $category->localized_name }}</span>
</nav>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $category->localized_name }}</h1>
        @if($category->localized_description)
            <p class="mt-1 text-sm text-gray-600">{{ $category->localized_description }}</p>
        @endif
        <p class="mt-1 text-xs text-gray-500">{{ trans('common.products_count', ['count' => $category->meals_count]) }}</p>
    </div>

    <form method="GET" action="{{ route('catalog.category', $category->slug) }}" class="flex items-center gap-2">
        <select name="sort_by" onchange="this.form.submit()" class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>{{ trans('common.sort_newest') }}</option>
            <option value="price" {{ request('sort_by') === 'price' ? 'selected' : '' }}>{{ trans('common.sort_price_low') }}</option>
            <option value="rating" {{ request('sort_by') === 'rating' ? 'selected' : '' }}>{{ trans('common.sort_rating') }}</option>
            <option value="sold_count" {{ request('sort_by') === 'sold_count' ? 'selected' : '' }}>{{ trans('common.sort_popular') }}</option>
        </select>
    </form>
</div>

@if($subcategories->count())
    <div class="mt-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ trans('common.subcategories') }}</h2>
        <div class="flex flex-wrap gap-2">
            @foreach($subcategories as $subcategory)
                <a href="{{ route('catalog.subcategory', $subcategory->slug) }}"
                    class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:border-brand-500 hover:text-brand-600 transition">
                    @if($subcategory->image_url)
                        <img src="{{ $subcategory->image_url }}" alt="" class="w-6 h-6 rounded object-cover">
                    @endif
                    {{ $subcategory->localized_name }}
                    <span class="text-xs text-gray-400">({{ $subcategory->meals()->available()->count() }})</span>
                </a>
            @endforeach
        </div>
    </div>
@endif

<div class="mt-8">
    @if($meals->count())
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @foreach($meals as $meal)
                @include('catalog.partials.meal-card', ['meal' => $meal])
            @endforeach
        </div>

        <div class="mt-8">
            {{ $meals->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500">{{ trans('common.no_products') }}</p>
        </div>
    @endif
</div>
@endsection
