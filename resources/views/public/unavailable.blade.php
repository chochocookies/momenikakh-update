@props(['heading', 'message'])
<x-layouts.public :title="$heading">
    <div class="mx-auto grid min-h-screen max-w-md content-center justify-items-center gap-5 px-6 py-16 text-center">
        <img src="{{ asset('brand/momenikah-icon.svg') }}" width="56" height="56" alt="">
        <h1 class="font-serif text-4xl font-semibold text-ink">{{ $heading }}</h1>
        <p class="text-lg text-muted">{{ $message }}</p>
        <p class="text-muted">Kamu juga bisa membuat undangan digital sendiri.</p>
        <x-ui.button :href="request()->getScheme().'://'.config('surfaces.marketing.domain')">Kenali Momenikah</x-ui.button>
    </div>
</x-layouts.public>
