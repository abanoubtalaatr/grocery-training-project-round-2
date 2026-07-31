<?php

namespace App\Http\Controllers\Api\Sanctum;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    public function index(): JsonResponse
    {
        $courses = Course::query()
            ->with('instructor:id,name,email')
            ->withCount('lessons')
            ->latest()
            ->get();

        return response()->json([
            'auth' => 'sanctum',
            'courses' => $courses,
        ]);
    }
}
