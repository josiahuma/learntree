<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function create(Course $course)
    {
        return view('lessons.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        // Only instructors can add lessons
        if (auth()->user()->role !== 'instructor') {
            abort(403, 'Only instructors can add lessons.');
        }

        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'nullable|string',
            'video_url' => 'nullable|url',
        ]);

        // Determine the next order value for this course
        $nextOrder = ($course->lessons()->max('order') ?? 0) + 1;

        $course->lessons()->create([
            'title'     => $data['title'],
            'content'   => $data['content'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'order'     => $nextOrder,
        ]);

        return redirect()
            ->route('courses.show', $course)
            ->with('success', 'Lesson created!');
    }

    public function edit(Lesson $lesson)
    {
        return view('lessons.edit', compact('lesson'));
    }

    public function update(Request $request, Lesson $lesson)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'nullable|string',
            'video_url' => 'nullable|url',
        ]);

        $lesson->update($data);

        return redirect()
            ->route('courses.show', $lesson->course_id)
            ->with('success', 'Lesson updated.');
    }

    public function destroy(Lesson $lesson)
    {
        $courseId = $lesson->course_id;
        $lesson->delete();

        return redirect()
            ->route('courses.show', $courseId)
            ->with('success', 'Lesson deleted.');
    }

    public function show(Lesson $lesson)
    {
        // Ensure this lesson has an order value (helpful for older data)
        if (is_null($lesson->order)) {
            $maxOrder = $lesson->course
                ? $lesson->course->lessons()->whereNotNull('order')->max('order')
                : null;

            $lesson->order = ($maxOrder ?? 0) + 1;
            $lesson->save();
        }

        // Find the next lesson in this course by order
        $nextLesson = Lesson::where('course_id', $lesson->course_id)
            ->where('order', '>', $lesson->order)
            ->orderBy('order')
            ->first();

        return view('lessons.show', compact('lesson', 'nextLesson'));
    }

    public function quiz(Lesson $lesson)
    {
        return view('lessons.quiz', compact('lesson'));
    }

    public function submitQuiz(Request $request, Lesson $lesson)
    {
        // Placeholder – real scoring logic would go here.
        session()->flash('quiz_passed', true);

        return redirect()
            ->route('lessons.show', $lesson)
            ->with('success', 'Quiz submitted!');
    }
}
