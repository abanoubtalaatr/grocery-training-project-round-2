@extends('layouts.app')

@section('title', trans('orders.profile.image_title'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('profile.show') }}" class="text-sm text-brand-600 hover:text-brand-700">
            &larr; {{ trans('common.back') }}
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ trans('orders.profile.image_heading') }}</h1>

        <div class="text-center mb-8">
            <div class="w-32 h-32 mx-auto rounded-full bg-gray-100 flex items-center justify-center overflow-hidden border-4 border-white shadow-lg">
                @if($user->profile_image_url)
                    <img src="{{ $user->profile_image_url }}" alt="{{ $user->full_name }}" class="w-32 h-32 rounded-full object-cover">
                @elseif($user->avatar)
                    <img src="{{ $user->avatar }}" alt="{{ $user->full_name }}" class="w-32 h-32 rounded-full object-cover">
                @else
                    <span class="text-4xl font-bold text-gray-400">{{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}</span>
                @endif
            </div>
        </div>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.image.upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="profile_image" class="block text-sm font-medium text-gray-700">{{ trans('orders.profile.select_image') }}</label>
                    <input type="file" name="profile_image" id="profile_image" accept="image/jpeg,image/png,image/jpg,image/gif" required
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    <p class="mt-1 text-xs text-gray-500">{{ trans('orders.profile.image_rules') }}</p>
                </div>

                <button type="submit" class="bg-brand-500 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 transition">
                    {{ trans('orders.profile.upload_image') }}
                </button>
            </div>
        </form>

        @if($user->profile_image || $user->avatar)
            <div class="mt-8 pt-6 border-t border-gray-200">
                <form method="POST" action="{{ route('profile.image.delete') }}"
                    x-data="{ confirmed: false }"
                    @submit.prevent="if(confirmed) $el.submit(); else if(confirm('{{ trans('orders.profile.delete_image_confirm') }}')) { confirmed = true; $el.submit(); }">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                        {{ trans('orders.profile.delete_image') }}
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
