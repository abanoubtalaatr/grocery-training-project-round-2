@extends('layouts.learn')

@section('title', 'Compare')

@section('content')
    <header class="page-intro reveal">
        <span class="badge badge-session">All methods</span>
        <h1>Same goal. Different tools.</h1>
        <p>Use this table when someone asks: “Should we use Sanctum or Passport?”</p>
    </header>

    <section class="panel reveal">
        <div class="compare-wrap">
            <table class="compare">
                <thead>
                    <tr>
                        <th></th>
                        <th>Session</th>
                        <th>Sanctum</th>
                        <th>Passport</th>
                        <th>JWT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Use for</th>
                        <td>Traditional website</td>
                        <td>Own mobile / SPA</td>
                        <td>Third-party apps</td>
                        <td>Microservices</td>
                    </tr>
                    <tr>
                        <th>Stored</th>
                        <td>Server session</td>
                        <td>personal_access_tokens</td>
                        <td>oauth_* tables</td>
                        <td>Usually on client</td>
                    </tr>
                    <tr>
                        <th>Client sends</th>
                        <td>Cookie</td>
                        <td>Bearer PAT</td>
                        <td>Bearer access token</td>
                        <td>Bearer JWT</td>
                    </tr>
                    <tr>
                        <th>Stateful?</th>
                        <td>Yes</td>
                        <td>Token row lookup</td>
                        <td>Token + scopes</td>
                        <td>No (basic JWT)</td>
                    </tr>
                    <tr>
                        <th>Client ID/Secret</th>
                        <td>No</td>
                        <td>No</td>
                        <td>Yes</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <th>Scopes</th>
                        <td>Roles / policies</td>
                        <td>Token abilities</td>
                        <td>OAuth scopes</td>
                        <td>Claims / custom</td>
                    </tr>
                    <tr>
                        <th>Package</th>
                        <td>Breeze / Session</td>
                        <td>laravel/sanctum</td>
                        <td>laravel/passport</td>
                        <td>jwt-auth</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="phase-grid stagger" style="margin-top:1rem;">
        <a class="phase-card reveal" data-phase="session" href="{{ route('presentation.session') }}">
            <div class="phase-card__meta"><span class="badge badge-session">Next</span><span class="phase-card__arrow">→</span></div>
            <h3>Review Session</h3>
            <p>Cookie + server memory.</p>
        </a>
        <a class="phase-card reveal" data-phase="passport" href="{{ route('presentation.passport') }}">
            <div class="phase-card__meta"><span class="badge badge-passport">Next</span><span class="phase-card__arrow">→</span></div>
            <h3>Review Passport</h3>
            <p>When outsiders need limited access.</p>
        </a>
    </section>
@endsection
