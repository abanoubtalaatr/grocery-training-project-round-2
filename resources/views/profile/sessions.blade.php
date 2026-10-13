@extends('layouts.app')

@section('title', trans('auth.sessions.title'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900">{{ trans('auth.sessions.heading') }}</h2>
            <p class="mt-2 text-sm text-gray-600">{{ trans('auth.sessions.subtitle') }}</p>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-4">
            @forelse($sessions as $session)
                <div class="border border-gray-200 rounded-lg p-4 {{ $session->id === $currentSessionId ? 'border-brand-300 bg-brand-50' : '' }}">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $session->user_agent ? substr($session->user_agent, 0, 60) . '...' : 'Unknown Device' }}
                                </span>
                                @if($session->id === $currentSessionId)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-brand-100 text-brand-800">
                                        {{ trans('auth.sessions.this_device') }}
                                    </span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-gray-400">{{ $session->ip_address }}</p>
                            <p class="mt-1 text-xs text-gray-400">
                                {{ trans('auth.sessions.last_active', ['time' => \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans()]) }}
                            </p>
                        </div>

                        @if($session->id !== $currentSessionId)
                            <form method="POST" action="{{ route('sessions.destroy', $session->id) }}"
                                x-data="{ confirmed: false }"
                                @submit.prevent="if(confirmed) $el.submit(); else if(confirm('{{ trans('auth.sessions.revoke_confirm') }}')) { confirmed = true; $el.submit(); }">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                                    {{ trans('auth.sessions.revoke') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 text-center py-4">{{ trans('common.no_results') }}</p>
            @endforelse
        </div>

        @if($sessions->count() > 1)
            <div class="mt-8 pt-6 border-t border-gray-200">
                <form method="POST" action="{{ route('sessions.destroy-others') }}"
                    x-data="{ showPassword: false, confirmed: false }"
                    @submit.prevent="if(confirmed) $el.submit(); else if(confirm('{{ trans('auth.sessions.revoke_others_confirm') }}')) { confirmed = true; $el.submit(); }">
                    @csrf
                    @method('DELETE')

                    <div x-show="showPassword" class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">{{ trans('auth.delete_account.password') }}</label>
                        <input type="password" name="password" id="password" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <button type="button" @click="showPassword = !showPassword; if(!showPassword) $el.closest('form').submit();"
                        class="text-sm text-red-600 hover:text-red-800 font-medium">
                        {{ trans('auth.sessions.revoke_others') }}
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
