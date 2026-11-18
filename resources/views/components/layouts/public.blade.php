@props(['title' => null, 'description' => null, 'image' => null])
<!doctype html>
<html lang="id" class="scroll-smooth motion-reduce:scroll-auto">
<head>
    <x-seo.head :title="$title" :description="$description" :image="$image" />
    <meta name="robots" content="noindex,nofollow">
    @vite(['resources/css/app.css', 'resources/js/public.js'])
</head>
<body data-surface="public" class="min-h-screen bg-ivory font-sans text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-rose focus:px-5 focus:py-3 focus:text-white">Lewati ke konten</a>
    <main id="main" tabindex="-1">{{ $slot }}</main>
    <div id="toasts" role="region" aria-label="Notifikasi" class="fixed inset-x-4 bottom-4 z-50 grid gap-2 sm:left-auto sm:w-96"></div>
</body>
</html>
