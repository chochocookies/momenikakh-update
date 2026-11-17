<?php

declare(strict_types=1);

namespace App\Domain\Invitation\EventTypes;

use App\Domain\Invitation\Enums\EventType;
use Closure;
use InvalidArgumentException;

/**
 * Everything that differs between event types lives here, built from config/event-types.php.
 * Editor forms, validation rules, sample data and templates all read this one object.
 */
final readonly class EventTypeDefinition
{
    public const STEPS = ['basics', 'people', 'events', 'story', 'gifts', 'content', 'settings'];

    /**
     * @param array<string, array{label:string,min:int,max:int}> $roles
     * @param array{min:int,max:int,kinds:list<string>} $events
     * @param list<string> $sections
     * @param array<string,int> $content key => max length
     * @param array<string,bool> $features
     * @param array<string,string> $defaults
     */
    public function __construct(
        public EventType $type,
        public string $label,
        public string $description,
        public array $roles,
        public array $events,
        public array $sections,
        public array $content,
        public array $features,
        public array $defaults,
    ) {
    }

    /** @param array<string,mixed> $config */
    public static function fromArray(EventType $type, array $config): self
    {
        foreach (['label', 'description', 'roles', 'events', 'sections', 'content', 'features', 'defaults'] as $key) {
            if (! array_key_exists($key, $config)) {
                throw new InvalidArgumentException("Event type [{$type->value}] is missing config key [{$key}].");
            }
        }

        return new self(
            $type, $config['label'], $config['description'], $config['roles'], $config['events'],
            $config['sections'], $config['content'], $config['features'], $config['defaults'],
        );
    }

    public function key(): string
    {
        return $this->type->value;
    }

    /** @return list<string> */
    public function roleKeys(): array
    {
        return array_keys($this->roles);
    }

    /** @return list<string> roles that must appear at least once */
    public function requiredRoles(): array
    {
        return array_keys(array_filter($this->roles, fn (array $r) => $r['min'] > 0));
    }

    /** @return list<string> */
    public function kinds(): array
    {
        return $this->events['kinds'];
    }

    public function allowsRole(string $role): bool
    {
        return isset($this->roles[$role]);
    }

    public function allowsKind(string $kind): bool
    {
        return in_array($kind, $this->events['kinds'], true);
    }

    public function hasFeature(string $feature): bool
    {
        return $this->features[$feature] ?? false;
    }

    /** @return list<string> content keys this type accepts */
    public function contentKeys(): array
    {
        return array_keys($this->content);
    }

    /** @return array<string, mixed> Laravel validation rules for one editor step */
    public function rules(string $step): array
    {
        return match ($step) {
            'basics' => $this->basicsRules(),
            'people' => $this->peopleRules(),
            'events' => $this->eventRules(),
            'story' => $this->storyRules(),
            'gifts' => $this->giftRules(),
            'content' => $this->contentRules(),
            'settings' => $this->settingsRules(),
            default => throw new InvalidArgumentException("Unknown editor step [{$step}]."),
        };
    }

    /** @return array<string,mixed> */
    private function basicsRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'template_slug' => ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'locale' => ['nullable', 'in:id'],
            'timezone' => ['nullable', 'in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura'],
        ];
    }

    /** @return array<string,mixed> */
    private function peopleRules(): array
    {
        $min = array_sum(array_column($this->roles, 'min'));
        $max = array_sum(array_column($this->roles, 'max'));

        return [
            'people' => ['required', 'array', "min:{$min}", "max:{$max}", $this->roleCountRule()],
            'people.*.id' => ['nullable', 'integer'],
            'people.*.role' => ['required', 'string', 'in:'.implode(',', $this->roleKeys())],
            'people.*.name' => ['required', 'string', 'max:100'],
            'people.*.nickname' => ['nullable', 'string', 'max:40'],
            'people.*.parents_text' => ['nullable', 'string', 'max:200'],
            'people.*.bio' => ['nullable', 'string', 'max:500'],
            'people.*.birth_date' => ['nullable', 'date_format:Y-m-d'],
            'people.*.instagram' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9._]+$/'],
        ];
    }

    private function roleCountRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $counts = [];
            foreach ((array) $value as $person) {
                $role = is_array($person) ? ($person['role'] ?? null) : null;
                if (is_string($role)) {
                    $counts[$role] = ($counts[$role] ?? 0) + 1;
                }
            }
            foreach ($this->roles as $role => $def) {
                $n = $counts[$role] ?? 0;
                if ($n < $def['min']) {
                    $fail("Peran \"{$def['label']}\" minimal {$def['min']} orang.");
                }
                if ($n > $def['max']) {
                    $fail("Peran \"{$def['label']}\" maksimal {$def['max']} orang.");
                }
            }
        };
    }

    /** @return array<string,mixed> */
    private function eventRules(): array
    {
        return [
            'events' => ['required', 'array', "min:{$this->events['min']}", "max:{$this->events['max']}"],
            'events.*.id' => ['nullable', 'integer'],
            'events.*.kind' => ['required', 'string', 'in:'.implode(',', $this->events['kinds'])],
            'events.*.label' => ['nullable', 'string', 'max:60'],
            'events.*.starts_at' => ['required', 'date_format:Y-m-d H:i'],
            'events.*.ends_at' => ['nullable', 'date_format:Y-m-d H:i', 'after:events.*.starts_at'],
            'events.*.venue_name' => ['required', 'string', 'max:120'],
            'events.*.address' => ['required', 'string', 'max:300'],
            'events.*.maps_url' => ['nullable', 'url:https', 'max:500'],
            'events.*.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'events.*.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'events.*.dress_code' => ['nullable', 'string', 'max:80'],
            'events.*.notes' => ['nullable', 'string', 'max:300'],
        ];
    }

    /** @return array<string,mixed> */
    private function storyRules(): array
    {
        if (! $this->hasFeature('story')) {
            return ['story' => ['prohibited']];
        }

        return [
            'story' => ['nullable', 'array', 'max:12'],
            'story.*.id' => ['nullable', 'integer'],
            'story.*.title' => ['required', 'string', 'max:80'],
            'story.*.body' => ['required', 'string', 'max:600'],
            'story.*.happened_on' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    /** @return array<string,mixed> */
    private function giftRules(): array
    {
        return [
            'gifts' => ['nullable', 'array', 'max:8'],
            'gifts.*.id' => ['nullable', 'integer'],
            'gifts.*.type' => ['required', 'string', 'in:bank,ewallet,address'],
            'gifts.*.provider' => ['required_if:gifts.*.type,bank,ewallet', 'nullable', 'string', 'max:60'],
            'gifts.*.holder_name' => ['required_if:gifts.*.type,bank,ewallet', 'nullable', 'string', 'max:100'],
            'gifts.*.number' => ['required_if:gifts.*.type,bank,ewallet', 'nullable', 'regex:/^[0-9][0-9 -]{4,29}$/'],
            'gifts.*.address_text' => ['required_if:gifts.*.type,address', 'nullable', 'string', 'max:300'],
            'gifts.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string,mixed> */
    private function contentRules(): array
    {
        $rules = ['content' => ['nullable', 'array']];
        foreach ($this->content as $key => $max) {
            $rules["content.{$key}"] = ['nullable', 'string', "max:{$max}"];
        }

        return $rules;
    }

    /** @return array<string,mixed> */
    private function settingsRules(): array
    {
        return [
            'settings' => ['nullable', 'array'],
            'settings.palette' => ['nullable', 'string', 'max:40', 'regex:/^[a-z0-9-]+$/'],
            'settings.motion' => ['nullable', 'in:full,reduced'],
            'settings.rsvp_enabled' => ['nullable', 'boolean'],
            'settings.wishes_enabled' => ['nullable', 'boolean'],
            'settings.gifts_enabled' => ['nullable', 'boolean'],
        ];
    }
}
