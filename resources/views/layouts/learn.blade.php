<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Auth Lab') — SmartLearn LMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,650;9..144,700&family=IBM+Plex+Mono:wght@400;500&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/learn.css', 'resources/js/learn.js'])
</head>
<body class="learn-body">
    <div class="learn-shell">
        <header class="learn-topbar">
            <a class="brand-mark" href="{{ route('presentation.index') }}">
                <span class="brand-mark__glyph">S</span>
                <span class="brand-mark__text">
                    <strong>SmartLearn</strong>
                    <span>Auth Lab</span>
                </span>
            </a>

            <div class="top-actions">
                <a class="btn btn-ghost" href="{{ route('presentation.compare') }}">Compare</a>
                <a class="btn btn-ghost" href="{{ route('presentation.demos') }}">Demos</a>
                @auth
                    <a class="btn btn-primary" href="{{ route('dashboard') }}">Dashboard</a>
                @else
                    <a class="btn btn-primary" href="{{ route('login') }}">Try Session Login</a>
                @endauth
            </div>
        </header>

        <nav class="phase-rail" aria-label="Authentication phases">
            <a href="{{ route('presentation.session') }}" data-phase="session" @class(['active' => request()->routeIs('presentation.session')])>
                <span class="phase-rail__num">Phase 01</span>
                <span class="phase-rail__label">Session</span>
            </a>
            <a href="{{ route('presentation.sanctum') }}" data-phase="sanctum" @class(['active' => request()->routeIs('presentation.sanctum')])>
                <span class="phase-rail__num">Phase 02</span>
                <span class="phase-rail__label">Sanctum</span>
            </a>
            <a href="{{ route('presentation.passport') }}" data-phase="passport" @class(['active' => request()->routeIs('presentation.passport')])>
                <span class="phase-rail__num">Phase 03</span>
                <span class="phase-rail__label">Passport</span>
            </a>
            <a href="{{ route('presentation.jwt') }}" data-phase="jwt" @class(['active' => request()->routeIs('presentation.jwt')])>
                <span class="phase-rail__num">Phase 04</span>
                <span class="phase-rail__label">JWT</span>
            </a>
        </nav>

        <nav class="subnav" aria-label="Lab pages">
            <a href="{{ route('presentation.index') }}" @class(['active' => request()->routeIs('presentation.index')])>Overview</a>
            <a href="{{ route('presentation.compare') }}" @class(['active' => request()->routeIs('presentation.compare')])>Compare all</a>
            <a href="{{ route('presentation.demos') }}" @class(['active' => request()->routeIs('presentation.demos')])>7 demos</a>
        </nav>

        @yield('content')
    </div>
</body>
</html>
