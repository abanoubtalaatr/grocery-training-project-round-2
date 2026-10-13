@extends('layouts.app')

@section('title', trans('smart_lists.create_title'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('smart-lists.index') }}" class="text-sm text-brand-600 hover:text-brand-700">
            &larr; {{ trans('common.back') }}
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ trans('smart_lists.create_heading') }}</h1>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('smart-lists.store') }}">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">{{ trans('smart_lists.name') }} *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        placeholder="{{ trans('smart_lists.name_placeholder') }}"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">{{ trans('smart_lists.category') }}</label>
                    <input type="text" name="category" id="category" value="{{ old('category') }}"
                        placeholder="{{ trans('smart_lists.category_placeholder') }}"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">{{ trans('smart_lists.description') }}</label>
                    <textarea name="description" id="description" rows="3" maxlength="255"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">{{ old('description') }}</textarea>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ trans('smart_lists.notification_settings') }}</h3>

                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input type="hidden" name="notify_on_price_drop" value="0">
                            <input type="checkbox" name="notify_on_price_drop" id="notify_on_price_drop" value="1"
                                {{ old('notify_on_price_drop', '1') === '1' ? 'checked' : '' }}
                                class="h-4 w-4 text-brand-500 focus:ring-brand-500 border-gray-300 rounded">
                            <label for="notify_on_price_drop" class="ml-2 text-sm text-gray-700">{{ trans('smart_lists.notify_price_drop') }}</label>
                        </div>

                        <div class="flex items-center">
                            <input type="hidden" name="notify_on_offers" value="0">
                            <input type="checkbox" name="notify_on_offers" id="notify_on_offers" value="1"
                                {{ old('notify_on_offers', '1') === '1' ? 'checked' : '' }}
                                class="h-4 w-4 text-brand-500 focus:ring-brand-500 border-gray-300 rounded">
                            <label for="notify_on_offers" class="ml-2 text-sm text-gray-700">{{ trans('smart_lists.notify_offers') }}</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center gap-4">
                <button type="submit" class="bg-brand-500 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 transition">
                    {{ trans('smart_lists.create_list') }}
                </button>
                <a href="{{ route('smart-lists.index') }}" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                    {{ trans('common.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
