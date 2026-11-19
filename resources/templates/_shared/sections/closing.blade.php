<section id="closing" class="inv-section inv-closing" aria-labelledby="closing-title" data-reveal>
    @includeIf('templates::'.$template->slug.'.ornaments.closing')
    <h2 id="closing-title" class="inv-heading">Terima Kasih</h2>
    <hr class="inv-divider">
    @if ($invitation->text('closing'))
        <p class="inv-text">{{ $invitation->text('closing') }}</p>
    @endif
    <p class="inv-signoff">{{ $invitation->title }}</p>
    @if ($invitation->music?->attribution)
        <p class="inv-small">{{ $invitation->music->attribution }}</p>
    @endif
    <p class="inv-small">Dibuat dengan Momenikah</p>
</section>
