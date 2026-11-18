@props(['src', 'title' => 'Musik latar'])

{{-- No autoplay attribute on purpose: music starts from the tap that opens the cover (music.js). --}}
<audio data-music src="{{ $src }}" preload="none" loop playsinline></audio>
<button type="button" data-music-toggle aria-pressed="false" aria-label="Putar musik"
    {{ $attributes->class('fixed bottom-[max(1rem,env(safe-area-inset-bottom))] right-4 z-40 grid size-12 place-items-center rounded-full bg-brand-rose text-brand-on shadow-lg transition motion-reduce:transition-none aria-pressed:bg-brand-ink disabled:opacity-50') }}>
    <x-ui.icon name="music" size="lg" />
    <span class="sr-only">{{ $title }}</span>
</button>
