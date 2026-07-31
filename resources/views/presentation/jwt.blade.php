@extends('layouts.learn')

@section('title', 'JWT')

@section('content')
    <header class="page-intro reveal">
        <span class="badge badge-jwt">Phase 4 · JWT</span>
        <h1>A signed token that travels with the request.</h1>
        <p>
            Microservice style. No session cookie. No Sanctum row lookup for basic JWT.
            The server verifies the signature and expiry.
        </p>
    </header>

    <section class="panel reveal">
        <h2 style="font-family:var(--font-display);margin:0 0 .4rem;">How it flows</h2>
        <div class="flow-track">
            <div class="flow-step" data-tone="jwt">
                <div class="flow-step__index">1</div>
                <div>
                    <strong>POST /api/jwt/login</strong>
                    <span>Credentials in, signed JWT out.</span>
                </div>
            </div>
            <div class="flow-step" data-tone="jwt">
                <div class="flow-step__index">2</div>
                <div>
                    <strong>Client stores the JWT</strong>
                    <span>Usually only on the client — the token carries the claims.</span>
                </div>
            </div>
            <div class="flow-step" data-tone="jwt">
                <div class="flow-step__index">3</div>
                <div>
                    <strong>Authorization: Bearer JWT</strong>
                    <span>Profile endpoint trusts the signature + expiry.</span>
                </div>
            </div>
            <div class="flow-step" data-tone="jwt">
                <div class="flow-step__index">4</div>
                <div>
                    <strong>POST /api/jwt/refresh</strong>
                    <span>Rotate before expiry without asking for the password again.</span>
                </div>
            </div>
        </div>
    </section>

    <section class="panel reveal">
        <h2 style="font-family:var(--font-display);margin:0 0 .4rem;">Copy & try</h2>
        <div class="code-block">
            <div class="code-block__bar">
                <span>curl · JWT login + profile</span>
                <button type="button" class="copy-btn" data-copy="#jwt-login">Copy</button>
            </div>
            <pre id="jwt-login">curl -X POST {{ url('/api/jwt/login') }} \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"student@smartlearn.test","password":"password"}'

curl {{ url('/api/jwt/profile') }} \
  -H "Authorization: Bearer JWT_HERE" \
  -H "Accept: application/json"

curl -X POST {{ url('/api/jwt/refresh') }} \
  -H "Authorization: Bearer JWT_HERE" \
  -H "Accept: application/json"</pre>
        </div>
        <div class="note">
            Great for microservices. Harder to revoke instantly unless you add a denylist.
        </div>
    </section>
@endsection
