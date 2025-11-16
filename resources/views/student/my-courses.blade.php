<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold">My Enrolled Courses</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4">
            @if ($courses->isEmpty())
                <p class="text-gray-700">You have not enrolled in any courses yet.</p>
            @else
                <ul class="space-y-4">
                    @foreach ($courses as $course)
                        <li class="border p-4 rounded shadow bg-white">
                            <a href="{{ route('courses.show', $course) }}"
                               class="text-lg font-semibold text-blue-600 hover:underline">
                                {{ $course->title }}
                            </a><br>

                            <small class="text-gray-600">
                                Instructor: {{ $course->instructor->name }}
                            </small><br>

                            {{-- Pricing --}}
                            @if ($course->sale_price && $course->sale_price > 0)
                                <p class="text-gray-500 line-through text-sm">
                                    £{{ number_format($course->price, 2) }}
                                </p>
                                <p class="text-indigo-600 font-bold text-lg">
                                    £{{ number_format($course->sale_price, 2) }}
                                </p>
                            @elseif ($course->price > 0)
                                <p class="text-indigo-600 font-bold text-lg">
                                    £{{ number_format($course->price, 2) }}
                                </p>
                            @else
                                <p class="text-green-600 font-bold text-lg">FREE</p>
                            @endif

                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-app-layout>
