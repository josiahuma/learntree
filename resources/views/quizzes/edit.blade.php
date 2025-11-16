<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold">
                ✏️ Edit Quiz for: <span class="font-semibold">{{ $lesson->title }}</span>
            </h2>

            <a href="{{ route('lessons.show', $lesson) }}"
               class="text-sm text-gray-600 hover:text-gray-800 underline">
                ⬅ Back to Lesson
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 bg-white p-6 rounded shadow border">
            <div class="mb-4">
                <p class="text-sm text-gray-700">
                    Update the questions and answers for this quiz. Each question can have
                    <span class="font-semibold">2–10 options</span>, and you can tick
                    <span class="font-semibold">one or more correct answers</span>.
                </p>
            </div>

            @if($errors->any())
                <div class="mb-4 p-3 rounded bg-red-100 text-red-700 text-sm">
                    <p class="font-semibold mb-1">Please fix the following issues:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('quizzes.update', $quiz) }}" method="POST" id="quiz-form">
                @csrf
                @method('PUT')

                @php
                    $existingCount = $quiz->questions->count();
                @endphp

                <div id="questions-container">
                    @foreach($quiz->questions as $qIndex => $question)
                        @php
                            // Ensure we have a zero-based, contiguous list of answers
                            $answers = $question->answers->values();
                            $answerCount = max($answers->count(), 2);
                        @endphp

                        <div class="question-block mb-6 border p-4 rounded shadow-sm bg-gray-50 relative"
                             data-question-index="{{ $qIndex }}">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-lg font-semibold">
                                    Question <span class="question-number">{{ $loop->iteration }}</span>
                                </h3>

                                @if ($qIndex > 0)
                                    <button type="button"
                                            class="text-xs text-red-600 hover:text-red-800"
                                            onclick="removeQuestion(this)">
                                        ✖ Remove Question
                                    </button>
                                @endif
                            </div>

                            <label class="block mb-1 text-sm font-medium">Question text</label>
                            <input
                                type="text"
                                name="questions[{{ $qIndex }}][question]"
                                class="w-full border rounded px-3 py-2 mb-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                value="{{ old("questions.$qIndex.question", $question->question) }}"
                                placeholder="Type the question here..."
                                required
                            >

                            <label class="block mb-1 text-sm font-medium">Answer options</label>
                            <p class="text-xs text-gray-500 mb-2">
                                Tick the checkbox next to each correct answer. You must have at least 2 options.
                            </p>

                            <div id="answers-{{ $qIndex }}"
                                 class="space-y-2"
                                 data-next-index="{{ $answers->count() }}">
                                @foreach($answers as $aIndex => $answer)
                                    <div class="answer-row flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            name="questions[{{ $qIndex }}][answers][{{ $aIndex }}][is_correct]"
                                            value="1"
                                            class="shrink-0"
                                            {{ old("questions.$qIndex.answers.$aIndex.is_correct", $answer->is_correct) ? 'checked' : '' }}
                                        >
                                        <input
                                            type="text"
                                            name="questions[{{ $qIndex }}][answers][{{ $aIndex }}][answer_text]"
                                            class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            value="{{ old("questions.$qIndex.answers.$aIndex.answer_text", $answer->answer_text) }}"
                                            placeholder="Option {{ chr(65 + $aIndex) }}"
                                        >
                                        <button type="button"
                                                class="remove-answer text-xs text-red-600 hover:text-red-800 px-2 py-1 rounded {{ $answerCount <= 2 ? 'hidden' : '' }}"
                                                onclick="removeAnswer(this, {{ $qIndex }})">
                                            ✖
                                        </button>
                                    </div>
                                @endforeach

                                {{-- If somehow fewer than 2 answers exist, pad with empty ones --}}
                                @for($aIndex = $answers->count(); $aIndex < 2; $aIndex++)
                                    <div class="answer-row flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            name="questions[{{ $qIndex }}][answers][{{ $aIndex }}][is_correct]"
                                            value="1"
                                            class="shrink-0"
                                        >
                                        <input
                                            type="text"
                                            name="questions[{{ $qIndex }}][answers][{{ $aIndex }}][answer_text]"
                                            class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            placeholder="Option {{ chr(65 + $aIndex) }}"
                                        >
                                        <button type="button"
                                                class="remove-answer text-xs text-red-600 hover:text-red-800 px-2 py-1 rounded hidden"
                                                onclick="removeAnswer(this, {{ $qIndex }})">
                                            ✖
                                        </button>
                                    </div>
                                @endfor
                            </div>

                            <div class="mt-3 flex items-center justify-between">
                                <button type="button"
                                        class="text-xs bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded font-semibold"
                                        onclick="addAnswer({{ $qIndex }})">
                                    ➕ Add Option
                                </button>
                                <span class="text-[10px] text-gray-400">Max 10 options per question</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between mt-4">
                    <button
                        type="button"
                        onclick="addQuestion()"
                        class="inline-flex items-center bg-yellow-400 hover:bg-yellow-500 text-black text-sm font-semibold px-4 py-2 rounded shadow-sm">
                        ➕ Add Another Question
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-6 py-2 rounded shadow">
                        💾 Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let questionIndex = {{ $existingCount }}; // next index for new questions
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

            // Build 2 initial options
            let answersHtml = '';
            for (let i = 0; i < MIN_OPTIONS; i++) {
                const labelChar = String.fromCharCode(65 + i);
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
                            placeholder="Option ${labelChar}"
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
            const labelChar = String.fromCharCode(65 + nextIndex);

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

            if (rows.length > MIN_OPTIONS) {
                removeButtons.forEach(btn => btn.classList.remove('hidden'));
            } else {
                removeButtons.forEach(btn => btn.classList.add('hidden'));
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Ensure existing questions' remove buttons are correctly shown/hidden
            document.querySelectorAll('[id^="answers-"]').forEach(container => {
                toggleRemoveButtons(container);
            });
        });
    </script>
</x-app-layout>
