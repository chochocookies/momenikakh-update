<section id="countdown" class="inv-section" aria-labelledby="countdown-title" data-reveal>
    <h2 id="countdown-title" class="inv-heading">Menuju Hari Acara</h2>
    <hr class="inv-divider">
    <div class="inv-countdown" data-countdown="{{ $invitation->countdownTo() }}" role="timer" aria-label="Hitung mundur menuju acara">
        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
            <div class="inv-card inv-countdown__unit">
                <span class="inv-countdown__value" data-unit="{{ $unit }}">0</span>
                <span class="inv-countdown__label">{{ $label }}</span>
            </div>
        @endforeach
    </div>
    <p class="inv-text" data-countdown-done hidden>Acara sedang atau sudah berlangsung.</p>
    @if ($invitation->firstEvent())
        <p class="inv-text">{{ $invitation->dateLabel($invitation->firstEvent()) }}, {{ $invitation->timeLabel($invitation->firstEvent()) }}</p>
    @endif
</section>
