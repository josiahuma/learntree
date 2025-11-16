<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonCompletion;
use Illuminate\Support\Facades\Auth;

class LessonCompletionController extends Controller
{
    public function store(Lesson $lesson)
    {
        // Mark lesson as completed for this user
        LessonCompletion::firstOrCreate([
            'user_id'   => Auth::id(),
            'lesson_id' => $lesson->id,
        ]);

        // If this lesson has a quiz, send them to the paginated quiz
        if ($lesson->quiz) {
            return redirect()
                ->route('lessons.quiz.paginated', ['lesson' => $lesson->id, 'question' => 0]);
        }

        // Ensure this lesson has an order value (for older rows that may be null)
        if (is_null($lesson->order)) {
            $maxOrder = $lesson->course
                ? $lesson->course->lessons()->whereNotNull('order')->max('order')
                : null;

            $lesson->order = ($maxOrder ?? 0) + 1;
            $lesson->save();
        }

        // Try to find the next lesson in order within the same course
        $nextLesson = Lesson::where('course_id', $lesson->course_id)
            ->where('order', '>', $lesson->order)
            ->orderBy('order')
            ->first();

        if ($nextLesson) {
            return redirect()
                ->route('lessons.show', $nextLesson->id)
                ->with('success', 'Lesson completed. Proceed to the next lesson.');
        }

        // No next lesson → course completed, go to student dashboard
        return redirect()
            ->route('student.dashboard')
            ->with('success', 'Course completed. You can now download your certificate.');
    }
}
