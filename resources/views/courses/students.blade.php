<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold">Students in {{ $course->title }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 bg-white p-6 rounded shadow border">
            @if ($students->isEmpty())
                <p class="text-gray-600">No students have enrolled yet.</p>
            @else
                <ul class="space-y-2 list-disc pl-6">
                    @foreach ($students as $student)
                        <li class="text-gray-800">
                            {{ $student->name }}
                            <span class="text-sm text-gray-500">({{ $student->email }})</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="mt-6">
                <a href="{{ route('instructor.dashboard') }}" class="inline-block text-blue-600 hover:underline">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
