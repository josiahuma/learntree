<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseApprovalController extends Controller
{
    public function index()
    {
        // You can tweak the ordering as you like
        $courses = Course::with('instructor')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.courses.index', compact('courses'));
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'is_approved' => 'required|boolean',
        ]);

        $course->update([
            'is_approved' => $data['is_approved'],
        ]);

        return back()->with('success', "Course '{$course->title}' has been updated.");
    }
}
