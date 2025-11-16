<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">
            Manage Users
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 bg-white p-6 rounded shadow border">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-700 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-3 rounded bg-red-100 text-red-700 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="border-b bg-gray-50">
                        <th class="text-left py-2 px-2">Name</th>
                        <th class="text-left py-2 px-2">Email</th>
                        <th class="text-left py-2 px-2">Role</th>
                        <th class="text-left py-2 px-2">Created</th>
                        <th class="text-left py-2 px-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="border-b">
                            <td class="py-2 px-2">{{ $user->name }}</td>
                            <td class="py-2 px-2">{{ $user->email }}</td>
                            <td class="py-2 px-2 capitalize">{{ $user->role }}</td>
                            <td class="py-2 px-2 text-xs text-gray-500">
                                {{ $user->created_at?->format('Y-m-d') }}
                            </td>
                            <td class="py-2 px-2">
                                <form action="{{ route('admin.users.update-role', $user) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')

                                    <select name="role" class="border rounded px-2 py-1 text-xs">
                                        <option value="student"    @selected($user->role === 'student')>Student</option>
                                        <option value="instructor" @selected($user->role === 'instructor')>Instructor</option>
                                        <option value="admin"      @selected($user->role === 'admin')>Admin</option>
                                    </select>

                                    <button type="submit"
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1 rounded">
                                        Save
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-gray-500">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
