@extends('layouts.app')

@section('title', trans('auth.reset_password.title'))

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">{{ trans('auth.reset_password.heading') }}</h2>
            <p class="mt-2 text-sm text-gray-600">{{ trans('auth.reset_password.subtitle') }}</p>
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

        <form method="POST" action="{{ route('password.reset') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="identifier" class="block text-sm font-medium text-gray-700">{{ trans('auth.reset_password.identifier') }}</label>
                    <input type="text" name="identifier" id="identifier" value="{{ $identifier ?? old('identifier') }}" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>

                <div>
                    <label for="otp" class="block text-sm font-medium text-gray-700">{{ trans('auth.reset_password.otp') }}</label>
                    <input type="text" name="otp" id="otp" value="{{ old('otp') }}" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-center text-2xl tracking-widest">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">{{ trans('auth.reset_password.password') }}</label>
                    <input type="password" name="password" id="password" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ trans('auth.reset_password.password_confirmation') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>

                <button type="submit"
                    class="w-full bg-brand-500 text-white py-2 px-4 rounded-lg hover:bg-brand-600 transition font-medium">
                    {{ trans('auth.reset_password.submit') }}
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-gray-700">
                {{ trans('auth.reset_password.back_to_login') }}
            </a>
        </div>
    </div>
</div>
@endsection
