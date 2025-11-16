{{-- resources/views/admin/courses/index.blade.php --}}
@php use Illuminate\Support\Facades\Storage; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-semibold leading-tight">
                Approve Courses
            </h2>
            {{-- Breadcrumb --}}
            <div class="text-xs text-gray-500">
                <a href="{{ route('admin.dashboard') }}" class="hover:underline">Admin</a>
                <span class="mx-1">/</span>
                <span>Approve Courses</span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4">
            @if (session('success'))
                <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded shadow border overflow-x-auto">
                <table class="w-full border-collapse table-auto text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="px-4 py-2 text-left font-semibold">Course</th>
                            <th class="px-4 py-2 text-left font-semibold">Instructor</th>
                            <th class="px-4 py-2 text-left font-semibold">Created</th>
                            <th class="px-4 py-2 text-left font-semibold">Status</th>
                            <th class="px-4 py-2 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                            <tr class="border-b last:border-b-0">
                                <td class="px-4 py-3 align-top">
                                    <div class="flex items-center gap-3">
                                        {{-- FIXED THUMBNAIL SIZE --}}
                                        <div
                                            class="overflow-hidden rounded bg-gray-100 flex-shrink-0"
                                            style="width: 220px; height: 124px;"
                                        >
                                            <img
                                                src="{{ $course->featured_image ? Storage::url($course->featured_image) : asset('images/default-course.jpg') }}"
                                                alt="Course thumbnail"
                                                class="w-full h-full object-cover"
                                            >
                                        </div>

                                        <div class="min-w-0">
                                            <a href="{{ route('courses.show', $course) }}"
                                               class="font-semibold text-indigo-600 hover:underline block truncate">
                                                {{ $course->title }}
                                            </a>

                                            @if($course->price > 0)
                                                <div class="text-xs text-gray-500">
                                                    £{{ number_format($course->sale_price ?? $course->price, 2) }}
                                                </div>
                                            @else
                                                <div class="text-xs text-green-600 font-semibold">
                                                    FREE
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3 align-top">
                                    <div class="text-sm text-gray-800">
                                        {{ $course->instructor->name ?? '—' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $course->instructor->email ?? '' }}
                                    </div>
                                </td>

                                <td class="px-4 py-3 align-top text-sm text-gray-600 whitespace-nowrap">
                                    {{ $course->created_at->format('Y-m-d') }}
                                </td>

                                <td class="px-4 py-3 align-top">
                                    @if($course->is_approved)
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                            Approved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 align-top">
                                    <form method="POST" action="{{ route('admin.courses.update', $course) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="is_approved" value="{{ $course->is_approved ? 0 : 1 }}">

                                        @if($course->is_approved)
                                            <button
                                                type="submit"
                                                class="text-xs rounded bg-gray-200 px-3 py-1 font-semibold text-gray-800 hover:bg-gray-300">
                                                ⏸ Unapprove
                                            </button>
                                        @else
                                            <button
                                                type="submit"
                                                class="text-xs rounded bg-indigo-600 px-3 py-1 font-semibold text-white hover:bg-indigo-700">
                                                ✅ Approve
                                            </button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                                    No courses found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $courses->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
