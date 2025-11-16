@php use Illuminate\Support\Facades\Storage; @endphp

<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4">

            {{-- Hero Section --}}
            @php
                $bgColor = $settings->hero_background_color ?? '#4f46e5';
            @endphp

            <section class="text-white py-20 rounded-lg mb-12"
                     style="background-color: {{ $bgColor }};">
                <div class="text-center px-4">
                    <h1 class="text-4xl font-bold mb-4">
                        {{ $settings->hero_heading ?? 'Unlock Your Potential' }}
                    </h1>

                    <p class="mb-6 text-lg">
                        {{ $settings->hero_subheading ?? 'Learn at your own pace with practical, hands-on courses.' }}
                    </p>

                    @php
                        $buttonLink = $settings->hero_button_link ?: route('courses.index');
                        $buttonText = $settings->hero_button_text ?: 'Browse Courses';
                    @endphp

                    <a href="{{ $buttonLink }}"
                       class="bg-white text-indigo-600 px-6 py-3 rounded shadow hover:bg-gray-100">
                        {{ $buttonText }}
                    </a>
                </div>
            </section>

            {{-- Featured Courses --}}
            <section class="py-12">
                <div class="max-w-7xl mx-auto px-4">

                    {{-- Admin warning for unapproved featured courses --}}
                    @if(auth()->user()?->role === 'admin')
                        @php
                            $unapprovedFeatured = \App\Models\Course::whereIn('id', $settings->featured_course_ids ?? [])
                                            ->where('is_approved', 0)
                                            ->count();
                        @endphp

                        @if($unapprovedFeatured > 0)
                            <div class="p-3 mb-4 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded">
                                ⚠️ {{ $unapprovedFeatured }} of your featured courses have not been approved and are hidden from the homepage.
                            </div>
                        @endif
                    @endif

                    <h2 class="text-2xl font-bold mb-6">Featured Courses</h2>

                    @if($courses->isEmpty())
                        <p class="text-gray-600">
                            No featured courses selected yet. Go to
                            <a href="{{ route('admin.home-settings.edit') }}" class="text-indigo-600 underline">
                                Home Settings
                            </a>
                            to choose some.
                        </p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            @foreach ($courses as $course)
                                <div class="bg-white border rounded shadow hover:shadow-lg transition overflow-hidden">
                                    <img src="{{ $course->featured_image ? Storage::url($course->featured_image) : asset('images/default-course.jpg') }}"
                                         alt="Course Thumbnail"
                                         class="w-full h-48 object-cover rounded-t">

                                    <div class="p-4">
                                        <h3 class="text-lg font-semibold">{{ $course->title }}</h3>
                                        <p class="text-sm text-gray-600">By {{ $course->instructor->name }}</p>

                                        @if($course->reviews->count())
                                            <span class="text-yellow-500 text-sm">
                                                ⭐ {{ $course->averageRating() }}/5
                                            </span>
                                        @else
                                            <p class="text-gray-500 text-sm">No reviews yet.</p>
                                        @endif

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

                                        <a href="{{ route('courses.show', $course) }}"
                                           class="mt-4 inline-block text-indigo-600 hover:underline text-sm">
                                            View Course
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

        </div>
    </div>
</x-app-layout>
