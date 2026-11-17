<x-layouts.auth :title="__('Reset your password')">
    <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
        {{ __("Forgot your password? No problem. Just let us know your email address and we'll send you a password reset link.") }}
    </p>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 text-sm font-medium text-green-600 dark:text-green-400">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
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

        <button
            type="submit"
            class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
        >
            {{ __('Email password reset link') }}
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
        <a href="{{ route('login') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
            {{ __('Back to log in') }}
        </a>
    </p>
</x-layouts.auth>
