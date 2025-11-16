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
                    Create a quiz for this lesson. Each question can have
                    <span class="font-semibold">2–10 answer options</span>, and you can tick
                    <span class="font-semibold">one or more correct answers</span>.
                    For True/False, just keep 2 options. For “select all that apply”, tick multiple.
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
                    {{-- Initial Question (index 0) --}}
                    <div class="question-block mb-6 border p-4 rounded shadow-sm bg-gray-50 relative"
                         data-question-index="0">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold">
                                Question <span class="question-number">1</span>
                            </h3>
                            {{-- Only extra questions will show remove (added via JS) --}}
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
                            Tick the checkbox next to each correct answer. You must have at least 2 options.
                        </p>

                        <div id="answers-0"
                             class="space-y-2"
                             data-next-index="2">
                            {{-- Start with 2 options (index 0 and 1) --}}
                            @for ($i = 0; $i < 2; $i++)
                                <div class="answer-row flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="questions[0][answers][{{ $i }}][is_correct]"
                                        value="1"
                                        class="shrink-0"
                                    >
                                    <input
                                        type="text"
                                        name="questions[0][answers][{{ $i }}][answer_text]"
                                        class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="Option {{ chr(65 + $i) }} (e.g. True, False, etc.)"
                                    >
                                    {{-- Remove button (hidden when only 2 options) --}}
                                    <button type="button"
                                            class="remove-answer text-xs text-red-600 hover:text-red-800 px-2 py-1 rounded hidden"
                                            onclick="removeAnswer(this, 0)">
                                        ✖
                                    </button>
                                </div>
                            @endfor
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <button type="button"
                                    class="text-xs bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded font-semibold"
                                    onclick="addAnswer(0)">
                                ➕ Add Option
                            </button>
                            <span class="text-[10px] text-gray-400">Max 10 options per question</span>
                        </div>
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
        let questionIndex = 1; // we already used 0
        const MAX_OPTIONS = 10;
        const MIN_OPTIONS = 2;

        function renumberQuestions() {
            const blocks = document.querySelectorAll('.question-block');
            blocks.forEach((block, idx) => {
                const numberEl = block.querySelector('.question-number');
                if (numberEl) {
                    numberEl.textContent = idx + 1;
                }
            });
        }

        function addQuestion() {
            const container = document.getElementById('questions-container');

            const block = document.createElement('div');
            block.className = 'question-block mb-6 border p-4 rounded shadow-sm bg-gray-50 relative';
            block.setAttribute('data-question-index', questionIndex);

            // Build 2 initial answers (index 0 & 1)
            let answersHtml = '';
            for (let i = 0; i < MIN_OPTIONS; i++) {
                answersHtml += `
                    <div class="answer-row flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="questions[${questionIndex}][answers][${i}][is_correct]"
                            value="1"
                            class="shrink-0"
                        >
                        <input
                            type="text"
                            name="questions[${questionIndex}][answers][${i}][answer_text]"
                            class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Option ${String.fromCharCode(65 + i)}"
                        >
                        <button type="button"
                                class="remove-answer text-xs text-red-600 hover:text-red-800 px-2 py-1 rounded hidden"
                                onclick="removeAnswer(this, ${questionIndex})">
                            ✖
                        </button>
                    </div>
                `;
            }

            block.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold">
                        Question <span class="question-number">${questionIndex + 1}</span>
                    </h3>
                    <button type="button"
                        class="text-xs text-red-600 hover:text-red-800"
                        onclick="removeQuestion(this)">
                        ✖ Remove Question
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
                    Tick the checkbox next to each correct answer. You must have at least 2 options.
                </p>

                <div id="answers-${questionIndex}"
                     class="space-y-2"
                     data-next-index="${MIN_OPTIONS}">
                    ${answersHtml}
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <button type="button"
                            class="text-xs bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded font-semibold"
                            onclick="addAnswer(${questionIndex})">
                        ➕ Add Option
                    </button>
                    <span class="text-[10px] text-gray-400">Max 10 options per question</span>
                </div>
            `;

            container.appendChild(block);
            questionIndex++;
            renumberQuestions();
        }

        function removeQuestion(button) {
            const block = button.closest('.question-block');
            if (!block) return;

            const container = document.getElementById('questions-container');
            container.removeChild(block);

            // If we removed some question, we don't renumber input names
            // (PHP handles sparse indexes just fine), but we *do* renumber titles.
            renumberQuestions();
        }

        function addAnswer(qIndex) {
            const container = document.getElementById(`answers-${qIndex}`);
            if (!container) return;

            const currentCount = container.querySelectorAll('.answer-row').length;
            if (currentCount >= MAX_OPTIONS) {
                alert(`You can only have up to ${MAX_OPTIONS} options for a question.`);
                return;
            }

            let nextIndex = parseInt(container.dataset.nextIndex || currentCount, 10);
            const labelChar = String.fromCharCode(65 + nextIndex); // A, B, C...

            const row = document.createElement('div');
            row.className = 'answer-row flex items-center gap-2';
            row.innerHTML = `
                <input
                    type="checkbox"
                    name="questions[${qIndex}][answers][${nextIndex}][is_correct]"
                    value="1"
                    class="shrink-0"
                >
                <input
                    type="text"
                    name="questions[${qIndex}][answers][${nextIndex}][answer_text]"
                    class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Option ${labelChar}"
                >
                <button type="button"
                        class="remove-answer text-xs text-red-600 hover:text-red-800 px-2 py-1 rounded"
                        onclick="removeAnswer(this, ${qIndex})">
                    ✖
                </button>
            `;

            container.appendChild(row);
            container.dataset.nextIndex = nextIndex + 1;

            toggleRemoveButtons(container);
        }

        function removeAnswer(button, qIndex) {
            const container = document.getElementById(`answers-${qIndex}`);
            if (!container) return;

            const rows = container.querySelectorAll('.answer-row');
            if (rows.length <= MIN_OPTIONS) {
                alert(`You must have at least ${MIN_OPTIONS} options.`);
                return;
            }

            const row = button.closest('.answer-row');
            if (row) {
                container.removeChild(row);
            }

            toggleRemoveButtons(container);
        }

        function toggleRemoveButtons(container) {
            const rows = container.querySelectorAll('.answer-row');
            const removeButtons = container.querySelectorAll('.remove-answer');

            // Show remove buttons only if more than MIN_OPTIONS options
            if (rows.length > MIN_OPTIONS) {
                removeButtons.forEach(btn => btn.classList.remove('hidden'));
            } else {
                removeButtons.forEach(btn => btn.classList.add('hidden'));
            }
        }

        // Ensure initial question's remove buttons are correctly hidden
        document.addEventListener('DOMContentLoaded', () => {
            const initialContainer = document.getElementById('answers-0');
            if (initialContainer) {
                toggleRemoveButtons(initialContainer);
            }
        });
    </script>
</x-app-layout>
