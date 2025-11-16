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
        $quiz = $lesson->quiz;

        // If a quiz already exists you *could* redirect to edit,
        // but we’ll just allow creating (which will overwrite in store()).
        return view('quizzes.create', compact('lesson'));
    }

    /**
     * Store quiz + questions + answers for a lesson.
     */
    public function store(Request $request, Lesson $lesson)
    {
        $data = $request->validate([
            'questions'                         => 'required|array|min:1',
            'questions.*.question'              => 'required|string',
            'questions.*.correct_answer'        => 'required|integer|min:0|max:3',
            'questions.*.answers'               => 'required|array|size:4',
            'questions.*.answers.*.answer_text' => 'required|string',
        ]);

        DB::transaction(function () use ($data, $lesson) {
            $quiz = Quiz::firstOrCreate([
                'lesson_id' => $lesson->id,
            ]);

            // Clear old questions/answers
            $quiz->questions()->each(function (QuizQuestion $question) {
                $question->answers()->delete();
                $question->delete();
            });

            foreach ($data['questions'] as $questionData) {
                $question = QuizQuestion::create([
                    'quiz_id'  => $quiz->id,
                    'question' => $questionData['question'],
                ]);

                foreach ($questionData['answers'] as $index => $answerData) {
                    QuizAnswer::create([
                        'quiz_question_id' => $question->id,
                        'answer_text'      => $answerData['answer_text'],
                        'is_correct'       => ($index == $questionData['correct_answer']),
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

        // Eager load questions + answers
        $quiz->load(['questions.answers']);

        return view('quizzes.edit', compact('quiz', 'lesson'));
    }

    /**
     * Update existing quiz (questions + answers).
     *
     * Even though we "recreate" internally, the form is pre-filled so
     * instructors only change what they want.
     */
    public function update(Request $request, Quiz $quiz)
    {
        $data = $request->validate([
            'questions'                         => 'required|array|min:1',
            'questions.*.question'              => 'required|string',
            'questions.*.correct_answer'        => 'required|integer|min:0|max:3',
            'questions.*.answers'               => 'required|array|size:4',
            'questions.*.answers.*.answer_text' => 'required|string',
        ]);

        $lesson = $quiz->lesson;

        DB::transaction(function () use ($data, $quiz) {
            // Remove existing questions/answers
            $quiz->questions()->each(function (QuizQuestion $question) {
                $question->answers()->delete();
                $question->delete();
            });

            // Rebuild from submitted data
            foreach ($data['questions'] as $questionData) {
                $question = QuizQuestion::create([
                    'quiz_id'  => $quiz->id,
                    'question' => $questionData['question'],
                ]);

                foreach ($questionData['answers'] as $index => $answerData) {
                    QuizAnswer::create([
                        'quiz_question_id' => $question->id,
                        'answer_text'      => $answerData['answer_text'],
                        'is_correct'       => ($index == $questionData['correct_answer']),
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
        $questions     = $quiz->questions->values(); // 0-based index

        if (!isset($questions[$questionIndex])) {
            return redirect()
                ->route('lessons.show', $lesson->id)
                ->with('error', 'Question not found.');
        }

        $question = $questions[$questionIndex];

        return view('quizzes.take-paginated', [
            'lesson'        => $lesson,
            'quiz'          => $quiz,
            'question'      => $question,
            'questionIndex' => $questionIndex,
        ]);
    }

    /**
     * Paginated quiz: store answer for one question and move on.
     */
    public function storePaginatedAnswer(Request $request, Lesson $lesson)
    {
        $data = $request->validate([
            'question_id'      => 'required|exists:quiz_questions,id',
            'selected_option'  => 'required|integer',
            'current_question' => 'required|integer',
        ]);

        $answers = session()->get('quiz_answers', []);
        $answers[$data['question_id']] = $data['selected_option'];
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
     * Paginated quiz: final submit.
     */
    public function submitPaginated(Lesson $lesson)
    {
        $answers   = session()->get('quiz_answers', []);
        $correct   = 0;
        $questions = $lesson->quiz->questions;

        foreach ($questions as $question) {
            $selectedAnswerId = $answers[$question->id] ?? null;

            if ($selectedAnswerId) {
                $selectedAnswer = $question->answers()->find($selectedAnswerId);
                if ($selectedAnswer && $selectedAnswer->is_correct) {
                    $correct++;
                }
            }
        }

        $total      = $questions->count();
        $percentage = ($total > 0) ? ($correct / $total) * 100 : 0;

        QuizSubmission::create([
            'user_id'         => Auth::id(),
            'quiz_id'         => $lesson->quiz->id,
            'score'           => $percentage,
            'correct_answers' => $correct,
        ]);

        session()->forget('quiz_answers');

        return redirect()
            ->route('lessons.quiz.result', ['lesson' => $lesson->id])
            ->with('score', $correct)
            ->with('percentage', $percentage);
    }

    /**
     * Result page.
     */
    public function result(Lesson $lesson)
    {
        $score      = session('score');
        $percentage = session('percentage');
        $total      = $lesson->quiz->questions->count();

        return view('quizzes.result', compact('lesson', 'score', 'percentage', 'total'));
    }
}
