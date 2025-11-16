<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    // GET /lessons/{lesson}/quiz/take?question=0
    public function takePaginated(Lesson $lesson)
    {
        $quiz = $lesson->quiz;

        if (! $quiz) {
            return redirect()
                ->route('lessons.show', $lesson)
                ->with('error', 'This lesson has no quiz yet.');
        }

        $questions = $quiz->questions()->with('answers')->get();

        if ($questions->isEmpty()) {
            return redirect()
                ->route('lessons.show', $lesson)
                ->with('error', 'This quiz has no questions.');
        }

        // question index comes from the query string, default 0
        $questionIndex = (int) request('question', 0);

        // clamp to valid range
        if ($questionIndex < 0 || $questionIndex >= $questions->count()) {
            $questionIndex = 0;
        }

        $question = $questions[$questionIndex];

        return view('quizzes.take-paginated', [
            'lesson'        => $lesson,
            'question'      => $question,
            'questionIndex' => $questionIndex,
            'questions'     => $questions,
        ]);
    }

    // POST /lessons/{lesson}/quiz/take
    public function storePaginatedAnswer(Request $request, Lesson $lesson)
    {
        // This matches your blade fields: selected_option + current_question
        $data = $request->validate([
            'selected_option'  => 'required|integer',
            'question_id'      => 'required|integer',
            'current_question' => 'required|integer',
        ]);

        // Store answers in session keyed by question_id
        $answers = session('quiz_answers', []);
        $answers[$data['question_id']] = $data['selected_option'];
        session(['quiz_answers' => $answers]);

        $currentIndex    = (int) $data['current_question'];
        $totalQuestions  = $lesson->quiz->questions()->count();
        $nextIndex       = $currentIndex + 1;

        // If we’re past the last question, go to final submit
        if ($nextIndex >= $totalQuestions) {
            return redirect()->route('lessons.quiz.paginated.submit', $lesson);
        }

        // Otherwise go to the next question (via query param)
        return redirect()->route('lessons.quiz.paginated', [
            'lesson'   => $lesson->id,
            'question' => $nextIndex,
        ]);
    }

    // GET /lessons/{lesson}/quiz/submit
    public function submitPaginated(Lesson $lesson)
    {
        $quiz = $lesson->quiz;

        if (! $quiz) {
            return redirect()
                ->route('lessons.show', $lesson)
                ->with('error', 'This lesson has no quiz.');
        }

        $questions = $quiz->questions()->with('answers')->get();
        $answers   = session('quiz_answers', []);

        $correct = 0;

        foreach ($questions as $question) {
            $selectedId = $answers[$question->id] ?? null;
            if (! $selectedId) {
                continue;
            }

            $correctAnswer = $question->answers->firstWhere('is_correct', true);

            if ($correctAnswer && (int) $correctAnswer->id === (int) $selectedId) {
                $correct++;
            }
        }

        $totalQuestions = max($questions->count(), 1);
        $score          = $correct;
        $percentage     = ($correct / $totalQuestions) * 100;

        // Store attempt (assuming you have quizAttempts() on Lesson)
        $lesson->quizAttempts()->create([
            'user_id' => Auth::id(),
            'score'   => $score,
        ]);

        // Clear stored answers for this quiz
        session()->forget('quiz_answers');

        // Redirect to result page with score info
        return redirect()->route('lessons.quiz.result', $lesson)->with([
            'score'          => $score,
            'percentage'     => $percentage,
            'totalQuestions' => $totalQuestions,
        ]);
    }

    // GET /lessons/{lesson}/quiz/result
    public function result(Lesson $lesson)
    {
        // Pull from flash data (or you can recompute from latest attempt)
        $score          = session('score');
        $percentage     = session('percentage');
        $totalQuestions = session('totalQuestions');

        // Fallback: if someone hits this URL directly, you can load last attempt
        if ($score === null || $percentage === null || $totalQuestions === null) {
            $lastAttempt = $lesson->quizAttempts()
                                  ->where('user_id', Auth::id())
                                  ->latest()
                                  ->first();

            if ($lastAttempt) {
                $totalQuestions = $lesson->quiz->questions()->count();
                $score          = $lastAttempt->score;
                $percentage     = $totalQuestions > 0
                    ? ($score / $totalQuestions) * 100
                    : 0;
            }
        }

        return view('quizzes.result', compact(
            'lesson',
            'score',
            'percentage',
            'totalQuestions'
        ));
    }
}
