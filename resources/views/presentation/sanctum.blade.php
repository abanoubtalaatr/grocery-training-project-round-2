@extends('layouts.learn')

@section('title', 'Sanctum')

@section('content')
    <header class="page-intro reveal">
        <span class="badge badge-sanctum">Phase 2 · Sanctum</span>
        <h1>Your mobile app gets a personal token.</h1>
        <p>
            Same company, same backend. Flutter / SPA login creates a Sanctum personal access token
            stored in <code class="inline">personal_access_tokens</code>.
        </p>
    </header>

    <section class="panel reveal">
        <h2 style="font-family:var(--font-display);margin:0 0 .4rem;">How it flows</h2>
        <div class="flow-track">
            <div class="flow-step" data-tone="sanctum">
                <div class="flow-step__index">1</div>
                <div>
                    <strong>POST /api/sanctum/login</strong>
                    <span>App sends email + password as JSON.</span>
                </div>
            </div>
            <div class="flow-step" data-tone="sanctum">
                <div class="flow-step__index">2</div>
                <div>
                    <strong>createSanctumToken('mobile')</strong>
                    <span>Hashed token lands in <code class="inline">personal_access_tokens</code>.</span>
                </div>
            </div>
            <div class="flow-step" data-tone="sanctum">
                <div class="flow-step__index">3</div>
                <div>
                    <strong>API returns plain-text token once</strong>
                    <span>Mobile stores it securely (Keychain / Secure Storage).</span>
                </div>
            </div>
            <div class="flow-step" data-tone="sanctum">
                <div class="flow-step__index">4</div>
                <div>
                    <strong>Authorization: Bearer TOKEN</strong>
                    <span>Profile + courses endpoints require this header.</span>
                </div>
            </div>
        </div>
    </section>

    <section class="panel reveal">
        <h2 style="font-family:var(--font-display);margin:0 0 .4rem;">Copy & try</h2>
        <div class="code-block">
            <div class="code-block__bar">
                <span>curl · Sanctum login</span>
                <button type="button" class="copy-btn" data-copy="#sanctum-login">Copy</button>
            </div>
            <pre id="sanctum-login">curl -X POST {{ url('/api/sanctum/login') }} \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"student@smartlearn.test","password":"password"}'

curl {{ url('/api/sanctum/profile') }} \
  -H "Authorization: Bearer TOKEN_HERE" \
  -H "Accept: application/json"</pre>
        </div>
        <div class="note">
            Sanctum is for <strong>first-party</strong> clients. No Client ID / Secret dance.
        </div>
    </section>
@endsection
