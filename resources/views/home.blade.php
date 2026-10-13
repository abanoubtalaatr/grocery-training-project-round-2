@extends('layouts.app')

@section('title', trans('common.home'))

@section('content')
<div class="text-center py-12">
    <h1 class="text-4xl font-bold text-gray-900">{{ trans('common.welcome') }} {{ trans('common.app_name') }}</h1>
    <p class="mt-4 text-lg text-gray-600">{{ trans('common.app_name') }} - {{ trans('common.footer_support_text') }}</p>
</div>

@if($featuredCategories->count())
    <section class="mt-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">{{ trans('common.categories') }}</h2>
            <a href="{{ route('catalog.categories') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">{{ trans('common.view_all') }}</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($featuredCategories as $category)
                <a href="{{ route('catalog.category', $category->slug) }}" class="block bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden group">
                    <div class="aspect-[4/3] overflow-hidden bg-gray-100">
                        @if($category->image_url)
                            <img src="{{ $category->image_url }}" alt="{{ $category->localized_name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @endif
                    </div>
                    <div class="p-3">
                        <h3 class="text-sm font-semibold text-gray-900 group-hover:text-brand-600 transition">{{ $category->localized_name }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ trans('common.products_count', ['count' => $category->meals_count]) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif

@if($todayDeals->count())
    <section class="mt-12">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">{{ trans('common.today_deals') }}</h2>
            <a href="{{ route('catalog.today-deals') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">{{ trans('common.view_all') }}</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($todayDeals as $meal)
                @include('catalog.partials.meal-card', ['meal' => $meal])
            @endforeach
        </div>
    </section>
@endif

@if($recommendations->count())
    <section class="mt-12">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">{{ trans('common.recommendations') }}</h2>
            <a href="{{ route('catalog.recommendations') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">{{ trans('common.view_all') }}</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 gap-4">
            @foreach($recommendations as $meal)
                @include('catalog.partials.meal-card', ['meal' => $meal])
            @endforeach
        </div>
    </section>
@endif
@endsection
