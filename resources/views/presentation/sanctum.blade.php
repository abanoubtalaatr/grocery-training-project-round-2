@extends('layouts.learn')

@section('title', 'Sanctum')

@section('content')
    <div class="card prose">
        <span class="badge badge-sanctum">Phase 2 · Sanctum</span>
        <h2>Own mobile app & SPA</h2>
        <p>
            Same company, same backend. Flutter / React apps authenticate with
            Sanctum personal access tokens. Tokens live in
            <code class="inline">personal_access_tokens</code>.
        </p>

        <h3>Flow</h3>
        <div class="flow">POST /api/sanctum/login
↓
$user->createSanctumToken('mobile')
↓
Store hashed token in personal_access_tokens
↓
Return plain-text token once
↓
Mobile stores token
↓
Authorization: Bearer {id}|{plainTextToken}</div>

        <h3>Endpoints</h3>
        <ul>
            <li><code class="inline">POST /api/sanctum/login</code></li>
            <li><code class="inline">POST /api/sanctum/logout</code> (revokes token)</li>
            <li><code class="inline">GET /api/sanctum/profile</code></li>
            <li><code class="inline">GET /api/sanctum/courses</code></li>
        </ul>

        <h3>Example (curl)</h3>
        <div class="flow">curl -X POST {{ url('/api/sanctum/login') }} \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"student@smartlearn.test","password":"password"}'

# then:
curl {{ url('/api/sanctum/profile') }} \
  -H "Authorization: Bearer TOKEN_HERE" \
  -H "Accept: application/json"</div>

        <div class="note">
            Sanctum is for <strong>first-party</strong> clients you control.
            No Client ID/Secret dance — just user login → token.
        </div>
    </div>
@endsection
