<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">
            Edit Homepage Settings
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 bg-white p-6 rounded shadow border space-y-6">

            {{-- Success message --}}
            @if(session('success'))
                <div class="p-3 rounded bg-green-100 text-green-700 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="p-3 rounded bg-red-100 text-red-700 text-sm">
                    <p class="font-semibold mb-1">Please fix the following issues:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.home-settings.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium mb-1">Hero Heading</label>
                    <input type="text"
                           name="hero_heading"
                           value="{{ old('hero_heading', $settings->hero_heading) }}"
                           class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Hero Subheading</label>
                    <input type="text"
                           name="hero_subheading"
                           value="{{ old('hero_subheading', $settings->hero_subheading) }}"
                           class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Button Text</label>
                    <input type="text"
                           name="hero_button_text"
                           value="{{ old('hero_button_text', $settings->hero_button_text) }}"
                           class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Button Link</label>
                    <input type="text"
                           name="hero_button_link"
                           value="{{ old('hero_button_link', $settings->hero_button_link) }}"
                           class="w-full border rounded p-2"
                           placeholder="/courses or https://example.com">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Hero Background Color</label>
                    <input type="color"
                           name="hero_background_color"
                           value="{{ old('hero_background_color', $settings->hero_background_color ?? '#4f46e5') }}">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Featured Courses</label>
                    <select name="featured_course_ids[]" multiple class="w-full border rounded p-2">
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}"
                                @selected(in_array($course->id, $settings->featured_course_ids ?? []))>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        Hold <kbd>Ctrl</kbd> (Windows) or <kbd>Cmd</kbd> (Mac) to select multiple courses.
                    </p>
                </div>

                <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Save Settings
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
