<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- Google button --}}
    <div class="mb-4">
        <a href="{{ route('auth.google.redirect') }}"
        class="w-full inline-flex items-center justify-center gap-2 border border-gray-300 bg-white px-4 py-2 rounded shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
            <svg class="h-4 w-4" viewBox="0 0 533.5 544.3" aria-hidden="true">
                {{-- tiny Google "G" svg, optional, or remove svg entirely --}}
                <path fill="#4285F4" d="M533.5 278.4c0-17.4-1.5-34.1-4.3-50.2H272v95.0h146.9..." />
            </svg>
            <span>Continue with Google</span>
        </a>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-4 flex flex-col space-y-2">
            <div class="flex justify-between items-center">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('register') }}">
                    {{ __('Don\'t have an account? Register Now') }}
                </a>
            </div>

            <x-primary-button class="w-full justify-center">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
