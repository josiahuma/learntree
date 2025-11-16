<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold">🎓 My Dashboard</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 space-y-8">
            {{-- Greeting --}}
            <div>
                <h3 class="text-lg font-semibold">Welcome, {{ Auth::user()->name }}</h3>
                <p class="text-sm text-gray-600 mt-1">
                    Here’s a quick overview of your courses and quiz progress.
                </p>
            </div>

            {{-- Empty state --}}
            @if($enrollments->count() === 0)
                <div class="mt-4 bg-white border border-dashed rounded-lg p-8 text-center shadow-sm">
                    <div class="text-3xl mb-3">📚</div>
                    <h4 class="text-lg font-semibold mb-2">You’re not enrolled in any courses yet</h4>
                    <p class="text-gray-600 mb-4">
                        Once you enroll in a course, you’ll see your lessons, quiz scores,
                        and certificates here.
                    </p>
                    <a href="{{ route('courses.index') }}"
                       class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded shadow">
                        🔍 Browse Courses
                    </a>
                </div>
            @endif

            {{-- Summary when there ARE enrollments --}}
            @if($enrollments->count() > 0)
                <div class="bg-white rounded-lg border shadow-sm p-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">
                            You are enrolled in
                            <span class="font-semibold text-gray-800">{{ $enrollments->count() }}</span>
                            {{ Str::plural('course', $enrollments->count()) }}.
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Keep completing lessons and scoring 80% or more in quizzes to unlock certificates.
                        </p>
                    </div>
                </div>
            @endif

            {{-- Course cards --}}
            @foreach($enrollments as $enrollment)
                @php
                    $course = $enrollment->course;
                @endphp

                @if($course)
                    @php
                        $totalLessons      = $course->lessons->count();
                        $completedLessons  = $course->lessons->whereIn('id', $completions)->count();
                        $progressPercent   = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
                        $eligibleForCertificate = $progress[$course->id]['eligibleForCertificate'] ?? false;

                        if ($progressPercent === 0) {
                            $statusLabel = 'Not started';
                            $statusClass = 'bg-gray-100 text-gray-700';
                        } elseif ($progressPercent < 100) {
                            $statusLabel = 'In progress';
                            $statusClass = 'bg-yellow-100 text-yellow-800';
                        } else {
                            $statusLabel = 'Completed';
                            $statusClass = 'bg-green-100 text-green-800';
                        }
                    @endphp

                    {{-- Each course card is now collapsible --}}
                    <div
                        x-data="{ open: false }"
                        class="border rounded-lg shadow bg-white p-6 space-y-4"
                    >
                        {{-- Header --}}
                        <div class="flex justify-between items-start gap-3">
                            <div>
                                <h4 class="text-lg font-bold">{{ $course->title }}</h4>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $completedLessons }} / {{ $totalLessons }} lessons completed
                                </p>
                            </div>

                            <div class="flex flex-col items-end gap-2">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>

                                {{-- Collapse toggle --}}
                                <button
                                    type="button"
                                    @click="open = !open"
                                    class="text-xs text-gray-600 hover:text-gray-800 flex items-center gap-1"
                                >
                                    <span x-text="open ? 'Hide lessons' : 'Show lessons'"></span>
                                    <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 15l7-7 7 7" />
                                    </svg>
                                    <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Progress bar --}}
                        <div>
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span>Course progress</span>
                                <span>{{ $progressPercent }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 h-3 rounded-full overflow-hidden">
                                <div class="bg-green-500 h-3 transition-all duration-300"
                                     style="width: {{ $progressPercent }}%">
                                </div>
                            </div>
                        </div>

                        {{-- Lessons list (collapsible) --}}
                        <ul
                            class="space-y-2"
                            x-show="open"
                            x-transition
                        >
                            @foreach($course->lessons as $lesson)
                                <li class="border-b pb-2 last:border-b-0 last:pb-0">
                                    <div class="flex justify-between items-center gap-3">
                                        <div class="min-w-0">
                                            <a href="{{ route('lessons.show', $lesson) }}"
                                               class="text-blue-600 hover:underline truncate block">
                                                <span class="font-medium">{{ $lesson->title }}</span>
                                            </a>
                                            @if(in_array($lesson->id, $completions))
                                                <span class="inline-flex items-center text-green-600 text-xs mt-0.5">
                                                    ✅ Completed
                                                </span>
                                            @endif
                                        </div>

                                        <div class="text-right text-xs space-x-2 flex-shrink-0">
                                            @if($lesson->quiz)
                                                @php
                                                    $quiz       = $lesson->quiz;
                                                    $submission = $submissions[$quiz->id] ?? null;
                                                    $correct    = $submission ? $submission->correct_answers : null;
                                                    $totalQ     = $quiz->questions->count();
                                                    $passed     = $correct !== null && $totalQ > 0 && ($correct / $totalQ) * 100 >= 80;
                                                @endphp

                                                <span class="block text-blue-600 mb-1">
                                                    🧠 Score:
                                                    {{ $correct !== null ? $correct . '/' . $totalQ : 'Not attempted' }}
                                                </span>

                                                @if($passed)
                                                    <a href="{{ route('lessons.quiz.paginated', $lesson) }}"
                                                       class="inline-block text-green-700 font-semibold hover:underline">
                                                        ✅ Retake Quiz
                                                    </a>
                                                @else
                                                    <a href="{{ route('lessons.quiz.paginated', $lesson) }}"
                                                       class="inline-block text-red-500 font-semibold hover:underline">
                                                        🔁 Start / Resume Quiz
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        {{-- Certificate actions --}}
                        <div class="pt-3 mt-2 flex items-center justify-between">
                            @if($eligibleForCertificate)
                                <div class="text-sm text-green-700 flex items-center gap-2">
                                    <span>🎉 You’ve completed this course and passed all quizzes!</span>
                                </div>
                                <a href="{{ route('certificate.download', $course->id) }}"
                                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded shadow text-sm">
                                    🎓 Download Certificate
                                </a>
                            @else
                                <div class="text-xs text-gray-500">
                                    Complete all lessons and score at least
                                    <span class="font-semibold">80%</span> in every quiz to unlock your certificate.
                                </div>
                                <button
                                    class="bg-gray-500 text-white px-4 py-2 rounded shadow text-sm cursor-not-allowed"
                                    disabled
                                    title="Complete all lessons and quizzes with 80%+ to download"
                                >
                                    🔒 Certificate Locked
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</x-app-layout>
