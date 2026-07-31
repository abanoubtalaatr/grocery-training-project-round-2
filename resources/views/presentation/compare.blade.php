@extends('layouts.learn')

@section('title', 'Compare')

@section('content')
    <div class="card prose">
        <h2>Authentication comparison</h2>
        <div class="table-wrap">
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
                        <td>Third-party apps (OAuth2)</td>
                        <td>Microservices / stateless APIs</td>
                    </tr>
                    <tr>
                        <th>Stored</th>
                        <td>Server session</td>
                        <td>personal_access_tokens</td>
                        <td>oauth_* tables</td>
                        <td>Usually only on client</td>
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
                        <td>Token row lookup (API mode)</td>
                        <td>Token row + scopes</td>
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
                        <td>App policies/roles</td>
                        <td>Token abilities</td>
                        <td>OAuth scopes</td>
                        <td>Claims / custom</td>
                    </tr>
                    <tr>
                        <th>Package</th>
                        <td>Breeze / Session</td>
                        <td>laravel/sanctum</td>
                        <td>laravel/passport</td>
                        <td>php-open-source-saver/jwt-auth</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
