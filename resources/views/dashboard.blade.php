<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                SmartLearn Dashboard
            </h2>
            <span class="text-sm text-gray-500">
                {{ $user->role?->label() }} · Session Auth
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <div class="text-sm text-gray-500">Courses</div>
                    <div class="text-2xl font-semibold">{{ $stats['courses'] }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <div class="text-sm text-gray-500">Enrollments</div>
                    <div class="text-2xl font-semibold">{{ $stats['enrollments'] }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <div class="text-sm text-gray-500">My enrollments</div>
                    <div class="text-2xl font-semibold">{{ $stats['my_enrollments'] }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <div class="text-sm text-gray-500">Courses I teach</div>
                    <div class="text-2xl font-semibold">{{ $stats['taught_courses'] }}</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-lg">Latest courses</h3>
                        <a href="{{ route('courses.index') }}" class="text-sm text-indigo-600 hover:underline">View all</a>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @forelse($courses as $course)
                            <li class="py-3 flex items-center justify-between gap-4">
                                <div>
                                    <a href="{{ route('courses.show', $course) }}" class="font-medium text-gray-900 hover:underline">
                                        {{ $course->title }}
                                    </a>
                                    <div class="text-sm text-gray-500">by {{ $course->instructor?->name }}</div>
                                </div>
                            </li>
                        @empty
                            <li class="py-3 text-gray-500">No courses yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="bg-slate-900 text-slate-100 rounded-lg p-5 text-sm">
                <p class="font-semibold mb-2">You are authenticated with Session Auth</p>
                <p class="opacity-80">
                    Browser cookie → server session row. Explore other methods in
                    <a class="underline" href="{{ route('presentation.index') }}">/learn</a>.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
