@props(['title' => null])
<!doctype html>
<html lang="id" class="scroll-smooth motion-reduce:scroll-auto">
<head>
    <x-seo.head :title="$title" :noindex="true" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-surface="app" class="min-h-screen bg-ivory font-sans text-ink antialiased">
    <main id="main" tabindex="-1" class="mx-auto grid min-h-screen w-full max-w-md content-center gap-6 px-5 py-10">
        <a href="{{ route('app.home') }}" class="justify-self-center"><img src="{{ asset('brand/momenikah-logo-horizontal.svg') }}" width="180" height="36" alt="Momenikah"></a>
        <div class="rounded-2xl border border-ink/10 bg-white p-6 shadow-sm">
            @if ($title)
                <h1 class="mb-5 font-serif text-3xl font-semibold text-ink">{{ $title }}</h1>
            @endif
            {{ $slot }}
        </div>
    </main>
</body>
</html>
