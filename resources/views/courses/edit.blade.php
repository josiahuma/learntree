<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold">Edit Course</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 bg-white p-6 rounded shadow border">
            <form method="POST" action="{{ route('courses.update', $course) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Show Validation Errors --}}
                @if ($errors->any())
                    <div class="mb-4 text-red-600">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Title --}}
                <div class="mb-4">
                    <label for="title" class="block font-semibold mb-1">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $course->title) }}"
                           class="w-full border rounded px-3 py-2" required>
                </div>

                {{-- Featured Image --}}
                <div class="mb-4">
                    <label for="featured_image" class="block font-semibold mb-1">Featured Image</label>
                    <input type="file" name="featured_image" id="featured_image"
                           class="w-full border rounded px-3 py-2">
                    @if($course->featured_image)
                        <img src="{{ Storage::url($course->featured_image) }}" class="mt-2 h-32 rounded">
                    @endif
                </div>

                {{-- Difficulty --}}
                <div class="mb-4">
                    <label for="difficulty" class="block font-semibold mb-1">Difficulty</label>
                    <select name="difficulty" id="difficulty" class="w-full border rounded px-3 py-2">
                        <option value="beginner" {{ $course->difficulty === 'beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="intermediate" {{ $course->difficulty === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="advanced" {{ $course->difficulty === 'advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                </div>

                {{-- Duration --}}
                <div class="mb-4">
                    <label for="duration" class="block font-semibold mb-1">
                        Course Duration (e.g. 3h 20m or 4 weeks)
                    </label>
                    <input type="text" name="duration" id="duration"
                           value="{{ old('duration', $course->duration) }}"
                           class="w-full border rounded px-3 py-2">
                </div>

                {{-- Price --}}
                <div class="mb-4">
                    <label for="price" class="block font-semibold mb-1">Price (£)</label>
                    <input type="number" step="0.01" name="price" id="price"
                           value="{{ $course->price }}"
                           class="w-full border rounded px-3 py-2" required>
                </div>

                {{-- Sale Price --}}
                <div class="mb-4">
                    <label for="sale_price" class="block font-semibold mb-1">Sale Price (£)</label>
                    <input type="number" step="0.01" name="sale_price" id="sale_price"
                           value="{{ $course->sale_price }}"
                           class="w-full border rounded px-3 py-2">
                </div>

                {{-- Description --}}
                <div class="mb-4">
                    <label for="description" class="block font-semibold mb-1">Description</label>
                    <textarea name="description" id="description" class="form-control w-full">
                        {{ old('description', $course->description) }}
                    </textarea>
                </div>

                {{-- SimpleMDE Markdown Editor --}}
                <script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        new SimpleMDE({ element: document.getElementById("description") });
                    });
                </script>

                {{-- Submit --}}
                <div class="mt-6">
                    <button type="submit"
                            class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                        ✅ Update Course
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
