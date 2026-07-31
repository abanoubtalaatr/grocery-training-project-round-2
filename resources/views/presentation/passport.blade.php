@extends('layouts.learn')

@section('title', 'Passport OAuth2')

@section('content')
    <header class="page-intro reveal">
        <span class="badge badge-passport">Phase 3 · Passport</span>
        <h1>Third-party apps ask for limited access.</h1>
        <p>
            ABC University, Zoom, ExamPro, and Certificate Generator are applications — not students.
            Each gets a Client ID + Secret and only the scopes it needs.
        </p>
    </header>

    <section class="panel reveal">
        <h2 style="font-family:var(--font-display);margin:0 0 .4rem;">Client Credentials flow</h2>
        <div class="flow-track">
            <div class="flow-step" data-tone="passport">
                <div class="flow-step__index">1</div>
                <div>
                    <strong>Register an OAuth client</strong>
                    <span>Seeded for you — secrets live in <code class="inline">storage/oauth-demo-clients.json</code>.</span>
                </div>
            </div>
            <div class="flow-step" data-tone="passport">
                <div class="flow-step__index">2</div>
                <div>
                    <strong>POST /oauth/token</strong>
                    <span><code class="inline">grant_type=client_credentials</code> + scope list.</span>
                </div>
            </div>
            <div class="flow-step" data-tone="passport">
                <div class="flow-step__index">3</div>
                <div>
                    <strong>Call /api/oauth-demo/*</strong>
                    <span>Middleware checks scopes. Missing scope → 403.</span>
                </div>
            </div>
        </div>
    </section>

    <section class="panel reveal">
        <h2 style="font-family:var(--font-display);margin:0 0 .8rem;">Seeded clients</h2>
        <div class="compare-wrap">
            <table class="compare">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>ID</th>
                        <th>Allowed</th>
                        <th>Blocked</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>{{ $client->name }}</td>
                            <td><code class="inline">{{ $client->id }}</code></td>
                            <td>
                                @if(str_contains($client->name, 'ABC University') && ! str_contains($client->name, 'Auth Code'))
                                    courses.read, students.read
                                @elseif(str_contains($client->name, 'Zoom'))
                                    meetings.read, courses.read
                                @elseif(str_contains($client->name, 'ExamPro'))
                                    grades.read
                                @elseif(str_contains($client->name, 'Certificate'))
                                    certificates.write
                                @elseif(str_contains($client->name, 'Auth Code'))
                                    User consent (Authorization Code)
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if(str_contains($client->name, 'ABC') && ! str_contains($client->name, 'Auth'))
                                    delete courses
                                @elseif(str_contains($client->name, 'Zoom'))
                                    read students
                                @else
                                    anything outside scopes
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Run <code class="inline">php artisan db:seed</code> to create clients.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel reveal">
        <h2 style="font-family:var(--font-display);margin:0 0 .4rem;">Example request</h2>
        <div class="code-block">
            <div class="code-block__bar">
                <span>curl · Passport client credentials</span>
                <button type="button" class="copy-btn" data-copy="#passport-token">Copy</button>
            </div>
            <pre id="passport-token">curl -X POST {{ url('/oauth/token') }} \
  -H "Accept: application/json" \
  -d "grant_type=client_credentials" \
  -d "client_id=CLIENT_ID" \
  -d "client_secret=CLIENT_SECRET" \
  -d "scope=courses.read students.read"

curl {{ url('/api/oauth-demo/courses') }} \
  -H "Authorization: Bearer ACCESS_TOKEN" \
  -H "Accept: application/json"</pre>
        </div>
        <div class="note">
            Authorization Code demo: login as a student, then hit <code class="inline">/oauth/authorize</code>
            with the Auth Code client to see the Allow / Cancel screen.
        </div>
    </section>
@endsection
