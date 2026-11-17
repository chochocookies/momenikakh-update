<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Momenikah') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col items-center justify-center">
    <div class="w-full max-w-sm px-4 py-8">
        <!-- Logo -->
        <div class="mb-8 text-center">
            <a href="{{ url('/') }}" class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ config('app.name', 'Momenikah') }}
            </a>
        </div>

        <!-- Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
            @isset($title)
                <h1 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
            @endisset

            {{ $slot }}
        </div>
    </div>

    @livewireScripts
</body>
</html>
