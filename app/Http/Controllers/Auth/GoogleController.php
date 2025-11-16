<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    // Step 1: redirect to Google
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // Step 2: handle callback from Google
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            // If anything goes wrong, send back to login
            return redirect()->route('login')
                ->with('status', 'Google sign-in failed, please try again.');
        }

        // Try find existing user by google_id
        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            // Or fallback by email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Attach google_id to existing account
                $user->update([
                    'google_id' => $googleUser->getId(),
                ]);
            } else {
                // Create a brand new user – default role: student
                $user = User::create([
                    'name'      => $googleUser->getName() ?: $googleUser->getNickname(),
                    'email'     => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    // random password so the column is not null; they can use "forgot password" to set one later if you want
                    'password'  => bcrypt(Str::random(32)),
                    'role'      => 'student',
                ]);
            }
        }

        Auth::login($user, remember: true);

        // After login: send them to appropriate dashboard
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'instructor') {
            return redirect()->route('instructor.dashboard');
        }

        // Default: student dashboard
        return redirect()->route('student.dashboard');
    }
}
