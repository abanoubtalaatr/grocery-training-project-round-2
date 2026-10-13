@extends('layouts.app')

@section('title', trans('support.faqs.title'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('support.index') }}" class="text-sm text-brand-600 hover:text-brand-700">
            &larr; {{ trans('common.back') }}
        </a>
    </div>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ trans('support.faqs.heading') }}</h1>
    </div>

    {{-- Search & Filter --}}
    <div class="mb-6 flex flex-col sm:flex-row gap-4">
        <form method="GET" action="{{ route('support.faqs') }}" class="flex-1">
            <div class="flex gap-2">
                <input type="text" name="search" value="{{ $searchQuery }}" placeholder="{{ trans('support.faqs.search_placeholder') }}"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-medium hover:bg-brand-600 transition">
                    {{ trans('common.search') }}
                </button>
            </div>
        </form>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('support.faqs') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ !$selectedCategory ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                {{ trans('support.faqs.all_categories') }}
            </a>
            @foreach($categories as $category)
                <a href="{{ route('support.faqs', ['category' => $category]) }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ $selectedCategory === $category ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ $category }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- FAQ List --}}
    @if($faqs->isEmpty())
        <div class="text-center py-16">
            <svg class="mx-auto w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="mt-4 text-gray-500">{{ $searchQuery ? trans('support.faqs.no_results') : trans('support.faqs.no_faqs') }}</p>
            @if($searchQuery)
                <p class="mt-1 text-sm text-gray-400">{{ trans('support.faqs.try_different') }}</p>
            @endif
        </div>
    @else
        <div class="space-y-3" x-data="{ openFaq: null }">
            @foreach($faqs as $faq)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button @click="openFaq = openFaq === {{ $faq->id }} ? null : {{ $faq->id }}" class="w-full p-4 text-left flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $faq->question }}</p>
                            @if($faq->category)
                                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ $faq->category }}</span>
                            @endif
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': openFaq === {{ $faq->id }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openFaq === {{ $faq->id }}" x-collapse x-cloak>
                        <div class="px-4 pb-4 border-t border-gray-100">
                            <p class="mt-3 text-sm text-gray-600 whitespace-pre-line">{{ $faq->answer }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
