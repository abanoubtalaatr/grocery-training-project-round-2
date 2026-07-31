@extends('layouts.learn')

@section('title', 'JWT')

@section('content')
    <div class="card prose">
        <span class="badge badge-jwt">Phase 4 · JWT</span>
        <h2>Microservice-style API</h2>
        <p>
            A separate API authenticates with JWT (<code class="inline">php-open-source-saver/jwt-auth</code>).
            No session. No Sanctum PAT row. The token itself carries claims; the server verifies the signature.
        </p>

        <h3>Flow</h3>
        <div class="flow">POST /api/jwt/login
↓
Generate signed JWT
↓
Return JWT to client
↓
Client stores JWT
↓
Authorization: Bearer JWT
↓
Server verifies signature + expiry (stateless)</div>

        <h3>Endpoints</h3>
        <ul>
            <li><code class="inline">POST /api/jwt/login</code></li>
            <li><code class="inline">POST /api/jwt/refresh</code></li>
            <li><code class="inline">GET /api/jwt/profile</code></li>
            <li><code class="inline">POST /api/jwt/logout</code></li>
        </ul>

        <h3>Example</h3>
        <div class="flow">curl -X POST {{ url('/api/jwt/login') }} \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"student@smartlearn.test","password":"password"}'

curl {{ url('/api/jwt/profile') }} \
  -H "Authorization: Bearer JWT_HERE" \
  -H "Accept: application/json"

curl -X POST {{ url('/api/jwt/refresh') }} \
  -H "Authorization: Bearer JWT_HERE" \
  -H "Accept: application/json"</div>

        <div class="note">
            JWT shines for microservices and distributed systems where you want to avoid
            a shared token database on every request. Revocation is harder than Sanctum/Passport
            unless you add a denylist.
        </div>
    </div>
@endsection
