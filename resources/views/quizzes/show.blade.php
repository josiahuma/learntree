<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">
                🧪 Quiz Overview — <span class="font-semibold">{{ $quiz->lesson->title }}</span>
            </h2>

            <a href="{{ route('lessons.show', $quiz->lesson) }}"
               class="text-sm text-indigo-600 hover:text-indigo-800 underline">
                ⬅ Back to Lesson
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 space-y-8">

            @if ($quiz->questions->count() === 0)
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-lg">
                    ⚠️ This quiz has no questions yet.
                </div>
            @endif

            @foreach ($quiz->questions as $index => $question)
                <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-6 space-y-4">

                    {{-- Question header --}}
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Question {{ $index + 1 }}</h3>
                        <span class="text-xs text-gray-500">
                            {{ $question->answers->count() }} options
                        </span>
                    </div>

                    {{-- Question text --}}
                    <p class="text-gray-800 leading-relaxed">
                        {{ $question->question }}
                    </p>

                    {{-- Answer list --}}
                    <ul class="space-y-2">
                        @foreach ($question->answers as $answer)
                            <li class="flex items-center gap-3 p-3 rounded-lg border
                                @if($answer->is_correct)
                                    bg-green-50 border-green-300 text-green-800 font-semibold
                                @else
                                    bg-gray-50 border-gray-200 text-gray-700
                                @endif
                            ">
                                <div class="flex-1">
                                    {{ $answer->answer_text }}
                                </div>

                                @if($answer->is_correct)
                                    <span class="text-green-700 text-sm font-medium">✔ Correct</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            {{-- Back button --}}
            <div class="text-center pt-4">
                <a href="{{ route('lessons.show', $quiz->lesson) }}"
                   class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-semibold text-sm underline">
                    ⬅ Back to Lesson
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
