<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Phase 1 — Course management via Session Auth.
 */
class CourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->with(['instructor', 'lessons'])
            ->withCount('enrollments')
            ->latest()
            ->paginate(10);

        return view('courses.index', compact('courses'));
    }

    public function show(Course $course): View
    {
        $course->load(['instructor', 'lessons', 'exams', 'enrollments.student']);

        return view('courses.show', compact('course'));
    }

    public function create(): View
    {
        return view('courses.create');
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $course = Course::query()->create([
            ...$request->validated(),
            'instructor_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('courses.show', $course)
            ->with('status', 'Course created successfully.');
    }

    public function edit(Course $course): View
    {
        $this->authorizeCourse($course);

        return view('courses.edit', compact('course'));
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorizeCourse($course);
        $course->update($request->validated());

        return redirect()
            ->route('courses.show', $course)
            ->with('status', 'Course updated successfully.');
    }

    public function destroy(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeCourse($course);
        $course->delete();

        return redirect()
            ->route('courses.index')
            ->with('status', 'Course deleted successfully.');
    }

    private function authorizeCourse(Course $course): void
    {
        $user = request()->user();

        abort_unless(
            $user->isAdmin() || $course->instructor_id === $user->id,
            403
        );
    }
}
