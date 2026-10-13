@extends('layouts.app')

@section('title', $subcategory->localized_name)

@section('content')
<nav class="mb-4 text-sm text-gray-500">
    <a href="{{ route('catalog.categories') }}" class="hover:text-brand-600">{{ trans('common.categories') }}</a>
    <span class="mx-1">/</span>
    <a href="{{ route('catalog.category', $subcategory->category->slug) }}" class="hover:text-brand-600">{{ $subcategory->category->localized_name }}</a>
    <span class="mx-1">/</span>
    <span class="text-gray-900">{{ $subcategory->localized_name }}</span>
</nav>

<div>
    <h1 class="text-2xl font-bold text-gray-900">{{ $subcategory->localized_name }}</h1>
    @if($subcategory->localized_description)
        <p class="mt-1 text-sm text-gray-600">{{ $subcategory->localized_description }}</p>
    @endif
    <p class="mt-1 text-xs text-gray-500">{{ trans('common.products_count', ['count' => $subcategory->meals_count]) }}</p>
</div>

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
