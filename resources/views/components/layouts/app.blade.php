@props(['title' => null, 'description' => null])
<!doctype html>
<html lang="id" class="scroll-smooth motion-reduce:scroll-auto">
<head>
    <x-seo.head :title="$title" :description="$description" :noindex="true" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-surface="app" class="min-h-screen bg-ivory font-sans text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-rose focus:px-5 focus:py-3 focus:text-white">Lewati ke konten</a>
    <header class="border-b border-ink/10 bg-white/80 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-3">
            <a href="/dashboard"><img src="{{ asset('brand/momenikah-logo-horizontal.svg') }}" width="150" height="30" alt="Momenikah Dashboard"></a>
            <nav aria-label="Navigasi aplikasi" class="flex items-center gap-4 text-sm">
                <a href="/dashboard" class="hover:text-rose">Dashboard</a>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-muted hover:text-ink">Keluar</button>
                    </form>
                @endauth
            </nav>
        </div>
    </header>
    <main id="main" tabindex="-1" class="mx-auto max-w-6xl px-5 py-8">{{ $slot }}</main>
    <div id="toasts" role="region" aria-label="Notifikasi" class="fixed inset-x-4 bottom-4 z-50 grid gap-2 sm:left-auto sm:w-96"></div>
</body>
</html>
