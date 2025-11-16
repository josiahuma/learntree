<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-semibold leading-tight">
                Admin Dashboard
            </h2>
            <div class="text-xs text-gray-500">
                <span>Admin</span> <span class="mx-1">/</span> <span>Dashboard</span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 space-y-6">

            {{-- Welcome card --}}
            <div class="bg-white border rounded shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-1">
                    Welcome, {{ auth()->user()->name }}
                </h3>
                <p class="text-sm text-gray-600">
                    You’re logged in as <span class="font-semibold">{{ auth()->user()->role }}</span>.
                    Use the links in the top navigation bar to manage the homepage, users and courses.
                </p>
            </div>

            {{-- Quick info cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white border rounded shadow-sm p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase">Home Settings</p>
                    <p class="mt-2 text-sm text-gray-700">
                        Update the hero section, button text and featured courses from
                        <a href="{{ route('admin.home-settings.edit') }}" class="text-indigo-600 underline">
                            Home Settings
                        </a>.
                    </p>
                </div>

                <div class="bg-white border rounded shadow-sm p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase">Users</p>
                    <p class="mt-2 text-sm text-gray-700">
                        Promote or demote users to <span class="font-semibold">Instructor</span> or
                        <span class="font-semibold">Student</span> under
                        <a href="{{ route('admin.users.index') }}" class="text-indigo-600 underline">
                            Manage Users
                        </a>.
                    </p>
                </div>

                <div class="bg-white border rounded shadow-sm p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase">Courses</p>
                    <p class="mt-2 text-sm text-gray-700">
                        Review and manage courses from
                        <a href="{{ route('courses.index') }}" class="text-indigo-600 underline">
                            All Courses
                        </a>.
                        You can later add a dedicated “Approve Courses” screen here.
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
