<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold">Edit Lesson</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 bg-white p-6 rounded shadow border">
            <form method="POST" action="{{ route('lessons.update', $lesson) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Lesson Title</label>
                    <input
                        type="text"
                        name="title"
                        value="{{ $lesson->title }}"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Lesson Content</label>
                    <textarea
                        name="content"
                        id="content"
                        rows="4"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    ></textarea>
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        new SimpleMDE({ element: document.getElementById("content") });
                    });
                </script>

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Video URL (YouTube or Vimeo)</label>
                    <input
                        type="url"
                        name="video_url"
                        value="{{ $lesson->video_url }}"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <button
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded"
                >
                    💾 Update Lesson
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
