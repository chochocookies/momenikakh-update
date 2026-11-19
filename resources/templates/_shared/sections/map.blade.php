<section id="map" class="inv-section" aria-labelledby="map-title" data-reveal>
    <h2 id="map-title" class="inv-heading">Lokasi</h2>
    <hr class="inv-divider">
    <div class="inv-events">
        @foreach ($invitation->schedule as $event)
            @if ($event->mapsUrl && str_starts_with($event->mapsUrl, 'https://'))
                <article class="inv-card inv-event">
                    <h3 class="inv-event__title">{{ $invitation->eventTitle($event) }}</h3>
                    <p class="inv-event__venue"><strong>{{ $event->venueName }}</strong><br>{{ $event->address }}</p>
                    <a class="inv-button" href="{{ $event->mapsUrl }}" target="_blank" rel="noopener noreferrer">
                        <x-ui.icon name="map-pin" size="sm" /> Buka Peta<span class="sr-only"> {{ $event->venueName }} (tab baru)</span>
                    </a>
                </article>
            @endif
        @endforeach
    </div>
</section>
