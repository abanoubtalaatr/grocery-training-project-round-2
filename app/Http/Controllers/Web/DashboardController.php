<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Phase 1 — Session Authentication (Blade website).
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $stats = [
            'courses' => Course::query()->count(),
            'enrollments' => Enrollment::query()->count(),
            'my_enrollments' => $user->enrollments()->count(),
            'taught_courses' => $user->taughtCourses()->count(),
        ];

        $courses = Course::query()
            ->with('instructor')
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard', compact('user', 'stats', 'courses'));
    }
}
