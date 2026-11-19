<section id="gift" class="inv-section" aria-labelledby="gift-title" data-reveal>
    <h2 id="gift-title" class="inv-heading">Tanda Kasih</h2>
    <hr class="inv-divider">
    <p class="inv-text">Kehadiran dan doa Anda sudah berarti bagi kami. Bila berkenan memberi tanda kasih, Anda dapat memakai rekening atau alamat berikut.</p>
    <div class="inv-gifts">
        @foreach ($invitation->gifts as $gift)
            <article class="inv-card inv-gift">
                @if ($gift->type->value === 'address')
                    <h3 class="inv-gift__title">Alamat Pengiriman</h3>
                    <p class="inv-text">{{ $gift->addressText }}</p>
                    <button type="button" class="inv-button inv-button--ghost" data-copy="{{ $gift->addressText }}" data-copy-done="Alamat disalin.">
                        <x-ui.icon name="copy" size="sm" /> Salin alamat
                    </button>
                @else
                    <h3 class="inv-gift__title">{{ $gift->provider }}</h3>
                    <p class="inv-text">a.n. {{ $gift->holderName }}</p>
                    <p class="inv-gift__number">{{ $gift->number }}</p>
                    @if ($invitation->qrFor($gift))
                        <x-invitation.image :image="$invitation->qrFor($gift)" sizes="160px" class="inv-qr" />
                    @endif
                    <button type="button" class="inv-button inv-button--ghost" data-copy="{{ $gift->number }}" data-copy-done="Nomor {{ $gift->provider }} disalin.">
                        <x-ui.icon name="copy" size="sm" /> Salin nomor
                    </button>
                @endif
            </article>
        @endforeach
    </div>
</section>
