@props(['title' => null, 'description' => null, 'wide' => false])
@php
    $nav = [
        ['label' => 'Undangan saya', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard', 'dashboard.invitations.show', 'dashboard.invitations.edit', 'dashboard.guests.*', 'dashboard.responses.*')],
        ['label' => 'Buat undangan', 'href' => route('dashboard.invitations.create'), 'active' => request()->routeIs('dashboard.invitations.create')],
        ['label' => 'Akun', 'href' => route('dashboard.account'), 'active' => request()->routeIs('dashboard.account*')],
    ];
@endphp
<!doctype html>
<html lang="id" class="scroll-smooth motion-reduce:scroll-auto">
<head>
    <x-seo.head :title="$title" :description="$description" :noindex="true" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-surface="app" class="min-h-screen bg-ivory font-sans text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-rose focus:px-5 focus:py-3 focus:text-white">Lewati ke konten</a>
    <header class="border-b border-ink/10 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-6 px-5">
            <a href="{{ route('dashboard') }}" class="inline-flex min-h-14 items-center"><img src="{{ asset('brand/momenikah-logo-horizontal.svg') }}" width="150" height="30" alt="Momenikah Dashboard"></a>
            <nav aria-label="Navigasi aplikasi" class="flex flex-wrap items-center gap-x-1 text-sm">
                @foreach ($nav as $item)
                    <a href="{{ $item['href'] }}" @if ($item['active']) aria-current="page" @endif
                        class="inline-flex min-h-14 items-center border-b-2 border-transparent px-3 font-medium text-muted hover:text-ink aria-[current=page]:border-rose aria-[current=page]:text-ink">{{ $item['label'] }}</a>
                @endforeach
                @auth
                    <span class="mx-2 hidden h-5 w-px bg-ink/15 sm:block" aria-hidden="true"></span>
                    <span class="hidden px-2 text-muted sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="min-h-14 px-3 font-medium text-muted hover:text-ink">Keluar</button>
                    </form>
                @endauth
            </nav>
        </div>
    </header>
    <main id="main" tabindex="-1" @class(['mx-auto px-5 py-8', 'max-w-6xl' => true, 'max-w-7xl' => $wide])>
        @if (session('alert'))
            <x-ui.alert tone="warning" class="mb-6">{{ session('alert') }}</x-ui.alert>
        @endif
        {{ $slot }}
    </main>
    <div id="toasts" role="region" aria-label="Notifikasi" class="fixed inset-x-4 bottom-4 z-50 grid gap-2 sm:left-auto sm:w-96"></div>
</body>
</html>
