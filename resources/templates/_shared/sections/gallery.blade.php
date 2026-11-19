<section id="gallery" class="inv-section inv-section--wide" aria-labelledby="gallery-title" data-reveal>
    <h2 id="gallery-title" class="inv-heading">Galeri</h2>
    <hr class="inv-divider">
    <ul class="inv-gallery" role="list">
        @foreach ($invitation->gallery as $i => $image)
            <li>
                <button type="button" class="inv-gallery__item" data-lightbox-open="{{ $i }}"
                    data-src="{{ $image->url }}" data-srcset="{{ $image->srcset }}" data-width="{{ $image->width }}" data-height="{{ $image->height }}" data-alt="{{ $image->alt }}"
                    aria-label="Perbesar foto {{ $i + 1 }} dari {{ count($invitation->gallery) }}">
                    <x-invitation.image :image="$image" sizes="(min-width: 640px) 200px, 45vw" />
                </button>
            </li>
        @endforeach
    </ul>

    <dialog class="inv-lightbox" data-lightbox aria-label="Foto galeri">
        <figure class="inv-lightbox__figure">
            <img class="inv-lightbox__image" data-lightbox-image src="{{ $invitation->gallery[0]->url }}" width="{{ $invitation->gallery[0]->width }}" height="{{ $invitation->gallery[0]->height }}" alt="" decoding="async">
            <figcaption class="inv-lightbox__count" data-lightbox-count aria-live="polite"></figcaption>
        </figure>
        <button type="button" class="inv-lightbox__btn inv-lightbox__btn--close" data-lightbox-close aria-label="Tutup foto"><x-ui.icon name="x" /></button>
        <button type="button" class="inv-lightbox__btn inv-lightbox__btn--prev" data-lightbox-prev aria-label="Foto sebelumnya"><x-ui.icon name="arrow-right" /></button>
        <button type="button" class="inv-lightbox__btn inv-lightbox__btn--next" data-lightbox-next aria-label="Foto berikutnya"><x-ui.icon name="arrow-right" /></button>
    </dialog>
</section>
