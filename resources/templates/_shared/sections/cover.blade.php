@php($event = $invitation->firstEvent())
<section id="cover" class="inv-cover" data-cover aria-labelledby="cover-title">
    @includeIf('templates::'.$template->slug.'.ornaments.cover')
    @if ($invitation->cover)
        <x-invitation.image :image="$invitation->cover" :lazy="false" sizes="100vw" class="inv-cover__photo" />
    @endif
    <div class="inv-cover__body">
        <p class="inv-eyebrow">{{ $invitation->eventLabel }}</p>
        <h1 id="cover-title" class="inv-title">{{ $invitation->title }}</h1>
        @if ($event)
            <p class="inv-cover__date">{{ $invitation->dateLabel($event) }}</p>
        @endif
        <p class="inv-guest">
            <span class="inv-guest__label">Kepada Yth.</span>
            <strong class="inv-guest__name" data-guest data-guest-default="{{ $invitation->guestPlaceholder }}">{{ $invitation->guestPlaceholder }}</strong>
        </p>
        <button type="button" class="inv-button" data-open-invitation>
            <x-ui.icon name="heart" size="sm" />
            Buka Undangan
        </button>
    </div>
</section>
