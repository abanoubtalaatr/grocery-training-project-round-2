@extends('layouts.app')

@section('title', trans('smart_lists.title'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ trans('smart_lists.heading') }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ trans('smart_lists.subtitle') }}</p>
        </div>
        <a href="{{ route('smart-lists.create') }}" class="bg-brand-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 transition">
            {{ trans('smart_lists.create_new') }}
        </a>
    </div>

    @if($smartLists->isEmpty())
        <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-200">
            <svg class="mx-auto w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <p class="mt-4 text-gray-500">{{ trans('smart_lists.empty') }}</p>
            <a href="{{ route('smart-lists.create') }}" class="mt-4 inline-block bg-brand-500 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 transition">
                {{ trans('smart_lists.create_first') }}
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($smartLists as $list)
                <a href="{{ route('smart-lists.show', $list->id) }}" class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $list->name }}</h3>
                            @if($list->category)
                                <p class="mt-1 text-xs text-brand-600 bg-brand-50 inline-block px-2 py-0.5 rounded">{{ $list->category }}</p>
                            @endif
                        </div>
                    </div>

                    @if($list->description)
                        <p class="mt-2 text-sm text-gray-500 line-clamp-2">{{ $list->description }}</p>
                    @endif

                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            {{ $list->meals_count ?? $list->meals->count() }} {{ trans('smart_lists.items_count') }}
                        </span>
                        <div class="flex items-center gap-1">
                            @if($list->notify_on_price_drop)
                                <span class="text-xs text-green-600 bg-green-50 px-1.5 py-0.5 rounded" title="{{ trans('smart_lists.price_drop_enabled') }}">
                                    <svg class="w-3 h-3 inline" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"/></svg>
                                </span>
                            @endif
                            @if($list->notify_on_offers)
                                <span class="text-xs text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded" title="{{ trans('smart_lists.offers_enabled') }}">
                                    <svg class="w-3 h-3 inline" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
