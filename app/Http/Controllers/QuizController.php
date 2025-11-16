<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use App\Models\QuizSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * Show the create-quiz form for a lesson.
     */
    public function create(Lesson $lesson)
    {
        return view('quizzes.create', compact('lesson'));
    }

    /**
     * Normalise questions/answers from the request:
     * - keep only non-empty answers
     * - ensure at least 2 answers
     * - ensure at least 1 correct answer
     */
    protected function normaliseQuestions(array $questionsRaw)
    {
        $normalised = [];

        foreach ($questionsRaw as $qIndex => $questionData) {
            $questionText = trim($questionData['question'] ?? '');
            $answersRaw   = $questionData['answers'] ?? [];

            $answers = [];
            $hasCorrect = false;

            foreach ($answersRaw as $aIndex => $answerData) {
                $text = trim($answerData['answer_text'] ?? '');

                if ($text === '') {
                    continue; // ignore blank options
                }

                $isCorrect = !empty($answerData['is_correct']);

                if ($isCorrect) {
                    $hasCorrect = true;
                }

                $answers[] = [
                    'answer_text' => $text,
                    'is_correct'  => $isCorrect,
                ];
            }

            if (count($answers) < 2) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "questions.$qIndex" => "Question " . ($qIndex + 1) . " must have at least 2 answer options.",
                ]);
            }

            if (!$hasCorrect) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "questions.$qIndex" => "Question " . ($qIndex + 1) . " must have at least one correct answer.",
                ]);
            }

            $normalised[] = [
                'question' => $questionText,
                'answers'  => $answers,
            ];
        }

        return $normalised;
    }

    /**
     * Store quiz + questions + answers for a lesson.
     */
    public function store(Request $request, Lesson $lesson)
    {
        $data = $request->validate([
            'questions'                         => 'required|array|min:1',
            'questions.*.question'              => 'required|string',
            'questions.*.answers'               => 'required|array|min:1',
            'questions.*.answers.*.answer_text' => 'nullable|string',
            'questions.*.answers.*.is_correct'  => 'nullable|boolean',
        ]);

        // Clean & validate per question (2–4 answers, at least 1 correct)
        $questions = $this->normaliseQuestions($data['questions']);

        DB::transaction(function () use ($questions, $lesson) {
            $quiz = Quiz::firstOrCreate([
                'lesson_id' => $lesson->id,
            ]);

            // Clear old questions/answers
            $quiz->questions()->each(function (QuizQuestion $question) {
                $question->answers()->delete();
                $question->delete();
            });

            // Rebuild from normalised data
            foreach ($questions as $qData) {
                $question = QuizQuestion::create([
                    'quiz_id'  => $quiz->id,
                    'question' => $qData['question'],
                ]);

                foreach ($qData['answers'] as $answerData) {
                    QuizAnswer::create([
                        'quiz_question_id' => $question->id,
                        'answer_text'      => $answerData['answer_text'],
                        'is_correct'       => $answerData['is_correct'],
                    ]);
                }
            }
        });

        return redirect()
            ->route('lessons.show', $lesson)
            ->with('success', 'Quiz created successfully for this lesson.');
    }

    /**
     * Show the edit form with existing quiz data.
     */
    public function edit(Quiz $quiz)
    {
        $lesson = $quiz->lesson;
        $quiz->load(['questions.answers']);

        return view('quizzes.edit', compact('quiz', 'lesson'));
    }

    /**
     * Update existing quiz (questions + answers).
     */
    public function update(Request $request, Quiz $quiz)
    {
        $data = $request->validate([
            'questions'                         => 'required|array|min:1',
            'questions.*.question'              => 'required|string',
            'questions.*.answers'               => 'required|array|min:1',
            'questions.*.answers.*.answer_text' => 'nullable|string',
            'questions.*.answers.*.is_correct'  => 'nullable|boolean',
        ]);

        $questions = $this->normaliseQuestions($data['questions']);
        $lesson    = $quiz->lesson;

        DB::transaction(function () use ($questions, $quiz) {
            $quiz->questions()->each(function (QuizQuestion $question) {
                $question->answers()->delete();
                $question->delete();
            });

            foreach ($questions as $qData) {
                $question = QuizQuestion::create([
                    'quiz_id'  => $quiz->id,
                    'question' => $qData['question'],
                ]);

                foreach ($qData['answers'] as $answerData) {
                    QuizAnswer::create([
                        'quiz_question_id' => $question->id,
                        'answer_text'      => $answerData['answer_text'],
                        'is_correct'       => $answerData['is_correct'],
                    ]);
                }
            }
        });

        return redirect()
            ->route('lessons.show', $lesson)
            ->with('success', 'Quiz updated successfully.');
    }

    /**
     * Delete quiz and its questions/answers.
     */
    public function destroy(Quiz $quiz)
    {
        $lesson = $quiz->lesson;

        DB::transaction(function () use ($quiz) {
            $quiz->questions()->each(function (QuizQuestion $question) {
                $question->answers()->delete();
                $question->delete();
            });

            $quiz->delete();
        });

        return redirect()
            ->route('lessons.show', $lesson)
            ->with('success', 'Quiz deleted successfully.');
    }

    /**
     * Paginated quiz: show current question.
     */
    public function takePaginated(Request $request, Lesson $lesson)
    {
        $quiz = $lesson->quiz;

        if (!$quiz) {
            return redirect()
                ->route('lessons.show', $lesson->id)
                ->with('error', 'Quiz not found for this lesson.');
        }

        $questionIndex = (int) $request->query('question', 0);
        $questions     = $quiz->questions()->with('answers')->get()->values();

        if (!isset($questions[$questionIndex])) {
            return redirect()
                ->route('lessons.show', $lesson->id)
                ->with('error', 'Question not found.');
        }

        $question = $questions[$questionIndex];

        // Load previously selected options from session (for back-navigation)
        $stored = session()->get('quiz_answers', []);
        $selectedIds = isset($stored[$question->id])
            ? (array) $stored[$question->id]
            : [];

        return view('quizzes.take-paginated', [
            'lesson'        => $lesson,
            'quiz'          => $quiz,
            'question'      => $question,
            'questionIndex' => $questionIndex,
            'questions'     => $questions,
            'selectedIds'   => $selectedIds,
        ]);
    }

    /**
     * Paginated quiz: store answer for one question and move on.
     * Supports multiple selected options.
     */
    public function storePaginatedAnswer(Request $request, Lesson $lesson)
    {
        $data = $request->validate([
            'question_id'       => 'required|exists:quiz_questions,id',
            'selected_options'  => 'required|array|min:1',
            'selected_options.*'=> 'integer',
            'current_question'  => 'required|integer',
        ]);

        $answers = session()->get('quiz_answers', []);
        $answers[$data['question_id']] = $data['selected_options'];
        session()->put('quiz_answers', $answers);

        $totalQuestions = $lesson->quiz->questions()->count();

        if ($data['current_question'] + 1 >= $totalQuestions) {
            return redirect()
                ->route('lessons.quiz.paginated.submit', ['lesson' => $lesson->id]);
        }

        return redirect()->route('lessons.quiz.paginated', [
            'lesson'   => $lesson->id,
            'question' => $data['current_question'] + 1,
        ]);
    }

    /**
     * Paginated quiz: final submit – score based on exact match of
     * selected answers vs correct answers (supports multi-correct).
     */
    public function submitPaginated(Lesson $lesson)
    {
        $answers   = session()->get('quiz_answers', []);
        $questions = $lesson->quiz->questions()->with('answers')->get();

        $correctCount = 0;

        foreach ($questions as $question) {
            $selectedIds = collect($answers[$question->id] ?? [])
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values();

            $correctIds = $question->answers
                ->where('is_correct', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values();

            if ($selectedIds->isEmpty() || $correctIds->isEmpty()) {
                continue;
            }

            // Exact set match: no extra or missing answers
            if ($selectedIds->diff($correctIds)->isEmpty() &&
                $correctIds->diff($selectedIds)->isEmpty()) {
                $correctCount++;
            }
        }

        $total      = max($questions->count(), 1);
        $percentage = ($correctCount / $total) * 100;

        QuizSubmission::create([
            'user_id'         => Auth::id(),
            'quiz_id'         => $lesson->quiz->id,
            'score'           => $percentage,
            'correct_answers' => $correctCount,
        ]);

        session()->forget('quiz_answers');

        return redirect()
            ->route('lessons.quiz.result', ['lesson' => $lesson->id])
            ->with('score', $correctCount)
            ->with('percentage', $percentage);
    }

    /**
     * Result page.
     */
    public function result(Lesson $lesson)
    {
        $score      = session('score');
        $percentage = session('percentage');
        $total      = $lesson->quiz->questions()->count();

        return view('quizzes.result', compact('lesson', 'score', 'percentage', 'total'));
    }
}
