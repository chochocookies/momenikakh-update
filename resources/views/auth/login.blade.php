<x-layouts.auth :title="__('Log in')">
    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 text-sm font-medium text-green-600 dark:text-green-400">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('Email address') }}
            </label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
                class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('email') border-red-500 @enderror"
            >
            @error('email')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Password') }}
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>
            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
                class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('password') border-red-500 @enderror"
            >
            @error('password')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center gap-2">
            <input
                id="remember"
                name="remember"
                type="checkbox"
                {{ old('remember') ? 'checked' : '' }}
                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            >
            <label for="remember" class="text-sm text-gray-700 dark:text-gray-300">
                {{ __('Remember me') }}
            </label>
        </div>

        <button
            type="submit"
            class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
            data-test="login-button"
        >
            {{ __('Log in') }}
        </button>
    </form>

    @if (Route::has('register'))
        <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
            {{ __("Don't have an account?") }}
            <a href="{{ route('register') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                {{ __('Sign up') }}
            </a>
        </p>
    @endif
</x-layouts.auth>
