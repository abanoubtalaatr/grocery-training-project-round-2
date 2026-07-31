@extends('layouts.learn')

@section('title', 'Passport OAuth2')

@section('content')
    <div class="card prose" style="margin-bottom:1rem;">
        <span class="badge badge-passport">Phase 3 · Passport</span>
        <h2>Third-party SaaS integrations</h2>
        <p>
            External systems are <strong>Applications</strong>, not students.
            Each registers as an OAuth client with Client ID + Secret and limited scopes.
        </p>

        <h3>Flow (Client Credentials)</h3>
        <div class="flow">ABC University / Zoom / ExamPro / Certificate Generator
↓
Client ID + Client Secret
↓
POST /oauth/token  (grant_type=client_credentials&scope=...)
↓
Access Token (oauth_access_tokens)
↓
Call /api/oauth-demo/* with Authorization: Bearer ACCESS_TOKEN</div>

        <h3>Demo clients (seeded)</h3>
        <div class="table-wrap">
            <table class="compare">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>ID</th>
                        <th>Intended scopes</th>
                        <th>Cannot</th>
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
                                    Authorization Code demo (user consent)
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
                        <tr><td colspan="4">Run <code class="inline">php artisan db:seed</code> to create clients.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h3>Example — ABC University reads courses</h3>
        <div class="flow"># 1) Get token (replace CLIENT_ID / CLIENT_SECRET from seed output)
curl -X POST {{ url('/oauth/token') }} \
  -H "Accept: application/json" \
  -d "grant_type=client_credentials" \
  -d "client_id=CLIENT_ID" \
  -d "client_secret=CLIENT_SECRET" \
  -d "scope=courses.read students.read"

# 2) Call API
curl {{ url('/api/oauth-demo/courses') }} \
  -H "Authorization: Bearer ACCESS_TOKEN" \
  -H "Accept: application/json"

# 3) This should FAIL (missing courses.write)
curl -X DELETE {{ url('/api/oauth-demo/courses/1') }} \
  -H "Authorization: Bearer ACCESS_TOKEN" \
  -H "Accept: application/json"</div>

        <h3>Authorization Code (Approval Screen)</h3>
        <p>
            Log in as a student first, then open an authorize URL with the Auth Code client.
            Passport shows the consent screen listing requested scopes (Allow / Cancel).
        </p>
        <div class="flow">GET /oauth/authorize
  ?client_id=AUTH_CODE_CLIENT_ID
  &redirect_uri={{ url('/learn/passport') }}
  &response_type=code
  &scope=courses.read%20students.read
  &state=xyz</div>

        <div class="note">
            Passport stores tokens in <code class="inline">oauth_*</code> tables and supports
            refresh tokens, expiration, and fine-grained scopes — required for third parties.
        </div>
    </div>
@endsection
