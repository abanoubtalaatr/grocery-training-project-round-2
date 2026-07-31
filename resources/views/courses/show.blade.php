<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $course->title }}</h2>
            @if(auth()->user()->isAdmin() || $course->instructor_id === auth()->id())
                <div class="flex gap-2">
                    <a href="{{ route('courses.edit', $course) }}" class="px-3 py-2 text-sm bg-white border rounded-md">Edit</a>
                    <form method="POST" action="{{ route('courses.destroy', $course) }}" onsubmit="return confirm('Delete this course?')">
                        @csrf
                        @method('DELETE')
                        <button class="px-3 py-2 text-sm bg-red-600 text-white rounded-md">Delete</button>
                    </form>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-gray-700">{{ $course->description }}</p>
                <p class="text-sm text-gray-500 mt-3">Instructor: {{ $course->instructor?->name }}</p>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold mb-3">Lessons</h3>
                <ul class="space-y-2">
                    @forelse($course->lessons as $lesson)
                        <li class="text-sm text-gray-700">{{ $lesson->sort_order }}. {{ $lesson->title }}</li>
                    @empty
                        <li class="text-sm text-gray-500">No lessons yet.</li>
                    @endforelse
                </ul>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold mb-3">Enrolled students</h3>
                <ul class="space-y-2">
                    @forelse($course->enrollments as $enrollment)
                        <li class="text-sm text-gray-700">{{ $enrollment->student?->name }} ({{ $enrollment->student?->email }})</li>
                    @empty
                        <li class="text-sm text-gray-500">No enrollments yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
