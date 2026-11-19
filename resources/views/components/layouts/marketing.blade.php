@props(['title' => null, 'description' => null])
<!doctype html>
<html lang="id" class="scroll-smooth motion-reduce:scroll-auto">
<head>
    <x-seo.head :title="$title" :description="$description" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-surface="marketing" class="bg-ivory font-sans text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-rose focus:px-5 focus:py-3 focus:text-white">Lewati ke konten</a>
    <header class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4">
        <a href="/" class="inline-flex min-h-11 items-center"><img src="{{ asset('brand/momenikah-logo-horizontal.svg') }}" width="178" height="36" alt="Momenikah, ke beranda"></a>
        <x-ui.button href="/daftar">Buat undangan</x-ui.button>
    </header>
    <main id="main" tabindex="-1">{{ $slot }}</main>
    <footer class="mx-auto max-w-6xl px-5 py-10 text-sm text-muted">© {{ date('Y') }} Momenikah</footer>
    <div id="toasts" role="region" aria-label="Notifikasi" class="fixed inset-x-4 bottom-4 z-50 grid gap-2 sm:left-auto sm:w-96"></div>
</body>
</html>
