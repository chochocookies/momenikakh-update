<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Services;

use App\Domain\Invitation\Data\InvitationData;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Templates\TemplateRegistry;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

/**
 * DSH-05: is the invitation complete enough to go live? The same rules the editor validates with, run over what is
 * stored, plus "a template that exists and fits the event type". Content and settings are optional, so they are not checked.
 */
final class InvitationReadiness
{
    /** Editor step => the payload keys it owns. */
    private const KEYS = [
        'basics' => ['title', 'template_slug', 'timezone'],
        'people' => ['people'], 'events' => ['events'], 'story' => ['story'], 'gifts' => ['gifts'],
    ];

    public function __construct(private readonly EventTypeRegistry $events, private readonly TemplateRegistry $templates) {}

    /**
     * @return array{ready:bool,steps:array<string,string|null>} step => first problem, or null when the step is fine
     */
    public function check(Invitation $invitation): array
    {
        $definition = $this->events->for($invitation->event_type);
        $data = InvitationData::fromModel($invitation->fresh() ?? $invitation)->toArray();
        $steps = [];

        foreach (self::KEYS as $step => $keys) {
            // Optional steps are only checked when the event type uses them and something was entered.
            if (in_array($step, ['story', 'gifts'], true) && (! $definition->hasFeature($step) || $data[$step] === [])) {
                continue;
            }
            $validator = Validator::make(Arr::only($data, $keys), $definition->rules($step));
            $steps[$step] = $validator->fails() ? (string) $validator->errors()->first() : null;
        }

        $manifest = $this->templates->find((string) $invitation->template_slug);
        if ($manifest === null || ! $manifest->supportsEvent($invitation->event_type)) {
            $steps['basics'] ??= 'Pilih template yang cocok dengan jenis acara.';
        }

        return ['ready' => array_filter($steps) === [], 'steps' => $steps];
    }
}
