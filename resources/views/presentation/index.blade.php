@extends('layouts.learn')

@section('title', 'Overview')

@section('content')
    <section class="hero">
        <p style="opacity:.75;margin:0 0 .35rem;font-size:.85rem;letter-spacing:.08em;text-transform:uppercase;">SmartLearn LMS · Auth Lab</p>
        <h1>One Laravel app. Four authentication methods.</h1>
        <p>
            This project demonstrates <strong>Session</strong>, <strong>Sanctum</strong>,
            <strong>Passport (OAuth2)</strong>, and <strong>JWT</strong> with a Learning Management System
            similar to Udemy/Coursera — built for training, with live examples.
        </p>
    </section>

    <div class="grid" style="margin-bottom:1.5rem;">
        <a class="card" href="{{ route('presentation.session') }}" style="text-decoration:none;">
            <span class="badge badge-session">Phase 1</span>
            <h3>Session Auth</h3>
            <p>Traditional Blade website. Cookie + server session. Breeze login/logout/remember me.</p>
        </a>
        <a class="card" href="{{ route('presentation.sanctum') }}" style="text-decoration:none;">
            <span class="badge badge-sanctum">Phase 2</span>
            <h3>Sanctum</h3>
            <p>Own Flutter / SPA apps. Personal access tokens in <code class="inline">personal_access_tokens</code>.</p>
        </a>
        <a class="card" href="{{ route('presentation.passport') }}" style="text-decoration:none;">
            <span class="badge badge-passport">Phase 3</span>
            <h3>Passport OAuth2</h3>
            <p>Third-party apps (ABC University, Zoom, ExamPro) with Client ID, Secret, and scopes.</p>
        </a>
        <a class="card" href="{{ route('presentation.jwt') }}" style="text-decoration:none;">
            <span class="badge badge-jwt">Phase 4</span>
            <h3>JWT</h3>
            <p>Microservice-style API. Stateless Bearer JWT — no token row lookup for basic auth.</p>
        </a>
    </div>

    <div class="card prose">
        <h2>Demo accounts</h2>
        <p>Password for all: <code class="inline">password</code></p>
        <ul>
            <li><strong>admin@smartlearn.test</strong> — Admin</li>
            <li><strong>instructor@smartlearn.test</strong> — Instructor</li>
            <li><strong>student@smartlearn.test</strong> — Student</li>
        </ul>
        <p style="margin-top:1rem;">
            Start here:
            <a href="{{ route('login') }}">Session login</a> ·
            <a href="{{ route('presentation.demos') }}">API demo scripts</a> ·
            <a href="{{ route('presentation.compare') }}">Side-by-side comparison</a>
        </p>
    </div>
@endsection
