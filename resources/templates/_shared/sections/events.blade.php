<section id="events" class="inv-section" aria-labelledby="events-title" data-reveal>
    <h2 id="events-title" class="inv-heading">Waktu dan Tempat</h2>
    <hr class="inv-divider">
    <div class="inv-events">
        @foreach ($invitation->schedule as $event)
            <article class="inv-card inv-event">
                <h3 class="inv-event__title">{{ $invitation->eventTitle($event) }}</h3>
                <p class="inv-event__date"><x-ui.icon name="calendar" size="sm" /> {{ $invitation->dateLabel($event) }}</p>
                <p class="inv-event__time">{{ $invitation->timeLabel($event) }}</p>
                <p class="inv-event__venue"><strong>{{ $event->venueName }}</strong><br>{{ $event->address }}</p>
                @if ($event->dressCode)
                    <p class="inv-event__note">Busana: {{ $event->dressCode }}</p>
                @endif
                @if ($event->notes)
                    <p class="inv-event__note">{{ $event->notes }}</p>
                @endif
            </article>
        @endforeach
    </div>
    @foreach (['attendance_note', 'dress_code', 'wishlist_note'] as $note)
        @if ($invitation->text($note))
            <p class="inv-text">{{ $invitation->text($note) }}</p>
        @endif
    @endforeach
</section>
