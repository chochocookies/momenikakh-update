<?php

declare(strict_types=1);

namespace App\Domain\Templates;

use App\Domain\Invitation\Data\EventData;
use App\Domain\Invitation\Data\GiftData;
use App\Domain\Invitation\Data\InvitationData;
use App\Domain\Invitation\Data\PersonData;
use App\Domain\Invitation\Data\StoryItemData;
use App\Domain\Invitation\Enums\EventKind;
use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\Enums\GiftType;
use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Enums\PersonRole;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Invitation\Support\SamplePayload;
use App\Domain\Media\Data\MediaData;
use App\Domain\Media\Data\MusicData;
use Carbon\CarbonImmutable;

/**
 * Fictional but complete view-models for every event type, built without a database.
 * Every section has something to show, so previews, the catalogue and the template audit can render any
 * template for any event type it supports. Never put real people or real accounts here.
 */
final class TemplateSampleData
{
    public const SLUG_PREFIX = 'contoh-';

    public function __construct(
        private readonly InvitationViewModelBuilder $builder,
        private readonly EventTypeRegistry $eventTypes,
    ) {}

    /** @param  string|null  $template  render with this template instead of the sample's default */
    public function viewModel(EventType $type, ?string $template = null, ?string $palette = null, ?string $motion = null): InvitationViewModel
    {
        $data = $this->data($type, $template);
        if ($palette !== null || $motion !== null) {
            $data = $this->withSettings($data, array_filter(['palette' => $palette, 'motion' => $motion]));
        }

        return $this->builder->fromData($data, $this->media($type), preview: true, template: $template);
    }

    public function data(EventType $type, ?string $template = null): InvitationData
    {
        $payload = SamplePayload::for($type);
        $definition = $this->eventTypes->for($type);
        $timezone = (string) $payload['basics']['timezone'];

        $people = [];
        foreach ($payload['people']['people'] as $i => $p) {
            $people[] = new PersonData(
                $i + 1,
                PersonRole::from($p['role']),
                $p['name'],
                $p['nickname'] ?? null,
                $p['parents_text'] ?? null,
                $p['bio'] ?? null,
                $p['birth_date'] ?? null,
                $p['instagram'] ?? null,
            );
        }

        $events = $this->events($payload['events']['events'], $timezone);

        $story = [];
        foreach ($payload['story']['story'] as $i => $s) {
            $story[] = new StoryItemData($i + 1, $s['title'], $s['body'], $s['happened_on'] ?? null);
        }

        $gifts = [];
        foreach ($payload['gifts']['gifts'] as $i => $g) {
            $gifts[] = new GiftData(
                $i + 1,
                GiftType::from($g['type']),
                $g['provider'] ?? null,
                $g['holder_name'] ?? null,
                $g['number'] ?? null,
                $g['address_text'] ?? null,
                (bool) ($g['is_active'] ?? true),
            );
        }

        return new InvitationData(
            0,
            self::SLUG_PREFIX.str_replace('_', '-', $type->value),
            (string) $payload['basics']['title'],
            $type,
            $template ?? (string) $payload['basics']['template_slug'],
            (string) $payload['basics']['locale'],
            $timezone,
            InvitationStatus::Published,
            $people,
            $events,
            $story,
            $gifts,
            $payload['content']['content'],
            $payload['settings']['settings'],
            $definition->sections,
        );
    }

    /** Placeholder pictures (public/template-sample/*.svg) for every slot a template can fill. */
    public function media(EventType $type): InvitationMedia
    {
        $payload = SamplePayload::for($type);
        $image = fn (string $file, int $w, int $h, string $alt): MediaData => new MediaData("/template-sample/{$file}", '', null, $w, $h, $alt);

        $photos = [];
        foreach ($payload['people']['people'] as $i => $person) {
            $photos[$i + 1] = $image('portrait.svg', 600, 800, 'Foto '.$person['nickname']);
        }
        $qr = [];
        foreach ($payload['gifts']['gifts'] as $i => $gift) {
            $qr[$i + 1] = $image('qr.svg', 400, 400, 'Kode QR contoh');
        }

        return new InvitationMedia(
            $image('cover.svg', 800, 1200, 'Foto sampul contoh'),
            [
                $image('gallery-a.svg', 1200, 800, 'Foto galeri contoh 1'),
                $image('gallery-b.svg', 1200, 800, 'Foto galeri contoh 2'),
                $image('gallery-c.svg', 1200, 800, 'Foto galeri contoh 3'),
                $image('gallery-a.svg', 1200, 800, 'Foto galeri contoh 4'),
                $image('gallery-b.svg', 1200, 800, 'Foto galeri contoh 5'),
                $image('gallery-c.svg', 1200, 800, 'Foto galeri contoh 6'),
            ],
            $photos,
            $qr,
            new MusicData('/template-sample/silence.mp3', 'Musik contoh'),
        );
    }

    /**
     * The payload dates are fixed (2027); previews should always lie in the future, so the first event is moved to
     * 45 days from now and the others keep their distance from it.
     *
     * @param  list<array<string,mixed>>  $rows
     * @return list<EventData>
     */
    private function events(array $rows, string $timezone): array
    {
        $first = CarbonImmutable::createFromFormat('Y-m-d H:i', $rows[0]['starts_at'], $timezone);
        $shiftDays = (int) $first->startOfDay()->diffInDays(CarbonImmutable::now($timezone)->addDays(45)->startOfDay(), false);

        $shift = fn (string $value): CarbonImmutable => CarbonImmutable::createFromFormat('Y-m-d H:i', $value, $timezone)->addDays($shiftDays);

        $events = [];
        foreach ($rows as $i => $e) {
            $events[] = new EventData(
                $i + 1,
                EventKind::from($e['kind']),
                $e['label'] ?? null,
                $shift($e['starts_at']),
                isset($e['ends_at']) ? $shift($e['ends_at']) : null,
                $e['venue_name'],
                $e['address'],
                $e['maps_url'] ?? null,
                isset($e['latitude']) ? (float) $e['latitude'] : null,
                isset($e['longitude']) ? (float) $e['longitude'] : null,
                $e['dress_code'] ?? null,
                $e['notes'] ?? null,
            );
        }

        return $events;
    }

    /** @param  array<string,mixed>  $settings */
    private function withSettings(InvitationData $data, array $settings): InvitationData
    {
        return new InvitationData(
            $data->id,
            $data->slug,
            $data->title,
            $data->eventType,
            $data->templateSlug,
            $data->locale,
            $data->timezone,
            $data->status,
            $data->people,
            $data->events,
            $data->story,
            $data->gifts,
            $data->content,
            array_merge($data->settings, $settings),
            $data->sections,
        );
    }
}
