@extends('layouts.app')

@section('title', trans('auth.login.title'))

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">{{ trans('auth.login.heading') }}</h2>
            <p class="mt-2 text-sm text-gray-600">{{ trans('auth.login.subtitle') }}</p>
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

        <a href="{{ route('google.redirect') }}"
            class="w-full flex items-center justify-center gap-3 bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-50 transition font-medium mb-4">
            <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
            {{ trans('auth.login.google_sign_in') }}
        </a>

        <div class="relative my-4">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">{{ trans('auth.login.or') }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="login" class="block text-sm font-medium text-gray-700">{{ trans('auth.login.login') }}</label>
                    <input type="text" name="login" id="login" value="{{ old('login') }}" required autofocus
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">{{ trans('auth.login.password') }}</label>
                    <input type="password" name="password" id="password" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>

                <div class="flex items-center justify-end">
                    <a href="{{ route('password.forgot.form') }}" class="text-sm text-brand-500 hover:text-brand-600">
                        {{ trans('auth.login.forgot_password') }}
                    </a>
                </div>

                <button type="submit"
                    class="w-full bg-brand-500 text-white py-2 px-4 rounded-lg hover:bg-brand-600 transition font-medium">
                    {{ trans('auth.login.submit') }}
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                {{ trans('auth.login.no_account') }}
                <a href="{{ route('register') }}" class="text-brand-500 hover:text-brand-600 font-medium">
                    {{ trans('auth.login.create_account') }}
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
