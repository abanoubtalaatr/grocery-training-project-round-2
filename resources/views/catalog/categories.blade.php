@extends('layouts.app')

@section('title', trans('common.categories'))

@section('content')
<div>
    <h1 class="text-2xl font-bold text-gray-900">{{ trans('common.categories') }}</h1>
    <p class="mt-1 text-sm text-gray-600">{{ trans('common.products_count', ['count' => $categories->sum('meals_count')]) }}</p>
</div>

<div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
    @forelse($categories as $category)
        <a href="{{ route('catalog.category', $category->slug) }}" class="block bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden group">
            <div class="aspect-[4/3] overflow-hidden bg-gray-100">
                @if($category->image_url)
                    <img src="{{ $category->image_url }}" alt="{{ $category->localized_name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                @endif
            </div>
            <div class="p-3">
                <h3 class="text-sm font-semibold text-gray-900 group-hover:text-brand-600 transition">{{ $category->localized_name }}</h3>
                <p class="mt-1 text-xs text-gray-500">{{ trans('common.products_count', ['count' => $category->meals_count]) }}</p>
            </div>
        </a>
    @empty
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500">{{ trans('common.no_results') }}</p>
        </div>
    @endforelse
</div>
@endsection
