@extends('layouts.learn')

@section('title', 'Demo Scenarios')

@section('content')
    <div class="card prose">
        <h2>Seven demo scenarios</h2>

        <h3>1 — Student website (Session)</h3>
        <p>Login at <a href="{{ route('login') }}">/login</a> → dashboard → courses. Cookie-based.</p>

        <h3>2 — Student Flutter app (Sanctum)</h3>
        <div class="flow">POST /api/sanctum/login → Bearer token → GET /api/sanctum/courses</div>

        <h3>3 — ABC University portal (Passport)</h3>
        <p>Client credentials with <code class="inline">courses.read students.read</code>. Cannot delete courses.</p>

        <h3>4 — Zoom (Passport)</h3>
        <p>Scopes: <code class="inline">meetings.read courses.read</code>. Cannot read students.</p>

        <h3>5 — ExamPro (Passport)</h3>
        <p>Scope: <code class="inline">grades.read</code> only → <code class="inline">GET /api/oauth-demo/grades</code></p>

        <h3>6 — Certificate Generator (Passport)</h3>
        <div class="flow">POST /api/oauth-demo/certificates
{ "student_id": 3, "course_id": 1 }</div>

        <h3>7 — Microservice (JWT)</h3>
        <div class="flow">POST /api/jwt/login → Bearer JWT → GET /api/jwt/profile → POST /api/jwt/refresh</div>

        <div class="note" style="margin-top:1rem;">
            Import <code class="inline">postman/SmartLearn-Auth.postman_collection.json</code> for ready-made requests.
            After <code class="inline">php artisan db:seed</code>, copy OAuth client secrets from the terminal output
            (or from <code class="inline">storage/oauth-demo-clients.json</code> if present).
        </div>
    </div>
@endsection
