@extends('layouts.learn')

@section('title', 'Session Auth')

@section('content')
    <div class="card prose">
        <span class="badge badge-session">Phase 1 · Session</span>
        <h2>Traditional Laravel website</h2>
        <p>
            The LMS starts as a Blade website. Students log in, the server creates a session,
            and the browser stores a session cookie. Every later request sends that cookie.
        </p>

        <h3>Flow</h3>
        <div class="flow">Student Login
↓
Laravel verifies credentials (email + password)
↓
Creates Session (SESSION_DRIVER=database)
↓
Stores session row in sessions table
↓
Browser receives Cookie (laravel_session)
↓
Every request sends Cookie → middleware auth</div>

        <h3>Use cases</h3>
        <ul>
            <li>Login / Logout / Remember Me (Breeze)</li>
            <li>Dashboard</li>
            <li>Course management (CRUD)</li>
            <li>Student profile</li>
        </ul>

        <h3>Try it</h3>
        <ol>
            <li>Open <a href="{{ route('login') }}">/login</a></li>
            <li>Login as <code class="inline">student@smartlearn.test</code> / <code class="inline">password</code></li>
            <li>Visit <a href="{{ route('dashboard') }}">/dashboard</a> and <a href="{{ route('courses.index') }}">/courses</a></li>
            <li>In DevTools → Application → Cookies, inspect <code class="inline">laravel_session</code></li>
            <li>Logout — cookie/session is invalidated</li>
        </ol>

        <div class="note">
            Session auth is <strong>stateful</strong>: the server must remember who you are.
            Perfect for first-party websites, not for third-party APIs.
        </div>
    </div>
@endsection
