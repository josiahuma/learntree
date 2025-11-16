<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $lesson->title }}</h1>
                @if($lesson->course)
                    <p class="text-sm text-gray-500 mt-1">
                        Part of: <span class="font-semibold">{{ $lesson->course->title }}</span>
                    </p>
                @endif
            </div>

            <a href="{{ route('courses.show', $lesson->course_id) }}"
               class="text-sm text-indigo-600 hover:text-indigo-800 underline">
                ⬅ Back to Course
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 md:flex gap-8">
        {{-- Left: Lesson Content --}}
        <div class="md:w-2/3 space-y-6">

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-200 text-green-800 text-sm px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-200 text-red-800 text-sm px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Video Embed --}}
            @if ($lesson->video_url)
                @php
                    $videoUrl = $lesson->video_url;
                    $embedUrl = null;

                    if (str_contains($videoUrl, 'youtube.com') || str_contains($videoUrl, 'youtu.be')) {
                        preg_match('/(youtu\.be\/|v=)([a-zA-Z0-9_-]+)/', $videoUrl, $matches);
                        $videoId = $matches[2] ?? null;
                        $embedUrl = $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;
                    } elseif (str_contains($videoUrl, 'vimeo.com')) {
                        preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $matches);
                        $videoId = $matches[1] ?? null;
                        $embedUrl = $videoId ? "https://player.vimeo.com/video/{$videoId}" : null;
                    }
                @endphp

                @if ($embedUrl)
                    <div class="w-full aspect-video rounded overflow-hidden shadow">
                        <iframe
                            src="{{ $embedUrl }}"
                            class="w-full h-full"
                            frameborder="0"
                            allowfullscreen
                        ></iframe>
                    </div>
                @else
                    <div class="p-4 border rounded bg-yellow-50 text-sm text-yellow-800">
                        ⚠ We couldn't recognise this video URL. Please check that it's a valid YouTube or Vimeo link.
                    </div>
                @endif
            @endif

            {{-- Lesson Content --}}
            @if ($lesson->content)
                <div class="prose max-w-none text-gray-800">
                    {!! nl2br(e($lesson->content)) !!}
                </div>
            @else
                <div class="p-4 border rounded bg-gray-50 text-sm text-gray-600">
                    No written content has been added for this lesson yet.
                </div>
            @endif

            {{-- Completion & navigation --}}
            @php
                $isCompleted = auth()->user()->completedLessons->contains($lesson->id);
            @endphp

            <div class="mt-6 flex flex-wrap items-center gap-3">
                @if (!$isCompleted)
                    {{-- Mark as complete --}}
                    <form method="POST" action="{{ route('lessons.complete', $lesson->id) }}">
                        @csrf
                        <button
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded text-sm font-semibold">
                            ✅ Mark as Complete
                        </button>
                    </form>

                    <p class="text-xs text-gray-500">
                        Mark this lesson as complete to unlock the quiz or move to the next lesson.
                    </p>
                @else
                    {{-- Completed state: show next action --}}
                    <span class="inline-flex items-center text-green-700 text-sm font-medium">
                        ✔ Lesson completed
                    </span>

                    @if ($lesson->quiz)
                        <a href="{{ route('lessons.quiz.paginated', ['lesson' => $lesson->id, 'question' => 0]) }}"
                           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm inline-flex items-center">
                            🧪 Take Quiz
                        </a>
                    @elseif (!empty($nextLesson))
                        <a href="{{ route('lessons.show', $nextLesson->id) }}"
                           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm inline-flex items-center">
                            ⏭️ Next Lesson
                        </a>
                    @else
                        <a href="{{ route('student.dashboard') }}"
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm inline-flex items-center">
                            🎉 View Progress / Download Certificate
                        </a>
                    @endif
                @endif
            </div>
        </div>

        {{-- Right: Instructor Tools --}}
        <div class="md:w-1/3 mt-10 md:mt-0">
            @if (auth()->user()->role === 'instructor')
                <div class="bg-white p-6 rounded shadow border sticky top-20 space-y-4">
                    <h2 class="text-lg font-semibold mb-2">📘 Lesson Management</h2>
                    <p class="text-sm text-gray-600 mb-3">
                        Manage the quiz attached to this lesson.
                    </p>

                    @if ($lesson->quiz)
                        <div class="mb-3">
                            <p class="text-green-700 font-medium mb-1">
                                ✅ A quiz is currently attached to this lesson.
                            </p>
                            <p class="text-xs text-gray-500">
                                Students will see a "Take Quiz" button after they complete the lesson.
                            </p>
                        </div>

                        <div class="space-y-2">
                            {{-- Edit quiz (pre-filled form) --}}
                            <a href="{{ route('quizzes.edit', $lesson->quiz) }}"
                            class="block text-sm bg-yellow-400 hover:bg-yellow-500 text-black px-3 py-2 rounded text-center font-semibold">
                                ✏️ Edit Quiz
                            </a>

                            {{-- Preview quiz as student --}}
                            <a href="{{ route('lessons.quiz.paginated', ['lesson' => $lesson->id, 'question' => 0]) }}"
                            class="block text-sm text-indigo-600 hover:underline text-center">
                                👀 Preview Quiz as Student
                            </a>

                            {{-- Delete quiz --}}
                            <form action="{{ route('quizzes.destroy', $lesson->quiz) }}"
                                method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this quiz? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-full text-sm text-red-600 hover:text-red-800 font-semibold mt-2">
                                    🗑️ Delete Quiz
                                </button>
                            </form>
                        </div>
                    @else
                        <p class="text-sm text-gray-600 mb-3">
                            No quiz is attached to this lesson yet.
                        </p>

                        <a href="{{ route('quizzes.create', $lesson) }}"
                        class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm font-semibold">
                            ➕ Create Quiz for this Lesson
                        </a>
                    @endif
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
