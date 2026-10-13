@extends('layouts.app')

@section('title', trans('auth.register.title'))

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">{{ trans('auth.register.heading') }}</h2>
            <p class="mt-2 text-sm text-gray-600">{{ trans('auth.register.subtitle') }}</p>
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

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">{{ trans('auth.register.username') }}</label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">{{ trans('auth.register.email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">{{ trans('auth.register.phone') }}</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">{{ trans('auth.register.password') }}</label>
                    <input type="password" name="password" id="password" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ trans('auth.register.password_confirmation') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>

                <button type="submit"
                    class="w-full bg-brand-500 text-white py-2 px-4 rounded-lg hover:bg-brand-600 transition font-medium">
                    {{ trans('auth.register.submit') }}
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                {{ trans('auth.register.already_have') }}
                <a href="{{ route('login') }}" class="text-brand-500 hover:text-brand-600 font-medium">
                    {{ trans('auth.register.sign_in') }}
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
