<section id="rsvp" class="inv-section" aria-labelledby="rsvp-title" data-reveal>
    <h2 id="rsvp-title" class="inv-heading">Konfirmasi Kehadiran</h2>
    <hr class="inv-divider">
    <p class="inv-text">Mohon konfirmasi kehadiran Anda agar kami dapat menyiapkan segalanya dengan baik.</p>
    <form class="inv-form" method="post" action="{{ $invitation->urls['rsvp'] }}" data-rsvp-form>
        <fieldset class="inv-form__fields" @disabled($invitation->isPreview)>
            <legend class="sr-only">Formulir konfirmasi kehadiran</legend>
            <div class="inv-field">
                <label for="rsvp-name">Nama</label>
                <input id="rsvp-name" name="name" type="text" required maxlength="100" autocomplete="name" data-guest-input>
            </div>
            <fieldset class="inv-field inv-choices">
                <legend>Kehadiran</legend>
                <label class="inv-choice"><input type="radio" name="attendance" value="yes" required><span>Hadir</span></label>
                <label class="inv-choice"><input type="radio" name="attendance" value="maybe"><span>Masih ragu</span></label>
                <label class="inv-choice"><input type="radio" name="attendance" value="no"><span>Tidak hadir</span></label>
            </fieldset>
            <div class="inv-field">
                <label for="rsvp-pax">Jumlah tamu</label>
                <input id="rsvp-pax" name="pax" type="number" inputmode="numeric" min="1" max="10" value="1">
            </div>
            <div class="inv-field">
                <label for="rsvp-note">Catatan (boleh dikosongkan)</label>
                <textarea id="rsvp-note" name="note" rows="3" maxlength="300"></textarea>
            </div>
            <div class="inv-hp" aria-hidden="true">
                <label>Jangan diisi<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>
        </fieldset>
        @if (config('services.turnstile.site_key'))
            <div class="inv-turnstile" data-cf-turnstile-site-key="{{ config('services.turnstile.site_key') }}" data-rsvp-form></div>
        @endif
        <button type="submit" class="inv-button">Kirim Konfirmasi</button>
        @if ($invitation->isPreview)
            <p class="inv-note">Ini pratinjau. Formulir aktif setelah undangan terbit.</p>
        @endif
    </form>
</section>
