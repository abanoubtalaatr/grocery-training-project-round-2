@extends('layouts.app')

@section('title', trans('common.search_results'))

@section('content')
<div>
    <h1 class="text-2xl font-bold text-gray-900">
        @if($search)
            {{ trans('common.search_for', ['query' => $search]) }}
        @else
            {{ trans('common.search_results') }}
        @endif
    </h1>
    @if($meals->total())
        <p class="mt-1 text-sm text-gray-600">{{ trans('common.products_count', ['count' => $meals->total()]) }}</p>
    @endif
</div>

<form method="GET" action="{{ route('catalog.search') }}" class="mt-6">
    <div class="flex gap-2">
        <input type="text" name="search" value="{{ $search }}" placeholder="{{ trans('common.search_placeholder') }}"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        <button type="submit" class="bg-brand-500 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 transition">
            {{ trans('common.search') }}
        </button>
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
            <p class="text-gray-500">{{ $search ? trans('common.no_search_results', ['query' => $search]) : trans('common.no_products') }}</p>
            @if($search)
                <p class="mt-2 text-sm text-gray-400">{{ trans('common.try_different') }}</p>
            @endif
        </div>
    @endif
</div>
@endsection
