<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold">Question {{ $index + 1 }} of {{ $total }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 bg-white p-6 rounded shadow border">
            <form method="POST" action="{{ route('lessons.quiz.paginated.store', $lesson) }}">
                @csrf
                <input type="hidden" name="index" value="{{ $index }}">
                <input type="hidden" name="question_id" value="{{ $question->id }}">

                <h3 class="font-semibold mb-4 text-lg">{{ $question->question }}</h3>

                @foreach ($question->answers as $answer)
                    <div class="mb-3">
                        <label class="flex items-center gap-3">
                            <input type="radio" name="answer_id" value="{{ $answer->id }}" required>
                            <span>{{ $answer->answer_text }}</span>
                        </label>
                    </div>
                @endforeach

                <div class="mt-6">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-semibold">
                        {{ $index + 1 == $total ? '✅ Submit Quiz' : '➡️ Next Question' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
