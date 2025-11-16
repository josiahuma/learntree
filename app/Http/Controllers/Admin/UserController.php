<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // You can add ->where('role', '!=', 'admin') if you want to hide admins
        $users = User::orderBy('name')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => 'required|in:student,instructor,admin',
        ]);

        // Optional: prevent accidentally changing your own role
        if ($user->id === auth()->id() && $data['role'] !== 'admin') {
            return back()->with('error', 'You cannot change your own admin role.');
        }

        $user->role = $data['role'];
        $user->save();

        return back()->with('success', 'User role updated.');
    }
}
