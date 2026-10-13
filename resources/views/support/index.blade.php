@extends('layouts.app')

@section('title', trans('support.title'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-gray-900">{{ trans('support.heading') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ trans('support.subtitle') }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ route('support.faqs') }}" class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ trans('support.quick_links.faqs') }}</p>
                    <p class="text-xs text-gray-500">{{ trans('support.faqs.title') }}</p>
                </div>
            </div>
        </a>

        <a href="{{ route('support.policies') }}" class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ trans('support.quick_links.policies') }}</p>
                    <p class="text-xs text-gray-500">{{ trans('support.policies.title') }}</p>
                </div>
            </div>
        </a>

        <a href="{{ route('support.contact') }}" class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ trans('support.quick_links.contact') }}</p>
                    <p class="text-xs text-gray-500">{{ trans('support.contact.title') }}</p>
                </div>
            </div>
        </a>

        <a href="{{ route('support.messages') }}" class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ trans('support.quick_links.my_messages') }}</p>
                    <p class="text-xs text-gray-500">{{ trans('support.messages.title') }}</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
