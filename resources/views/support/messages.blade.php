@extends('layouts.app')

@section('title', trans('support.messages.title'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('support.index') }}" class="text-sm text-brand-600 hover:text-brand-700">
            &larr; {{ trans('common.back') }}
        </a>
    </div>

    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ trans('support.messages.heading') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ trans('support.messages.subtitle') }}</p>
        </div>
        <a href="{{ route('support.contact') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-medium hover:bg-brand-600 transition">
            {{ trans('support.contact.submit') }}
        </a>
    </div>

    @if($messages->isEmpty())
        <div class="text-center py-16">
            <svg class="mx-auto w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            <p class="mt-4 text-gray-500">{{ trans('support.messages.no_messages') }}</p>
            <a href="{{ route('support.contact') }}" class="mt-4 inline-block bg-brand-500 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 transition">
                {{ trans('support.messages.submit_first') }}
            </a>
        </div>
    @else
        @php
            $statusColors = [
                'new' => 'bg-blue-100 text-blue-800',
                'read' => 'bg-gray-100 text-gray-800',
                'replied' => 'bg-green-100 text-green-800',
                'spam' => 'bg-red-100 text-red-800',
            ];
            $statusLabels = [
                'new' => trans('support.messages.status_new'),
                'read' => trans('support.messages.status_read'),
                'replied' => trans('support.messages.status_replied'),
                'spam' => trans('support.messages.status_spam'),
            ];
        @endphp
        <div class="space-y-3">
            @foreach($messages as $msg)
                <a href="{{ route('support.messages.show', $msg->id) }}" class="block bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-gray-900">{{ $msg->subject }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$msg->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabels[$msg->status] ?? $msg->status }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ Str::limit($msg->message, 100) }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-xs text-gray-400">{{ $msg->created_at->format('M d, Y') }}</p>
                            @if($msg->replied_at)
                                <p class="mt-1 text-xs text-green-600">{{ trans('support.messages.replied_on', ['date' => $msg->replied_at->format('M d')]) }}</p>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $messages->links() }}
        </div>
    @endif
</div>
@endsection
