@extends('layouts.app')

@section('title', trans('support.policies.title'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('support.index') }}" class="text-sm text-brand-600 hover:text-brand-700">
            &larr; {{ trans('common.back') }}
        </a>
    </div>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ trans('support.policies.heading') }}</h1>
    </div>

    @if($policies->isEmpty())
        <div class="text-center py-16">
            <svg class="mx-auto w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="mt-4 text-gray-500">{{ trans('support.policies.no_content') }}</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach($policies as $policy)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ $policy->title }}</h2>
                    <div class="prose prose-sm max-w-none text-gray-600">
                        {!! clean($policy->content) !!}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
