<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\LessonCompletion;
use App\Models\QuizSubmission;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Load enrollments + course + lessons + quizzes in one go
        $enrollments = $user->enrollments()
            ->with('course.lessons.quiz.questions')
            ->get();

        // All completed lessons for this user
        $completions = LessonCompletion::where('user_id', $user->id)
            ->pluck('lesson_id')
            ->toArray();

        // Best quiz submissions per quiz (highest score)
        $submissions = QuizSubmission::where('user_id', $user->id)
            ->selectRaw('MAX(score) as score, MAX(correct_answers) as correct_answers, quiz_id')
            ->groupBy('quiz_id')
            ->get()
            ->keyBy('quiz_id');

        $progress = [];

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;

            // Safety: if an enrollment has no associated course, skip it
            if (!$course) {
                continue;
            }

            // Make sure we always have a collection
            $lessons       = $course->lessons ?? collect();
            $totalLessons  = $lessons->count();
            $completedLessons = $lessons->whereIn('id', $completions)->count();

            // Check quiz pass status for this course
            $allPassed = true;

            foreach ($lessons as $lesson) {
                if ($lesson->quiz) {
                    $quizId = $lesson->quiz->id;

                    // Might be null if user never attempted this quiz
                    $submission = $submissions[$quizId] ?? null;
                    $score      = $submission->score ?? 0;

                    if ($score < 80) {
                        $allPassed = false;
                        break;
                    }
                }
            }

            $progress[$course->id] = [
                'completedLessons'       => $completedLessons,
                'totalLessons'           => $totalLessons,
                'eligibleForCertificate' =>
                    $totalLessons > 0 &&
                    $completedLessons === $totalLessons &&
                    $allPassed,
            ];
        }

        return view('student.dashboard', compact('enrollments', 'completions', 'submissions', 'progress'));
    }
}
