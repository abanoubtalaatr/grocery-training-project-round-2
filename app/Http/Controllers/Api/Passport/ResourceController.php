<?php

namespace App\Http\Controllers\Api\Passport;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Phase 3 — Passport OAuth2 resource APIs for third-party apps.
 * Scopes are enforced by the `scopes:` middleware (CheckToken).
 */
class ResourceController extends Controller
{
    public function courses(): JsonResponse
    {
        return response()->json([
            'auth' => 'passport',
            'scope_required' => 'courses.read',
            'courses' => Course::query()->with('instructor:id,name')->get(),
        ]);
    }

    public function students(): JsonResponse
    {
        $students = User::query()
            ->where('role', UserRole::Student)
            ->with('enrollments.course:id,title')
            ->get(['id', 'name', 'email', 'role']);

        return response()->json([
            'auth' => 'passport',
            'scope_required' => 'students.read',
            'students' => $students,
        ]);
    }

    public function grades(): JsonResponse
    {
        return response()->json([
            'auth' => 'passport',
            'scope_required' => 'grades.read',
            'grades' => Grade::query()
                ->with(['student:id,name', 'exam:id,title', 'course:id,title'])
                ->get(),
        ]);
    }

    public function meetings(): JsonResponse
    {
        return response()->json([
            'auth' => 'passport',
            'scope_required' => 'meetings.read',
            'meetings' => Meeting::query()->with('course:id,title')->get(),
        ]);
    }

    public function storeCertificate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'course_id' => ['required', 'exists:courses,id'],
        ]);

        $certificate = Certificate::query()->firstOrCreate(
            [
                'student_id' => $data['student_id'],
                'course_id' => $data['course_id'],
            ],
            [
                'code' => 'CERT-'.Str::upper(Str::random(10)),
                'issued_at' => now(),
            ]
        );

        return response()->json([
            'auth' => 'passport',
            'scope_required' => 'certificates.write',
            'message' => 'Certificate generated.',
            'certificate' => $certificate->load(['student:id,name', 'course:id,title']),
        ], 201);
    }

    public function destroyCourse(Course $course): JsonResponse
    {
        $course->delete();

        return response()->json([
            'auth' => 'passport',
            'scope_required' => 'courses.write',
            'message' => 'Course deleted (requires courses.write).',
        ]);
    }
}
