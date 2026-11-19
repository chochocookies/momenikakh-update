<div class="inv-music">
    <audio data-music data-autostart="true" preload="none" loop src="{{ $invitation->music->url }}"></audio>
    <button type="button" class="inv-music__toggle" data-music-toggle aria-pressed="false" aria-label="Putar musik">
        <x-ui.icon name="music" />
    </button>
</div>
