<section id="opening" class="inv-section" aria-labelledby="opening-title" data-reveal>
    @if ($invitation->text('salam'))
        <p class="inv-salam">{{ $invitation->text('salam') }}</p>
    @endif
    <h2 id="opening-title" class="inv-heading">Undangan {{ $invitation->eventLabel }}</h2>
    <hr class="inv-divider">
    @if ($invitation->text('opening'))
        <p class="inv-text">{{ $invitation->text('opening') }}</p>
    @endif
    @if ($invitation->text('theme'))
        <p class="inv-text">Tema: <strong>{{ $invitation->text('theme') }}</strong></p>
    @endif
    @if ($invitation->text('birth_note'))
        <p class="inv-text">{{ $invitation->text('birth_note') }}</p>
    @endif
    @if ($invitation->text('adat_note'))
        <p class="inv-text">{{ $invitation->text('adat_note') }}</p>
    @endif
    @if ($invitation->text('quote'))
        <blockquote class="inv-quote"><p>{{ $invitation->text('quote') }}</p></blockquote>
    @endif
    @if ($invitation->text('prayer'))
        <blockquote class="inv-quote"><p>{{ $invitation->text('prayer') }}</p></blockquote>
    @endif
</section>
