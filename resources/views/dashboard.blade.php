<x-layouts.app :title="__('Dashboard')">
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Dashboard') }}</h1>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    {{ __('Welcome back,') }} {{ auth()->user()->name }}!
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __("You're logged in to the Momenikah application surface.") }}
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
