{{-- resources/views/quizzes/take-paginated.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold">
            {{ $lesson->title }} —
            Question {{ $questionIndex + 1 }}
            @if($lesson->quiz && $lesson->quiz->questions)
                of {{ $lesson->quiz->questions->count() }}
            @endif
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4">
            <form method="POST"
                  action="{{ route('lessons.quiz.paginated.store', ['lesson' => $lesson->id]) }}">
                @csrf

                <div class="bg-white shadow border rounded p-6">
                    {{-- Question text --}}
                    <h3 class="font-semibold text-lg mb-4">
                        {{ $question->question }}
                    </h3>

                    @php
                        // Restore previously selected answers from session (if your controller saves them as 'quiz_answers')
                        $storedAnswers  = session('quiz_answers', []);
                        $selectedOption = $storedAnswers[$question->id] ?? null;
                    @endphp

                    {{-- Answer options --}}
                    @foreach ($question->answers as $answer)
                        <div class="mb-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="radio"
                                    name="selected_option"
                                    value="{{ $answer->id }}"
                                    class="h-4 w-4 text-indigo-600"
                                    {{ $selectedOption == $answer->id ? 'checked' : '' }}
                                    required
                                >
                                <span>{{ $answer->answer_text }}</span>
                            </label>
                        </div>
                    @endforeach

                    {{-- Hidden fields --}}
                    <input type="hidden" name="question_id" value="{{ $question->id }}">
                    <input type="hidden" name="current_question" value="{{ $questionIndex }}">
                </div>

                {{-- Navigation buttons --}}
                <div class="mt-6 flex justify-between">
                    {{-- Back button --}}
                    @if ($questionIndex > 0)
                        <a href="{{ route('lessons.quiz.paginated', [
                                'lesson'   => $lesson->id,
                                'question' => $questionIndex - 1,
                            ]) }}"
                           class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-5 py-2 rounded shadow">
                            ← Back
                        </a>
                    @else
                        <div></div>
                    @endif

                    {{-- Next / Submit button --}}
                    @php
                        $totalQuestions = $lesson->quiz?->questions?->count() ?? 0;
                    @endphp

                    @if ($questionIndex + 1 === $totalQuestions)
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded shadow">
                            ✅ Submit Quiz
                        </button>
                    @else
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded shadow">
                            ➡️ Next Question
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
