@extends('layouts.app')

@section('title', trans('support.contact.title'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('support.index') }}" class="text-sm text-brand-600 hover:text-brand-700">
            &larr; {{ trans('common.back') }}
        </a>
    </div>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ trans('support.contact.heading') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ trans('support.contact.subtitle') }}</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('support.contact.submit') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">{{ trans('support.contact.name') }} *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user?->full_name ?? '') }}" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                        placeholder="{{ trans('support.contact.name_placeholder') }}">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">{{ trans('support.contact.email') }} *</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user?->email ?? '') }}" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                        placeholder="{{ trans('support.contact.email_placeholder') }}">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">{{ trans('support.contact.phone') }}</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                        placeholder="{{ trans('support.contact.phone_placeholder') }}">
                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700">{{ trans('support.contact.subject') }} *</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                        placeholder="{{ trans('support.contact.subject_placeholder') }}">
                    @error('subject') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="message" class="block text-sm font-medium text-gray-700">{{ trans('support.contact.message') }} *</label>
                    <textarea name="message" id="message" rows="5" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                        placeholder="{{ trans('support.contact.message_placeholder') }}">{{ old('message') }}</textarea>
                    @error('message') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-brand-500 text-white rounded-lg text-sm font-medium hover:bg-brand-600 transition">
                    {{ trans('support.contact.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
