<?php

declare(strict_types=1);

namespace App\Livewire\Editor;

use App\Domain\Billing\Entitlements;
use App\Domain\Invitation\Data\InvitationData;
use App\Domain\Invitation\EventTypes\EventTypeDefinition;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Rules\AvailableSlug;
use App\Domain\Invitation\Services\InvitationWriter;
use App\Domain\Media\Models\MusicTrack;
use App\Domain\Templates\TemplateRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * DSH-03: the invitation editor. One step at a time; every field saves by itself when it loses focus.
 *
 * - The form mirrors the payload of InvitationWriter, and the rules come from EventTypeDefinition::rules($step),
 *   so the editor, the validation and the writer cannot drift apart.
 * - A step is saved only when the whole step is valid. Errors show inline under the field; the data stays on screen
 *   (marked "belum tersimpan") until it is fixed.
 * - Gallery and music (DSH-12/13) use MediaUploader (same rules as the MED routes) via HandlesEditorMedia.
 */
#[Layout('components.layouts.app', ['title' => 'Isi data undangan'])]
final class InvitationEditor extends Component
{
    use AuthorizesRequests;
    use HandlesEditorMedia;
    use WithFileUploads;

    /** First segment of a form path => the step that saves it. */
    private const STEP_OF = [
        'title' => 'basics', 'slug' => 'basics', 'template_slug' => 'basics', 'timezone' => 'basics',
        'people' => 'people', 'events' => 'events', 'story' => 'story', 'gifts' => 'gifts',
        'content' => 'content', 'settings' => 'settings',
    ];

    public Invitation $invitation;

    #[Locked]
    public string $step = 'basics';

    /** @var array<string,mixed> */
    public array $form = [];

    /** @var list<string> steps with edits that failed validation and are not saved yet */
    #[Locked]
    public array $unsaved = [];

    #[Locked]
    public ?string $savedAt = null;

    public function mount(Invitation $invitation): void
    {
        $this->invitation = $invitation;
        $this->authorize('update', $invitation);
        $this->loadForm();
        $this->bootMediaState();
    }

    public function hydrate(): void
    {
        // Update requests do not pass through the route's policy check, so the owner is checked on every request.
        $this->authorize('update', $this->invitation);
    }

    public function selectStep(string $step): void
    {
        if (array_key_exists($step, StepFields::steps($this->definition()))) {
            $this->step = $step;
        }
    }

    public function addRow(string $step): void
    {
        $definition = $this->definition();
        if (! in_array($step, StepFields::LISTS, true) || ! array_key_exists($step, StepFields::steps($definition))) {
            return;
        }
        $rows = (array) ($this->form[$step] ?? []);
        if (count($rows) >= $this->maxRows($step)) {
            return;
        }
        $rows[] = $this->blankRow($step, $rows);
        $this->form[$step] = $rows;
    }

    public function removeRow(string $step, int $index): void
    {
        if (! in_array($step, StepFields::LISTS, true) || ! isset($this->form[$step][$index])) {
            return;
        }
        $rows = $this->form[$step];
        unset($rows[$index]);
        $this->form[$step] = array_values($rows);
        $this->save($step);
    }

    /** Livewire hook: runs after any wire:model value changed. $name is the full path, e.g. form.people.0.name. */
    public function updated(string $name): void
    {
        $parts = explode('.', $name);
        if ($parts[0] !== 'form' || ! isset(self::STEP_OF[$parts[1] ?? ''])) {
            return;
        }

        $saved = $this->save(self::STEP_OF[$parts[1]]);

        // A new template brings its own palettes: keep the chosen one when it exists there, else use its first.
        if ($saved && $parts[1] === 'template_slug') {
            $manifest = app(TemplateRegistry::class)->find((string) $this->invitation->template_slug);
            if ($manifest !== null && ! in_array($this->form['settings']['palette'] ?? null, $manifest->paletteKeys(), true)) {
                $this->form['settings']['palette'] = $manifest->defaultPalette();
                $this->save('settings');
            }
        }
    }

    /** Validate one step and write it. Returns whether it was saved. Media steps are handled separately. */
    public function save(string $step): bool
    {
        $this->authorize('update', $this->invitation);

        if (StepFields::isMedia($step)) {
            return true;
        }

        $definition = $this->definition();
        $payload = $this->payload($step);
        $validator = Validator::make($payload, $this->rules($step, $definition), [], $this->attributes($step, $definition));

        $this->resetErrorBag($this->stepErrorKeys($step));

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $messages) {
                $this->addError("form.{$key}", $messages[0]);
            }
            $this->unsaved = array_values(array_unique([...$this->unsaved, $step]));

