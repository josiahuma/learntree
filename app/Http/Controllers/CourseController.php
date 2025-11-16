<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['instructor', 'reviews'])
            ->where('is_approved', 1); // ✅ Only approved courses

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->filled('price')) {
            if ($request->price === 'free') {
                $query->where('price', 0);
            } elseif ($request->price === 'paid') {
                $query->where('price', '>', 0);
            }
        }

        $courses = $query->paginate(12);

        return view('courses.index', compact('courses'));
    }



    public function create()
    {
        if (auth()->user()->role !== 'instructor') {
            abort(403, 'Only instructors can create courses.');
        }

        return view('courses.create');
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'instructor') {
            abort(403);
        }

       $data = $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'difficulty' => 'required|string|in:beginner,intermediate,advanced',
            'duration' => 'nullable|string|max:100',
            'featured_image' => 'nullable|image|max:5120', // 50MB max
        ]);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('courses', 'public');
        }

        $data['duration'] = $request->input('duration');
        $data['user_id'] = auth()->id();
        Course::create($data);


        return redirect()->route('courses.index')->with('success', 'Course created!');
    }

    public function show(Course $course)
    {
        $user = auth()->user();
        $isEnrolled = false;

        if ($user && $user->role === 'student') {
            $isEnrolled = $user->enrolledCourses->contains($course->id);
        }

        return view('courses.show', compact('course', 'isEnrolled'));
    }


    public function edit(Course $course)
    {
        return view('courses.edit', compact('course'));
    }


    public function update(Request $request, Course $course)
    {
        // 1. Validate
        $data = $request->validate([
            'title'          => 'required|string',
            'description'    => 'nullable|string',
            'price'          => 'required|numeric',
            'sale_price'     => 'nullable|numeric',
            'difficulty'     => 'required|string|in:beginner,intermediate,advanced',
            'duration'       => 'nullable|string|max:100',
            'featured_image' => 'nullable|image|max:5120', // 5MB
        ]);

        // 2. Handle image upload (if present)
        if ($request->hasFile('featured_image')) {
            // optional: delete old image
            if ($course->featured_image) {
                Storage::disk('public')->delete($course->featured_image);
            }

            $path = $request->file('featured_image')->store('courses', 'public');
            $data['featured_image'] = $path;
        } else {
            // Don't overwrite existing image when no new file is uploaded
            unset($data['featured_image']);
        }

        // 3. Update course
        $course->update($data);

        return redirect()
            ->route('courses.show', $course)
            ->with('success', 'Course updated.');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('courses.index')->with('success', 'Course deleted.');
    }

    public function students(Course $course)
    {
        if ($course->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $students = $course->students; // uses the 'enrollments' pivot

        return view('courses.students', compact('course', 'students'));
    }

    public function landing()
    {
        // Always have a settings row
        $settings = SiteSetting::firstOrCreate([], [
            'hero_background_color' => '#4f46e5',
            'featured_course_ids'   => [],
        ]);

        $featuredIds = $settings->featured_course_ids ?? [];

        // Base query with relations
        $query = Course::with(['instructor', 'reviews'])
                    ->where('is_approved', 1); // 👈 NEW filter

        if (!empty($featuredIds)) {
            // Only featured courses, keep same order as in settings
            $idList = implode(',', $featuredIds);

            $query->whereIn('id', $featuredIds)
                ->orderByRaw("FIELD(id, {$idList})");
        } else {
            // Fallback: latest 8 approved courses
            $query->latest()->take(8);
        }

        $courses = $query->get();

        return view('home', compact('settings', 'courses'));
    }



    public function search(Request $request)
    {
        $query = $request->input('query');
        $courses = Course::where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->with('instructor')
            ->get();

        return view('courses.search_results', compact('courses', 'query'));
    }



}
