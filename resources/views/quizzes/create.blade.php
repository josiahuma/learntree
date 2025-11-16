<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold">
                🧠 Create Quiz for: <span class="font-semibold">{{ $lesson->title }}</span>
            </h2>

            <a href="{{ route('lessons.show', $lesson) }}"
               class="text-sm text-gray-600 hover:text-gray-800 underline">
                ⬅ Back to Lesson
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 bg-white p-6 rounded shadow border">
            {{-- Intro / helper text --}}
            <div class="mb-4">
                <p class="text-sm text-gray-700">
                    Create a multiple-choice quiz for this lesson. Each question must have
                    <span class="font-semibold">4 answer options (A–D)</span> and exactly one correct answer.
                </p>
            </div>

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="mb-4 p-3 rounded bg-red-100 text-red-700 text-sm">
                    <p class="font-semibold mb-1">Please fix the following issues:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('quizzes.store', $lesson) }}" method="POST" id="quiz-form">
                @csrf

                <div id="questions-container">
                    {{-- Initial Question --}}
                    <div class="question-block mb-6 border p-4 rounded shadow-sm bg-gray-50 relative">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold">
                                Question 1
                            </h3>
                        </div>

                        <label class="block mb-1 text-sm font-medium">Question text</label>
                        <input
                            type="text"
                            name="questions[0][question]"
                            class="w-full border rounded px-3 py-2 mb-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Type the question here..."
                            required
                        >

                        <label class="block mb-1 text-sm font-medium">Answer options</label>
                        <p class="text-xs text-gray-500 mb-2">
                            Select the radio button next to the correct answer.
                        </p>

                        @foreach (['A', 'B', 'C', 'D'] as $index => $label)
                            <div class="flex items-center gap-2 mb-2">
                                <input
                                    type="radio"
                                    name="questions[0][correct_answer]"
                                    value="{{ $index }}"
                                    class="shrink-0"
                                    required
                                >
                                <input
                                    type="text"
                                    name="questions[0][answers][{{ $index }}][answer_text]"
                                    class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Option {{ $label }}"
                                    required
                                >
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between mt-4">
                    <button
                        type="button"
                        onclick="addQuestion()"
                        class="inline-flex items-center bg-yellow-400 hover:bg-yellow-500 text-black text-sm font-semibold px-4 py-2 rounded shadow-sm"
                    >
                        ➕ Add Another Question
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-6 py-2 rounded shadow"
                    >
                        ✅ Save Quiz
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let questionIndex = 1;

        function addQuestion() {
            const container = document.getElementById('questions-container');

            const block = document.createElement('div');
            block.className = 'question-block mb-6 border p-4 rounded shadow-sm bg-gray-50 relative';

            block.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold">
                        Question ${questionIndex + 1}
                    </h3>
                    <button type="button"
                        class="text-xs text-red-600 hover:text-red-800"
                        onclick="removeQuestion(this)">
                        ✖ Remove
                    </button>
                </div>

                <label class="block mb-1 text-sm font-medium">Question text</label>
                <input
                    type="text"
                    name="questions[${questionIndex}][question]"
                    class="w-full border rounded px-3 py-2 mb-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Type the question here..."
                    required
                >

                <label class="block mb-1 text-sm font-medium">Answer options</label>
                <p class="text-xs text-gray-500 mb-2">
                    Select the radio button next to the correct answer.
                </p>

                ${['A', 'B', 'C', 'D'].map((label, i) => `
                    <div class="flex items-center gap-2 mb-2">
                        <input
                            type="radio"
                            name="questions[${questionIndex}][correct_answer]"
                            value="${i}"
                            class="shrink-0"
                            required
                        >
                        <input
                            type="text"
                            name="questions[${questionIndex}][answers][${i}][answer_text]"
                            class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Option ${label}"
                            required
                        >
                    </div>
                `).join('')}
            `;

            container.appendChild(block);
            questionIndex++;
        }

        function removeQuestion(button) {
            const block = button.closest('.question-block');
            if (block) {
                block.remove();
            }
        }
    </script>
</x-app-layout>