            return false;
        }

        app(InvitationWriter::class)->apply($this->invitation, $step, $payload);
        $this->unsaved = array_values(array_diff($this->unsaved, [$step]));
        $this->savedAt = now($this->invitation->timezone)->format('H.i');
        $this->reloadStep($step);
        $this->dispatch('invitation-saved'); // the live preview reloads (DSH-04)

        return true;
    }

    public function render(): View
    {
        $definition = $this->definition();
        $templates = app(TemplateRegistry::class);
        $steps = StepFields::steps($definition);
        $isMedia = StepFields::isMedia($this->step);
        $entitlements = app(Entitlements::class);

        $fields = $isMedia
            ? []
            : (in_array($this->step, StepFields::LISTS, true)
                ? StepFields::row($this->step, $definition)
                : StepFields::single($this->step, $definition, $this->invitation, $templates));

        $catalogTracks = [];
        if ($this->step === 'music' && config('media-limits.audio.catalog_enabled')) {
            $catalogTracks = MusicTrack::query()->selectable()->orderBy('sort')->orderBy('title')
                ->get(['id', 'title', 'artist', 'requires_attribution', 'attribution_text'])
                ->map(fn (MusicTrack $t): array => [
                    'id' => $t->id,
                    'title' => $t->title,
                    'artist' => $t->artist,
                    'attribution' => $t->requires_attribution ? (string) $t->attribution_text : null,
                ])->all();
        }

        return view('livewire.editor.invitation-editor', [
            'definition' => $definition,
            'steps' => $steps,
            'fields' => $fields,
            'isList' => in_array($this->step, StepFields::LISTS, true),
            'isMedia' => $isMedia,
            'maxRows' => in_array($this->step, StepFields::LISTS, true) ? $this->maxRows($this->step) : 0,
            'template' => $templates->find((string) $this->invitation->template_slug),
            'maxPhotos' => $entitlements->limit($this->invitation, 'max_photos'),
            'canUploadMusic' => $entitlements->can($this->invitation, 'custom_music'),
            'catalogEnabled' => (bool) config('media-limits.audio.catalog_enabled'),
            'catalogTracks' => $catalogTracks,
            'imageMaxBytes' => (int) config('media-limits.image.max_bytes'),
            'audioMaxBytes' => (int) config('media-limits.audio.max_bytes'),
        ]);
    }

    private function definition(): EventTypeDefinition
    {
        return app(EventTypeRegistry::class)->for($this->invitation->event_type);
    }

    /** @return array<string,mixed> */
    private function rules(string $step, EventTypeDefinition $definition): array
    {
        $rules = $definition->rules($step);

        if ($step === 'basics') {
            $templates = array_keys(app(TemplateRegistry::class)->forEvent($definition->type));
            $rules['template_slug'] = ['required', 'string', Rule::in($templates)];
            $rules['timezone'] = ['required', 'in:'.implode(',', EventTypeDefinition::TIMEZONES)];
            // DSH-13: live uniqueness (AvailableSlug also blocks reserved words).
            $rules['slug'] = ['nullable', 'string', 'max:60', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', new AvailableSlug($this->invitation->id)];
        }
        if ($step === 'settings') {
            $manifest = app(TemplateRegistry::class)->find((string) $this->invitation->template_slug);
            if ($manifest !== null) {
                $rules['settings.palette'] = ['nullable', 'string', Rule::in($manifest->paletteKeys())];
            }
        }

        return $rules;
    }

    /** @return array<string,string> */
    private function attributes(string $step, EventTypeDefinition $definition): array
    {
        return StepFields::labels($step, $definition, $this->invitation, app(TemplateRegistry::class))
            + ['people' => 'Daftar pihak', 'events' => 'Daftar acara', 'story' => 'Cerita', 'gifts' => 'Amplop digital'];
    }

    /**
     * What goes to the validator and the writer: the step's part of the form, trimmed, with empty strings as null
     * and datetime-local values ("2026-12-05T08:00") as the "Y-m-d H:i" the rules and the writer expect.
     *
     * @return array<string,mixed>
     */
    private function payload(string $step): array
    {
        $keys = array_keys(array_filter(self::STEP_OF, fn (string $s) => $s === $step));
        $payload = $this->normalize(Arr::only($this->form, $keys));

        if ($step === 'events') {
            $payload['events'] = array_map(function (mixed $row): mixed {
                if (! is_array($row)) {
                    return $row;
                }
                foreach (['starts_at', 'ends_at'] as $key) {
                    if (is_string($row[$key] ?? null)) {
                        $row[$key] = str_replace('T', ' ', $row[$key]);
                    }
                }

                return $row;
            }, (array) ($payload['events'] ?? []));
        }

        return $payload;
    }

    private function normalize(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn (mixed $v) => $this->normalize($v), $value);
        }
        if (is_string($value)) {
            $value = trim($value);

            return $value === '' ? null : $value;
        }

        return $value;
    }

    /** @return list<string> error keys that belong to a step, so saving one step does not clear another one's errors */
    private function stepErrorKeys(string $step): array
    {
        $tops = array_keys(array_filter(self::STEP_OF, fn (string $s) => $s === $step));

        return array_values(array_filter(
            $this->getErrorBag()->keys(),
            fn (string $key) => in_array(explode('.', $key)[1] ?? '', $tops, true),
        ));
    }

    private function maxRows(string $step): int
    {
        $definition = $this->definition();

        return match ($step) {
            'people' => (int) array_sum(array_column($definition->roles, 'max')),
            'events' => $definition->events['max'],
            'story' => 12,
            default => 8,
        };
    }

    /**
     * @param  list<array<string,mixed>>  $rows
     * @return array<string,mixed>
     */
    private function blankRow(string $step, array $rows): array
    {
        $definition = $this->definition();

        return match ($step) {
            'people' => ['id' => null, 'role' => $this->nextRole($rows), 'name' => '', 'nickname' => '', 'parents_text' => '', 'bio' => '', 'birth_date' => '', 'instagram' => ''],
            'events' => ['id' => null, 'kind' => $definition->kinds()[0], 'label' => '', 'starts_at' => '', 'ends_at' => '', 'timezone' => '', 'venue_name' => '', 'address' => '', 'maps_url' => '', 'latitude' => '', 'longitude' => '', 'dress_code' => '', 'notes' => ''],
            'story' => ['id' => null, 'title' => '', 'body' => '', 'happened_on' => ''],
            default => ['id' => null, 'type' => 'bank', 'provider' => '', 'holder_name' => '', 'number' => '', 'address_text' => '', 'is_active' => true],
        };
    }

    /** The first role that has not reached its maximum yet (or the first role). @param list<array<string,mixed>> $rows */
    private function nextRole(array $rows): string
    {
        $definition = $this->definition();
        $counts = array_count_values(array_map(fn (array $r) => (string) ($r['role'] ?? ''), $rows));
        foreach ($definition->roles as $role => $def) {
            if (($counts[$role] ?? 0) < $def['max']) {
                return $role;
            }
        }

        return $definition->roleKeys()[0];
    }

    private function loadForm(): void
    {
        $data = $this->fresh();
        $definition = $this->definition();
        $manifest = app(TemplateRegistry::class)->find((string) $this->invitation->template_slug);

        $settings = $data['settings'];
        $this->form = [
            'title' => $data['title'],
            'slug' => $data['slug'],
            'template_slug' => (string) $data['template_slug'],
            'timezone' => $data['timezone'],
            'people' => $data['people'],
            'events' => $this->eventRows($data['events']),
            'story' => $data['story'],
            'gifts' => $data['gifts'],
            'content' => array_map(fn (string $key) => $data['content'][$key] ?? '', array_combine($definition->contentKeys(), $definition->contentKeys())),
            'settings' => [
                // A stored palette the template does not have (sample data says "default") shows as the template's first one, as the page does.
                'palette' => in_array($settings['palette'] ?? null, $manifest?->paletteKeys() ?? [], true) ? $settings['palette'] : ($manifest?->defaultPalette() ?? 'default'),
                'motion' => $settings['motion'] ?? 'full',
                'rsvp_enabled' => (bool) ($settings['rsvp_enabled'] ?? true),
                'wishes_enabled' => (bool) ($settings['wishes_enabled'] ?? true),
                'gifts_enabled' => (bool) ($settings['gifts_enabled'] ?? $definition->hasFeature('gifts')),
            ],
        ];
    }

    /** After a save: take the rows back from the database so new rows get their ids (media attaches to ids). */
    private function reloadStep(string $step): void
    {
        if (! in_array($step, StepFields::LISTS, true)) {
            return;
        }
        $data = $this->fresh();
        $this->form[$step] = $step === 'events' ? $this->eventRows($data['events']) : $data[$step];
    }

    /**
     * Events as the form shows them: datetime-local format, and the event's own zone left empty when it follows the
     * invitation's zone.
     *
     * @param  list<array<string,mixed>>  $events
     * @return list<array<string,mixed>>
     */
    private function eventRows(array $events): array
    {
        $own = $this->invitation->events->pluck('timezone', 'id');

        return array_map(function (array $e) use ($own): array {
            foreach (['starts_at', 'ends_at'] as $key) {
                $e[$key] = is_string($e[$key] ?? null) ? str_replace(' ', 'T', $e[$key]) : '';
            }
            $e['timezone'] = (string) ($own[$e['id']] ?? '');

            return $e;
        }, $events);
    }

    /** @return array<string,mixed> */
    private function fresh(): array
    {
        $this->invitation->refresh()->load(['people', 'events', 'storyItems', 'gifts']);

        return InvitationData::fromModel($this->invitation)->toArray();
    }
}
