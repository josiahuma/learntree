<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold">🎯 Quiz Result for: {{ $lesson->title }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 bg-white p-6 rounded shadow border">
            <div class="mb-6 text-center">
                <h3 class="text-2xl font-semibold">
                    You scored {{ $score }} out of {{ $lesson->quiz->questions->count() }}
                </h3>
                <p class="text-gray-700 mt-2 text-lg">
                    That's {{ number_format($percentage, 1) }}%
                </p>

                @if($percentage >= 80)
                    <p class="mt-4 text-green-600 font-medium text-xl">
                        🎉 Great job! You passed this quiz.
                    </p>
                @else
                    <p class="mt-4 text-red-600 font-medium text-xl">
                        😅 You scored below 80%. Try again!
                    </p>
                @endif
            </div>

            <div class="flex justify-center mt-6">
                @if($percentage >= 80)
                    @php
                        // Find the next lesson in this course (by ID or explicit order if you add it later)
                        $nextLesson = $lesson->course->lessons()
                            ->where('id', '>', $lesson->id)
                            ->orderBy('id')
                            ->first();
                    @endphp

                    @if($nextLesson)
                        {{-- There IS another lesson: show Next Lesson button --}}
                        <a href="{{ route('lessons.show', $nextLesson) }}"
                           class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded font-semibold">
                            👉 Next Lesson: {{ $nextLesson->title }}
                        </a>
                    @else
                        {{-- No more lessons: go to dashboard to see progress / certificate --}}
                        <div class="flex flex-col items-center gap-3">
                            <span class="text-gray-700 font-medium">
                                🎉 You've completed all lessons!
                            </span>

                            <a href="{{ route('student.dashboard') }}"
                               class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded font-semibold">
                                🎓 View Progress / Download Certificate
                            </a>
                        </div>
                    @endif
                @else
                    {{-- Failed quiz: retake button --}}
                    <a href="{{ route('lessons.quiz.paginated', ['lesson' => $lesson->id, 'question' => 0]) }}"
                       class="bg-yellow-400 hover:bg-yellow-500 text-black px-6 py-2 rounded font-semibold">
                        🔁 Retake Quiz
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
