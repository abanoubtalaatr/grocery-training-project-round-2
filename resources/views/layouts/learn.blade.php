<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SmartLearn Auth Lab') — SmartLearn LMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --ink: #0f172a;
            --muted: #475569;
            --line: #e2e8f0;
            --session: #0f766e;
            --sanctum: #1d4ed8;
            --passport: #b45309;
            --jwt: #7e22ce;
            --bg: #f8fafc;
        }
        body { background: var(--bg); color: var(--ink); }
        .learn-shell { max-width: 1100px; margin: 0 auto; padding: 1.5rem; }
        .learn-nav { display: flex; flex-wrap: wrap; gap: .75rem; margin-bottom: 1.5rem; }
        .learn-nav a {
            padding: .45rem .85rem; border: 1px solid var(--line); border-radius: .5rem;
            background: white; font-size: .875rem; color: var(--ink); text-decoration: none;
        }
        .learn-nav a.active { background: var(--ink); color: white; border-color: var(--ink); }
        .hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #0f766e 100%);
            color: white; border-radius: 1rem; padding: 2rem; margin-bottom: 1.5rem;
        }
        .hero h1 { font-size: clamp(1.6rem, 3vw, 2.4rem); font-weight: 700; margin: 0 0 .5rem; }
        .hero p { opacity: .9; max-width: 40rem; }
        .grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        .card {
            background: white; border: 1px solid var(--line); border-radius: .85rem; padding: 1.1rem;
        }
        .card h3 { margin: 0 0 .4rem; font-size: 1.05rem; }
        .card p { margin: 0; color: var(--muted); font-size: .9rem; line-height: 1.5; }
        .badge {
            display: inline-block; font-size: .7rem; font-weight: 700; letter-spacing: .04em;
            text-transform: uppercase; padding: .2rem .5rem; border-radius: .35rem; color: white; margin-bottom: .5rem;
        }
        .badge-session { background: var(--session); }
        .badge-sanctum { background: var(--sanctum); }
        .badge-passport { background: var(--passport); }
        .badge-jwt { background: var(--jwt); }
        .prose h2 { font-size: 1.4rem; margin: 0 0 .75rem; }
        .prose h3 { font-size: 1.05rem; margin: 1.25rem 0 .5rem; }
        .prose p, .prose li { color: var(--muted); line-height: 1.6; }
        .flow {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            background: #0f172a; color: #e2e8f0; padding: 1rem; border-radius: .75rem;
            white-space: pre-wrap; font-size: .82rem; line-height: 1.55; overflow-x: auto;
        }
        .table-wrap { overflow-x: auto; }
        table.compare { width: 100%; border-collapse: collapse; background: white; border-radius: .75rem; overflow: hidden; }
        table.compare th, table.compare td { border: 1px solid var(--line); padding: .7rem .8rem; text-align: left; font-size: .9rem; }
        table.compare th { background: #f1f5f9; }
        .note { background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; padding: .8rem 1rem; border-radius: .65rem; font-size: .9rem; }
        code.inline { background: #e2e8f0; padding: .1rem .35rem; border-radius: .3rem; font-size: .85em; }
    </style>
</head>
<body>
    <div class="learn-shell">
        <nav class="learn-nav">
            <a href="{{ route('presentation.index') }}" @class(['active' => request()->routeIs('presentation.index')])>Overview</a>
            <a href="{{ route('presentation.session') }}" @class(['active' => request()->routeIs('presentation.session')])>1. Session</a>
            <a href="{{ route('presentation.sanctum') }}" @class(['active' => request()->routeIs('presentation.sanctum')])>2. Sanctum</a>
            <a href="{{ route('presentation.passport') }}" @class(['active' => request()->routeIs('presentation.passport')])>3. Passport</a>
            <a href="{{ route('presentation.jwt') }}" @class(['active' => request()->routeIs('presentation.jwt')])>4. JWT</a>
            <a href="{{ route('presentation.compare') }}" @class(['active' => request()->routeIs('presentation.compare')])>Compare</a>
            <a href="{{ route('presentation.demos') }}" @class(['active' => request()->routeIs('presentation.demos')])>Demo Scripts</a>
            @auth
                <a href="{{ route('dashboard') }}">Dashboard</a>
            @else
                <a href="{{ route('login') }}">Login (Session)</a>
            @endauth
        </nav>

        @yield('content')
    </div>
</body>
</html>
