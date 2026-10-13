@extends('layouts.app')

@section('title', trans('common.products'))

@section('content')
<div>
    <h1 class="text-2xl font-bold text-gray-900">{{ trans('common.products') }}</h1>
</div>

<form method="GET" action="{{ route('catalog.meals') }}" class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">{{ trans('common.search') }}</label>
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ trans('common.search_placeholder') }}"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">{{ trans('common.all_categories') }}</label>
        <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            <option value="">{{ trans('common.all_categories') }}</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ (isset($filters['category_id']) && $filters['category_id'] == $category->id) ? 'selected' : '' }}>
                    {{ $category->localized_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">{{ trans('common.sort_by') }}</label>
        <select name="sort_by" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            <option value="created_at" {{ (isset($filters['sort_by']) && $filters['sort_by'] === 'created_at') ? 'selected' : '' }}>{{ trans('common.sort_newest') }}</option>
            <option value="price" {{ (isset($filters['sort_by']) && $filters['sort_by'] === 'price') ? 'selected' : '' }}>{{ trans('common.sort_price_low') }}</option>
            <option value="rating" {{ (isset($filters['sort_by']) && $filters['sort_by'] === 'rating') ? 'selected' : '' }}>{{ trans('common.sort_rating') }}</option>
            <option value="sold_count" {{ (isset($filters['sort_by']) && $filters['sort_by'] === 'sold_count') ? 'selected' : '' }}>{{ trans('common.sort_popular') }}</option>
        </select>
    </div>

    <div class="flex items-end gap-2">
        <button type="submit" class="bg-brand-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 transition">
            {{ trans('common.filter') }}
        </button>
        <a href="{{ route('catalog.meals') }}" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2">
            {{ trans('common.clear_filters') }}
        </a>
    </div>
</form>

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
