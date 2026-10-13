<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', trans('common.app_name'))</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen flex flex-col">
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-xl font-bold text-brand-600">
                        {{ trans('common.app_name') }}
                    </a>
                </div>

                <div class="hidden sm:flex items-center space-x-4">
                    <form method="GET" action="{{ route('catalog.search') }}" class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ trans('common.search_placeholder') }}"
                            class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>
                    </form>
                    <a href="{{ route('catalog.categories') }}" class="text-sm font-medium text-gray-700 hover:text-brand-600 transition">
                        {{ trans('common.categories') }}
                    </a>
                    <a href="{{ route('catalog.meals') }}" class="text-sm font-medium text-gray-700 hover:text-brand-600 transition">
                        {{ trans('common.products') }}
                    </a>
                </div>

                <div class="flex items-center space-x-3">
                    @auth
                        <a href="{{ route('cart.show') }}" class="relative text-gray-600 hover:text-brand-600 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                            </svg>
                            @php
                                $cartCount = auth()->user()->getOrCreateCart()->item_count ?? 0;
                            @endphp
                            @if($cartCount > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-4 h-4 rounded-full flex items-center justify-center font-bold">
                                    {{ $cartCount > 9 ? '9+' : $cartCount }}
                                </span>
                            @endif
                        </a>
                    @endauth

                    <a href="{{ route('locale.toggle', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
                        class="text-sm font-medium text-gray-600 hover:text-brand-600 transition">
                        {{ app()->getLocale() === 'ar' ? trans('common.english') : trans('common.arabic') }}
                    </a>

                    @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 text-sm font-medium text-gray-700 hover:text-brand-600 transition">
                                @if(auth()->user()->profile_image_url)
                                    <img src="{{ auth()->user()->profile_image_url }}" alt="" class="w-8 h-8 rounded-full object-cover">
                                @elseif(auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar }}" alt="" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <span class="w-8 h-8 rounded-full bg-brand-500 text-white flex items-center justify-center text-sm font-bold">
                                        {{ strtoupper(substr(auth()->user()->username ?? 'U', 0, 1)) }}
                                    </span>
                                @endif
                                <span class="hidden sm:inline">{{ auth()->user()->username ?? auth()->user()->name }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="open" @click.away="open = false" x-transition
                                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ trans('common.dashboard') }}
                                </a>
                                <a href="{{ route('ai-assistant') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ trans('common.ai_assistant') }}
                                </a>
                                <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ trans('common.profile') }}
                                </a>
                                <a href="{{ route('favorites.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ trans('common.favorites') }}
                                </a>
                                <a href="{{ route('smart-lists.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ trans('common.smart_lists') }}
                                </a>
                                <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ trans('common.my_orders') }}
                                </a>
                                <a href="{{ route('addresses.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ trans('common.manage_addresses') }}
                                </a>
                                <a href="{{ route('support.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ trans('common.help_support') }}
                                </a>
                                <a href="{{ route('sessions.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ trans('common.active_sessions') }}
                                </a>
                                <a href="{{ route('delete-account.confirm') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    {{ trans('common.delete_account') }}
                                </a>
                                <hr class="my-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        {{ trans('common.logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-brand-600 transition">
                            {{ trans('common.login') }}
                        </a>
                        <a href="{{ route('register') }}" class="text-sm font-medium bg-brand-500 text-white px-4 py-2 rounded-lg hover:bg-brand-600 transition">
                            {{ trans('common.register') }}
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('status'))
                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">{{ trans('common.footer_support') }}</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ trans('common.footer_support_text') }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">{{ trans('common.footer_quick_links') }}</h3>
                    <ul class="mt-2 space-y-1">
                        <li><a href="{{ route('support.index') }}" class="text-sm text-gray-600 hover:text-brand-600">{{ trans('common.footer_about') }}</a></li>
                        <li><a href="{{ route('support.contact') }}" class="text-sm text-gray-600 hover:text-brand-600">{{ trans('common.footer_contact') }}</a></li>
                        <li><a href="{{ route('support.faqs') }}" class="text-sm text-gray-600 hover:text-brand-600">{{ trans('common.footer_faq') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">{{ trans('common.footer_terms') }}</h3>
                    <ul class="mt-2 space-y-1">
                        <li><a href="{{ route('support.policies') }}" class="text-sm text-gray-600 hover:text-brand-600">{{ trans('common.footer_terms') }}</a></li>
                        <li><a href="{{ route('support.policies') }}" class="text-sm text-gray-600 hover:text-brand-600">{{ trans('common.footer_privacy') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-200 text-center">
                <p class="text-sm text-gray-500">
                    {{ trans('common.copyright', ['year' => date('Y'), 'name' => trans('common.app_name')]) }}
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @auth
    @if(config('services.firebase.project_id'))
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

        var firebaseConfig = {
            apiKey: "{{ config('services.firebase.api_key', '') }}",
            authDomain: "{{ config('services.firebase.auth_domain', '') }}",
            projectId: "{{ config('services.firebase.project_id', '') }}",
            storageBucket: "{{ config('services.firebase.storage_bucket', '') }}",
            messagingSenderId: "{{ config('services.firebase.messaging_sender_id', '') }}",
            appId: "{{ config('services.firebase.app_id', '') }}"
        };

        if (!firebaseConfig.apiKey || !firebaseConfig.projectId) return;

        try {
            firebase.initializeApp(firebaseConfig);
            var messaging = firebase.messaging();

            navigator.serviceWorker.register('/firebase-messaging-sw.js')
                .then(function (registration) {
                    messaging.useServiceWorker(registration);
                    return messaging.getToken({ vapidKey: "{{ config('services.firebase.vapid_key', '') }}" });
                })
                .then(function (token) {
                    if (!token) return;
                    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    fetch('/push-tokens', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ token: token, platform: 'web' })
                    }).catch(function () {});
                })
                .catch(function () {});

            messaging.onMessage(function (payload) {
                if (payload.notification) {
                    new Notification(payload.notification.title || 'Grocery App', {
                        body: payload.notification.body || '',
                        icon: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50" fill="%2310B981"/><text x="50" y="68" font-size="50" text-anchor="middle" fill="white" font-family="sans-serif" font-weight="bold">G</text></svg>'
                    });
                }
            });
        } catch (e) {}
    });
    </script>
    @endif
    @endauth
    @stack('scripts')
</body>
</html>
