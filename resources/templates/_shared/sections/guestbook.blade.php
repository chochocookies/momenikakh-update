<section id="guestbook" class="inv-section" aria-labelledby="guestbook-title" data-reveal>
    <h2 id="guestbook-title" class="inv-heading">Ucapan dan Doa</h2>
    <hr class="inv-divider">
    <form class="inv-form" method="post" action="{{ $invitation->urls['wishes'] }}" data-wishes-form>
        <fieldset class="inv-form__fields" @disabled($invitation->isPreview)>
            <legend class="sr-only">Formulir ucapan dan doa</legend>
            <div class="inv-field">
                <label for="wish-name">Nama</label>
                <input id="wish-name" name="name" type="text" required maxlength="100" autocomplete="name" data-guest-input>
            </div>
            <div class="inv-field">
                <label for="wish-message">Ucapan</label>
                <textarea id="wish-message" name="message" rows="4" required maxlength="300"></textarea>
            </div>
            <div class="inv-hp" aria-hidden="true">
                <label>Jangan diisi<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>
            <button type="submit" class="inv-button">Kirim Ucapan</button>
        </fieldset>
        @if ($invitation->isPreview)
            <p class="inv-note">Ini pratinjau. Formulir aktif setelah undangan terbit.</p>
        @endif
    </form>
    <ol class="inv-wishes" data-wishes data-wishes-url="{{ $invitation->urls['wishes'] }}" aria-label="Daftar ucapan" aria-live="polite"></ol>
    <p class="inv-note" data-wishes-empty>Belum ada ucapan. Jadilah yang pertama.</p>
</section>
