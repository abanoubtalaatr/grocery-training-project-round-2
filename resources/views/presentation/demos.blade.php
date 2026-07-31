@extends('layouts.learn')

@section('title', 'Demo Scenarios')

@section('content')
    <header class="page-intro reveal">
        <span class="badge badge-jwt">Practice</span>
        <h1>Seven demos. One app.</h1>
        <p>Walk these scenarios in order — each one uses a different authentication method.</p>
    </header>

    <section class="panel reveal">
        <div class="demo-list">
            <article class="demo-item">
                <div class="demo-item__num" style="background:var(--session)">1</div>
                <div>
                    <h3>Student website · Session</h3>
                    <p>Login at <a href="{{ route('login') }}">/login</a> → dashboard → courses. Cookie-based.</p>
                </div>
            </article>
            <article class="demo-item">
                <div class="demo-item__num" style="background:var(--sanctum)">2</div>
                <div>
                    <h3>Flutter app · Sanctum</h3>
                    <p><code class="inline">POST /api/sanctum/login</code> → Bearer token → <code class="inline">GET /api/sanctum/courses</code></p>
                </div>
            </article>
            <article class="demo-item">
                <div class="demo-item__num" style="background:var(--passport)">3</div>
                <div>
                    <h3>ABC University · Passport</h3>
                    <p>Scopes <code class="inline">courses.read students.read</code>. Cannot delete courses.</p>
                </div>
            </article>
            <article class="demo-item">
                <div class="demo-item__num" style="background:var(--passport)">4</div>
                <div>
                    <h3>Zoom · Passport</h3>
                    <p>Scopes <code class="inline">meetings.read courses.read</code>. Cannot read students.</p>
                </div>
            </article>
            <article class="demo-item">
                <div class="demo-item__num" style="background:var(--passport)">5</div>
                <div>
                    <h3>ExamPro · Passport</h3>
                    <p>Only <code class="inline">grades.read</code> → <code class="inline">GET /api/oauth-demo/grades</code></p>
                </div>
            </article>
            <article class="demo-item">
                <div class="demo-item__num" style="background:var(--passport)">6</div>
                <div>
                    <h3>Certificate Generator · Passport</h3>
                    <p><code class="inline">POST /api/oauth-demo/certificates</code> with student_id + course_id.</p>
                </div>
            </article>
            <article class="demo-item">
                <div class="demo-item__num" style="background:var(--jwt)">7</div>
                <div>
                    <h3>Microservice · JWT</h3>
                    <p>Login → profile → refresh with a signed Bearer JWT.</p>
                </div>
            </article>
        </div>

        <div class="note">
            Import <code class="inline">postman/SmartLearn-Auth.postman_collection.json</code>.
            OAuth secrets: <code class="inline">storage/oauth-demo-clients.json</code> after seeding.
        </div>
    </section>
@endsection
