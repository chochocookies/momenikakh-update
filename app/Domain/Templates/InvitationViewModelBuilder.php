<?php

declare(strict_types=1);

namespace App\Domain\Templates;

use App\Domain\Guest\Services\GuestLinkBuilder;
use App\Domain\Invitation\Data\GiftData;
use App\Domain\Invitation\Data\InvitationData;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Media\Data\MediaData;
use App\Domain\Media\Services\MusicResolver;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * The only place that turns an invitation into what a template may read. Eloquent stops here.
 * `fromModel()` is for saved invitations, `fromData()` for previews built from sample data (no database).
 */
final class InvitationViewModelBuilder
{
    public function __construct(
        private readonly EventTypeRegistry $eventTypes,
        private readonly TemplateRegistry $templates,
        private readonly GuestLinkBuilder $links,
        private readonly MusicResolver $music,
        private readonly string $defaultTemplate,
    ) {}

    public function fromModel(Invitation $invitation, bool $preview = false, ?string $template = null): InvitationViewModel
    {
        // Explicit eager loading keeps this safe with Model::preventLazyLoading().
        $invitation->loadMissing(['media', 'people.media', 'gifts.media']);

        $photos = [];
        foreach ($invitation->people as $person) {
            $photo = $person->getFirstMedia('photo');
            if ($photo instanceof Media) {
                $photos[$person->id] = MediaData::fromMedia($photo);
            }
        }
        $qr = [];
        foreach ($invitation->gifts as $gift) {
            $image = $gift->getFirstMedia('qr');
            if ($image instanceof Media) {
                $qr[$gift->id] = MediaData::fromMedia($image);
            }
        }
        $cover = $invitation->getFirstMedia('cover');

        $media = new InvitationMedia(
            $cover instanceof Media ? MediaData::fromMedia($cover) : null,
            $invitation->getMedia('gallery')->map(fn (Media $m) => MediaData::fromMedia($m))->values()->all(),
            $photos,
            $qr,
            $this->music->for($invitation),
        );

        return $this->fromData(InvitationData::fromModel($invitation, $this->eventTypes), $media, $preview, $template);
    }

    /** @param  string|null  $template  overrides the template stored on the invitation (previews, template switching) */
    public function fromData(InvitationData $data, InvitationMedia $media, bool $preview = false, ?string $template = null): InvitationViewModel
    {
        $definition = $this->eventTypes->for($data->eventType);
        $requested = $template ?? $data->templateSlug;
        $manifest = ($requested !== null ? $this->templates->find($requested) : null) ?? $this->templates->get($this->defaultTemplate);

        $rsvp = (bool) ($data->settings['rsvp_enabled'] ?? true);
        $wishes = (bool) ($data->settings['wishes_enabled'] ?? true);

        // Starter copy first, then whatever the owner wrote (empty strings do not hide the starter copy).
        $content = array_merge($definition->defaults, array_filter($data->content, fn (string $v) => trim($v) !== ''));

        $canonical = $this->links->plain($data->slug);

        return new InvitationViewModel(
            slug: $data->slug,
            title: $data->title,
            eventType: $data->eventType,
            eventLabel: $definition->label,
            locale: $data->locale,
            timezone: $data->timezone,
            templateSlug: $manifest->slug,
            templateVersion: $manifest->version,
            // The owner picks a palette by key; an unknown or missing key falls back to the template's first palette.
            palette: in_array($data->settings['palette'] ?? null, $manifest->paletteKeys(), true) ? (string) $data->settings['palette'] : $manifest->defaultPalette(),
            motion: ($data->settings['motion'] ?? 'full') === 'reduced' ? 'none' : 'full',
            people: $data->people,
            schedule: $data->events,
            story: $data->story,
            gifts: array_values(array_filter($data->gifts, fn (GiftData $g) => $g->isActive)),
            cover: $media->cover,
            gallery: $media->gallery,
            photos: $media->photos,
            qr: $media->qr,
            music: $media->music,
            content: $content,
            sections: $this->sections($data, $manifest, $media, $rsvp, $wishes),
            settings: $data->settings,
            urls: ['canonical' => $canonical, 'opened' => $canonical.'/opened', 'rsvp' => $canonical.'/rsvp', 'wishes' => $canonical.'/wishes'],
            rsvpEnabled: $rsvp,
            wishesEnabled: $wishes,
            isPreview: $preview,
        );
    }

    /**
     * The template decides the order, the event type decides which sections make sense, and the data decides
     * whether a section has anything to show (no empty gallery, no gift section without an account).
     *
     * @return list<string>
     */
    private function sections(InvitationData $data, TemplateManifest $manifest, InvitationMedia $media, bool $rsvp, bool $wishes): array
    {
        $forEventType = array_map(SectionRegistry::normalize(...), $data->sections);
        $giftsOn = (bool) ($data->settings['gifts_enabled'] ?? true);
        $hasGift = array_filter($data->gifts, fn (GiftData $g) => $g->isActive) !== [];
        $hasMap = array_filter($data->events, fn ($e) => $e->mapsUrl !== null) !== [];

        $show = fn (string $section): bool => match ($section) {
            'cover', 'opening', 'people', 'closing' => true,
            'events', 'countdown' => $data->events !== [],
            'story' => $data->story !== [],
            'gallery' => $media->gallery !== [],
            'gift' => $giftsOn && $hasGift,
            'rsvp' => $rsvp,
            'guestbook' => $wishes,
            'map' => $hasMap,
            'music' => $media->music !== null,
            default => false,
        };

        // map and music are not part of an event type's section list; they appear whenever the data has them.
        return array_values(array_filter(
            $manifest->sections,
            fn (string $section) => (in_array($section, ['map', 'music'], true) || in_array($section, $forEventType, true)) && $show($section),
        ));
    }
}
