@extends('layouts.learn')

@section('title', 'Overview')

@section('content')
    <section class="hero">
        <h1 class="hero__brand">SmartLearn</h1>
        <p class="hero__title">Learn authentication by watching it work.</p>
        <p class="hero__lead">
            One Laravel LMS. Four real methods — Session, Sanctum, Passport, and JWT —
            explained with flows you can click and APIs you can call.
        </p>
        <div class="hero__cta">
            <a class="btn btn-primary" href="{{ route('presentation.session') }}">Start with Session</a>
            <a class="btn btn-ghost" href="{{ route('login') }}">Open live login</a>
        </div>
    </section>

    <section class="section">
        <div class="section-head reveal">
            <h2>Pick a phase</h2>
            <p>Each one answers: who is the client, and what do they send?</p>
        </div>

        <div class="phase-grid stagger">
            <a class="phase-card reveal" data-phase="session" href="{{ route('presentation.session') }}">
                <div class="phase-card__meta">
                    <span class="badge badge-session">Phase 1</span>
                    <span class="phase-card__arrow">→</span>
                </div>
                <h3>Session</h3>
                <p>Blade website. Cookie + server session. Best for students in the browser.</p>
            </a>

            <a class="phase-card reveal" data-phase="sanctum" href="{{ route('presentation.sanctum') }}">
                <div class="phase-card__meta">
                    <span class="badge badge-sanctum">Phase 2</span>
                    <span class="phase-card__arrow">→</span>
                </div>
                <h3>Sanctum</h3>
                <p>Your Flutter / SPA apps. Personal access tokens you fully control.</p>
            </a>

            <a class="phase-card reveal" data-phase="passport" href="{{ route('presentation.passport') }}">
                <div class="phase-card__meta">
                    <span class="badge badge-passport">Phase 3</span>
                    <span class="phase-card__arrow">→</span>
                </div>
                <h3>Passport</h3>
                <p>Third-party systems. Client ID, secret, scopes, and an approval screen.</p>
            </a>

            <a class="phase-card reveal" data-phase="jwt" href="{{ route('presentation.jwt') }}">
                <div class="phase-card__meta">
                    <span class="badge badge-jwt">Phase 4</span>
                    <span class="phase-card__arrow">→</span>
                </div>
                <h3>JWT</h3>
                <p>Microservice style. Signed token, mostly stateless, easy to pass around.</p>
            </a>
        </div>
    </section>

    <section class="panel reveal">
        <div class="section-head">
            <h2>Demo accounts</h2>
            <p>Password for everyone: <code class="inline">password</code></p>
        </div>
        <div class="accounts">
            <div class="account">
                <strong>Admin</strong>
                <code>admin@smartlearn.test</code>
            </div>
            <div class="account">
                <strong>Instructor</strong>
                <code>instructor@smartlearn.test</code>
            </div>
            <div class="account">
                <strong>Student</strong>
                <code>student@smartlearn.test</code>
            </div>
        </div>
    </section>
@endsection
