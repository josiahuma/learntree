<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 md:flex gap-8">

        <!-- Left Column: Course Details -->
        <div class="md:w-2/3">
            <!-- Banner Image -->
            <div class="h-64 w-full rounded-lg overflow-hidden mb-6">
                <img src="{{ $course->featured_image ? Storage::url($course->featured_image) : asset('images/default-course.jpg') }}"
                    alt="Course Banner"
                    class="w-full h-full object-cover">
            </div>

            <!-- Title, Instructor and Tags -->
            <div class="mb-4">
                <h1 class="text-3xl font-bold">{{ $course->title }}</h1>
                @if($course->reviews->count())
                    <span class="text-yellow-500 text-sm">⭐ {{ $course->averageRating() }}/5</span>
                @else
                    <p class="text-gray-500">No reviews yet.</p>
                @endif
                <p class="text-gray-600">By <span class="font-medium">{{ $course->instructor->name }}</span></p>

                <!-- Difficulty Badge -->
                <span class="inline-block mt-2 px-3 py-1 text-sm rounded-full text-white
                    @if($course->difficulty === 'beginner') bg-green-500
                    @elseif($course->difficulty === 'intermediate') bg-yellow-500
                    @else bg-red-500 @endif">
                    {{ ucfirst($course->difficulty) }}
                </span>
            </div>
            @if($course->duration)
                <p class="text-gray-700 mb-2"><strong>Duration:</strong> {{ $course->duration }}</p>
            @endif

            <p class="text-sm text-gray-500">📅 Last Updated: {{ $course->updated_at->format('M d, Y') }}</p>

            <!-- Description -->
            <p class="text-gray-800 leading-relaxed mb-6">{{ $course->description }}</p>

            <!-- Lessons -->
            <h2 class="text-xl font-semibold mb-2">Course Content</h2>

            @php
                $user = auth()->user();
                $isStudent = $user && $user->role === 'student';
                $completedLessonIds = $isStudent
                    ? $user->completedLessons->pluck('id')->toArray()
                    : [];
            @endphp

            <div class="bg-white shadow rounded divide-y">
                @forelse ($course->lessons as $lesson)
                    @php
                        // Base flags
                        $isInstructor = $user && $user->role === 'instructor';

                        // Default: not locked
                        $isLocked = false;
                        $lockReason = '';

                        if ($isStudent) {
                            // If student is not enrolled, lock all lessons (existing behaviour)
                            if (!$isEnrolled) {
                                $isLocked = true;
                                $lockReason = 'Enroll to access this lesson';
                            } else {
                                // Student is enrolled: enforce prerequisite chain
                                if (!$loop->first) {
                                    $previousLesson = $course->lessons[$loop->index - 1] ?? null;
                                    $previousCompleted = $previousLesson
                                        ? in_array($previousLesson->id, $completedLessonIds)
                                        : false;

                                    if (!$previousCompleted) {
                                        $isLocked = true;
                                        $lockReason = 'Complete the previous lesson to unlock this one';
                                    }
                                }
                            }
                        }
                    @endphp

                    <div class="p-4 flex justify-between items-center
                        {{ $isLocked ? 'opacity-60 cursor-not-allowed' : '' }}">

                        {{-- Left: lesson title + lock info --}}
                        <div class="flex flex-col">
                            @if($isLocked)
                                <span class="text-gray-500">{{ $lesson->title }}</span>
                                @if($lockReason)
                                    <span class="text-xs text-gray-400 mt-1">🔒 {{ $lockReason }}</span>
                                @endif
                            @else
                                <a href="{{ route('lessons.show', $lesson) }}"
                                   class="text-indigo-600 font-medium hover:underline">
                                    {{ $lesson->title }}
                                </a>
                            @endif
                        </div>

                        {{-- Right: instructor actions (never locked for instructors) --}}
                        @if($isInstructor)
                            <div class="flex gap-3">
                                <a href="{{ route('lessons.edit', $lesson) }}"
                                   class="text-sm text-blue-600 hover:underline">
                                    ✏️ Edit
                                </a>
                                <form action="{{ route('lessons.destroy', $lesson) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete this lesson?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-sm text-red-600 hover:underline">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-4 text-gray-500">No lessons available yet.</div>
                @endforelse
            </div>



            <!-- Show alert only if logged-in student is not enrolled -->
            @if(auth()->check() && auth()->user()->role === 'student' && !$isEnrolled)
                <div class="mt-4 p-4 bg-yellow-100 border border-yellow-300 rounded text-yellow-800">
                    ⚠️ You must enroll to access lesson content.
                </div>
            @endif

            <!-- Reviews Section -->
            @if(auth()->check() && auth()->user()->role === 'student' && $isEnrolled)
                <div class="mt-8">
                    <h3 class="text-xl font-semibold mb-2">Leave a Review</h3>
                    <form action="{{ route('courses.reviews.store', $course) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label>Rating (1-5)</label>
                            <select name="rating" class="border rounded px-2 py-1 w-32" required>
                                <option value="">Select rating</option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="mb-2">
                            <label>Your Review</label>
                            <textarea name="review" rows="3" class="w-full border rounded px-2 py-1"></textarea>
                        </div>
                        <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
                    </form>
                </div>
            @endif
            <!-- Display Reviews -->
             <div class="mt-6">
                <h3 class="text-xl font-semibold mb-3">Student Reviews</h3>
                @forelse($course->reviews as $review)
                    <div class="mb-4 border-b pb-2">
                        <p class="font-semibold">{{ $review->user->name }}</p>
                        <p class="text-yellow-500">Rating: {{ str_repeat('⭐', $review->rating) }}</p>
                        <p class="text-gray-700">{{ $review->review }}</p>

                         @if(auth()->id() === $review->user_id)
                            <div class="flex gap-2 mt-1">
                                <a href="{{ route('courses.reviews.edit', [$course, $review]) }}" class="text-blue-500 text-sm">Edit</a>
                                <form action="{{ route('courses.reviews.destroy', [$course, $review]) }}" method="POST" onsubmit="return confirm('Delete review?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500 text-sm">Delete</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500">No reviews yet.</p>
                @endforelse
            </div>


            <!-- Share Buttons -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold mb-2">📤 Share this course</h3>
                <div class="flex gap-3">
                    @php $url = urlencode(request()->fullUrl()); @endphp
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $url }}" target="_blank"
                    class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Facebook</a>
                    <a href="https://twitter.com/intent/tweet?url={{ $url }}" target="_blank"
                    class="bg-blue-400 text-white px-3 py-1 rounded hover:bg-blue-500">Twitter</a>
                    <a href="https://wa.me/?text={{ $url }}" target="_blank"
                    class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">WhatsApp</a>
                </div>
            </div>


            <!-- Add Lesson Button for Instructors -->
            @if(auth()->check() && auth()->user()->role === 'instructor')
                <div class="mt-6">
                    <a href="{{ route('lessons.create', $course) }}" class="inline-block bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700">
                        ➕ Add New Lesson
                    </a>
                </div>
            @endif
        </div>

        <!-- Right Column: Sidebar -->
        <div class="md:w-1/3 mt-8 md:mt-0">
            <div class="bg-white shadow rounded p-6 sticky top-20">
                <h3 class="text-xl font-semibold mb-4">Course Info</h3>
                @if ($course->sale_price && $course->sale_price > 0)
                    <p class="text-gray-500 line-through text-sm">
                        £{{ number_format($course->price, 2) }}
                    </p>
                    <p class="text-indigo-600 font-bold text-lg">
                        £{{ number_format($course->sale_price, 2) }}
                    </p>
                @elseif ($course->price > 0)
                    <p class="text-indigo-600 font-bold text-lg">
                        £{{ number_format($course->price, 2) }}
                    </p>
                @else
                    <p class="text-green-600 font-bold text-lg">FREE</p>
                @endif


                <p class="text-gray-700 mb-6"><strong>Instructor:</strong> {{ $course->instructor->name }}</p>

                @guest
                    <a href="{{ route('login') }}" class="block w-full text-center bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Enroll in this Course
                    </a>
                @endguest

                @auth
                    @if(auth()->user()->role === 'student')
                        @if (auth()->user()->enrolledCourses->contains($course->id))
                            <p class="text-green-600 font-medium">✅ You're already enrolled.</p>
                        @else
                            <form action="{{ route('courses.enroll', $course) }}" method="POST" class="mt-2">
                                @csrf
                                <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                                    Enroll in this Course
                                </button>
                            </form>
                        @endif
                    @endif
                @endauth
            </div>
        </div>

    </div>
</x-app-layout>
