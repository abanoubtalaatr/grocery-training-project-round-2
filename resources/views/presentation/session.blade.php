@extends('layouts.learn')

@section('title', 'Session Auth')

@section('content')
    <header class="page-intro reveal">
        <span class="badge badge-session">Phase 1 · Session</span>
        <h1>The website remembers you with a cookie.</h1>
        <p>
            Traditional Blade LMS. Laravel checks email/password, creates a server session,
            and the browser keeps sending the cookie on every request.
        </p>
    </header>

    <section class="panel reveal">
        <h2 class="prose" style="font-family:var(--font-display);margin:0 0 .4rem;">How it flows</h2>
        <p style="margin:0 0 .5rem;color:var(--muted);">Follow the steps — this is the mental model for Session Auth.</p>

        <div class="flow-track">
            <div class="flow-step" data-tone="session">
                <div class="flow-step__index">1</div>
                <div>
                    <strong>Student opens /login</strong>
                    <span>Classic form. No Bearer token. Just email + password.</span>
                </div>
            </div>
            <div class="flow-step" data-tone="session">
                <div class="flow-step__index">2</div>
                <div>
                    <strong>Laravel verifies credentials</strong>
                    <span>Breeze + <code class="inline">auth</code> middleware guard the private pages.</span>
                </div>
            </div>
            <div class="flow-step" data-tone="session">
                <div class="flow-step__index">3</div>
                <div>
                    <strong>Session is stored on the server</strong>
                    <span><code class="inline">SESSION_DRIVER=database</code> → row in the sessions table.</span>
                </div>
            </div>
            <div class="flow-step" data-tone="session">
                <div class="flow-step__index">4</div>
                <div>
                    <strong>Browser receives cookie</strong>
                    <span>Look for <code class="inline">laravel_session</code> in DevTools → Application → Cookies.</span>
                </div>
            </div>
            <div class="flow-step" data-tone="session">
                <div class="flow-step__index">5</div>
                <div>
                    <strong>Every request sends the cookie</strong>
                    <span>Dashboard, courses, profile — all stateful and first-party.</span>
                </div>
            </div>
        </div>

        <div class="note">
            Session auth is <strong>stateful</strong>. Perfect for your own website. Not for third-party APIs.
        </div>
    </section>

    <section class="panel reveal">
        <h2 style="font-family:var(--font-display);margin:0 0 .7rem;">Try it in 60 seconds</h2>
        <ol class="checklist">
            <li>Open <a href="{{ route('login') }}">/login</a> and sign in as <code class="inline">student@smartlearn.test</code></li>
            <li>Visit <a href="{{ route('dashboard') }}">/dashboard</a> and <a href="{{ route('courses.index') }}">/courses</a></li>
            <li>Inspect the cookie, then logout — the session disappears</li>
        </ol>
        <div style="margin-top:1rem;">
            <a class="btn btn-primary" href="{{ route('login') }}">Go to Session Login</a>
        </div>
    </section>
@endsection
