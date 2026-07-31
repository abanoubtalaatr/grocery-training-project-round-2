<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Courses</h2>
            @if(auth()->user()->isAdmin() || auth()->user()->isInstructor())
                <a href="{{ route('courses.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm rounded-md">
                    New course
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">{{ session('status') }}</div>
            @endif

            @foreach($courses as $course)
                <div class="bg-white shadow-sm sm:rounded-lg p-5 flex items-start justify-between gap-4">
                    <div>
                        <a href="{{ route('courses.show', $course) }}" class="text-lg font-semibold text-gray-900 hover:underline">
                            {{ $course->title }}
                        </a>
                        <p class="text-sm text-gray-500 mt-1">{{ $course->description }}</p>
                        <p class="text-xs text-gray-400 mt-2">
                            Instructor: {{ $course->instructor?->name }} ·
                            {{ $course->lessons_count }} lessons ·
                            {{ $course->enrollments_count }} students
                        </p>
                    </div>
                </div>
            @endforeach

            <div>{{ $courses->links() }}</div>
        </div>
    </div>
</x-app-layout>
