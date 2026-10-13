@extends('layouts.app')

@section('title', trans('support.messages.title') . ' - ' . $message->subject)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('support.messages') }}" class="text-sm text-brand-600 hover:text-brand-700">
            &larr; {{ trans('common.back') }}
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Header --}}
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ $message->subject }}</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ trans('support.messages.submitted_on', ['date' => $message->created_at->format('M d, Y \a\t g:i A')]) }}
                    </p>
                </div>
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
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$message->status] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ $statusLabels[$message->status] ?? $message->status }}
                </span>
            </div>
        </div>

        {{-- Your Message --}}
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ trans('support.messages.your_message') }}</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $message->message }}</p>
            </div>
        </div>

        {{-- Reply from Support --}}
        @if($message->reply)
            <div class="p-6 border-b border-gray-200 bg-green-50">
                <h3 class="text-sm font-semibold text-green-800 mb-3">{{ trans('support.messages.reply_from_support') }}</h3>
                <div class="bg-white rounded-lg p-4 border border-green-200">
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $message->reply }}</p>
                </div>
                <p class="mt-2 text-xs text-green-600">
                    {{ trans('support.messages.replied_on', ['date' => $message->replied_at->format('M d, Y \a\t g:i A')]) }}
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
