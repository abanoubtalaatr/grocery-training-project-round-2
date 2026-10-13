@extends('layouts.app')

@section('title', trans('auth.delete_account.title'))

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8 border border-red-200">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-red-600">{{ trans('auth.delete_account.heading') }}</h2>
            <p class="mt-2 text-sm text-gray-600">{{ trans('auth.delete_account.subtitle') }}</p>
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

        <form method="POST" action="{{ route('delete-account') }}" x-data="{ confirmed: false }"
            @submit.prevent="if(confirmed) $el.submit(); else if(confirm('{{ trans('auth.delete_account.heading') }}? This cannot be undone.')) { confirmed = true; $el.submit(); }">
            @csrf
            @method('DELETE')
            <div class="space-y-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">{{ trans('auth.delete_account.password') }}</label>
                    <input type="password" name="password" id="password" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>

                <button type="submit"
                    class="w-full bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition font-medium">
                    {{ trans('auth.delete_account.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
